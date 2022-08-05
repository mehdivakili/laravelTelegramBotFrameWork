<?php


namespace App\TelegramBot;


use App\Models\CallbackQuery;
use App\Models\Chat;
use App\Models\Message;
use App\TelegramBot\Types\TelegramBaseKeyboard;
use App\TelegramBot\Types\TelegramFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TelegramBot
{
    use SendMessage;


    /**
     * @var Request
     */
    private $request;
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $config;
    /**
     * @var array|mixed
     */
    private $token;
    /**
     * @var string
     */
    public $bot_url;


    /**
     * @var array
     */
    public $data;
    public $chat_id;
    public $keyboard;
    public $reply_message_id;
    /**
     * @var array|mixed
     */
    public $command_controller;
    /**
     * @var array|mixed
     */
    public $commands;
    /**
     * @var mixed
     */
    public $text;
    public $user;
    public $chat;
    /**
     * @var mixed
     */
    public $message;
    /**
     * @var false|string
     */
    public $message_type;
    /**
     * @var Message
     */
    public $message_model;
    /**
     * @var mixed
     */
    public $message_object;
    private $_send_methods = array();
    /**
     * @var mixed
     */
    public $callback;
    /**
     * @var CallbackQuery
     */
    public $callback_model;
    /**
     * @var mixed
     */
    public $callback_object;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->config = config('telegramBot');
        $this->token = $this->config['token'];
        $this->bot_url = "https://api.telegram.org/bot" . $this->token;
        $this->keyboard = false;
        $this->reply_message_id = false;
        /*        $types = BotRout::$types;
                foreach ($types as $type) {
                    $funtionName = 'send_' . $type;
                    $this->_send_methods[$funtionName] = function ($args) use ($type) {
                        if ($type == "text")
                            return $this->send_reply('sendMessage', [$type => $args[0]]);
                        else
                            return $this->send_reply('send' . ucfirst($type), [$type => $args[0], "caption" => $args[1]]);
                    };
                }*/


    }

    /*public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->_send_methods)) {
            return $this->_send_methods[$name]($arguments);
        }
    }
    public function send_reply($url, $post_params)
    {

        $post_params['chat_id'] = $this->chat_id;
        if ($this->keyboard) {
            if (is_array($this->keyboard))
                $post_params['reply_markup'] = json_encode($this->keyboard);
            elseif (is_string($this->keyboard))
                $post_params['reply_markup'] = $this->keyboard;
            elseif ($this->keyboard instanceof TelegramBaseKeyboard) {
                $post_params['reply_markup'] = $this->keyboard->render();
            }
        }
        if ($this->reply_message_id) {
            $post_params['reply_to_message_id'] = $this->reply_message_id;
        }
        $request = null;
        $has_file = false;
        foreach ($post_params as $k => $p) {
            if ($p instanceof TelegramFile) {
                if (!$has_file) {
                    $request = Http::attach($k, $p->data, $p->name);
                    $has_file = true;
                } else {
                    $request = $request->attach($k, $p->data, $p->name);
                }
                Storage::put('file.jpg', $p->data);
                unset($post_params[$k]);
            } elseif ($p instanceof View) {
                $post_params[$k] = $p->render();
            }
        }

        if ($has_file)
            return $request->post($this->bot_url . '/' . $url, $post_params);
        else
            return Http::post($this->bot_url . '/' . $url, $post_params);

    }*/

    public function data_init()
    {
        $this->data = $this->request->all();
        /*        Storage::put($keys[1].'.json',json_encode($this->data[$keys[1]]));
                die(200);*/
        $this->update_type = $this->get_update_type();
        if (!$this->update_type) {
            $keys = array_keys($this->data);
            Storage::put($keys[1] . '.json', json_encode($this->data[$keys[1]]));
            die(200);
        }
        call_user_func([$this, 'data_' . $this->update_type . '_init']);

    }

    public function get_update_type()
    {
        $update_types = ["message", "edited_message", "channel_post", "edited_channel_post", 'callback_query'];
        $d = array_keys($this->data);
        foreach ($update_types as $type) {
            if (in_array($type, $d)) {
                return $type;
            }
        }
        return false;
    }

    public function data_message_init($message_type = "message")
    {
        $this->chat_id = $this->data[$message_type]['chat']['id'];
        $this->message = $this->data[$message_type];
        $this->message_type = $this->get_message_type();

        if (isset($this->message['text'])) {
            $this->text = $this->message['text'];
        } elseif (isset($this->message['caption'])) {
            $this->text = $this->message['caption'];
        } else {
            $this->text = '';
        }
        $chat = Chat::find($this->chat_id);
        if (empty($chat)) {
            $chat = new Chat();
            $chat->id = $this->chat_id;
            $chat->type = $this->data[$message_type]['chat']['type'];
            $chat->status = 0;
            $chat->save();
        }
        $this->chat = $chat;
        $this->user = $this->message["from"];

        $this->message_model = $this->message_init($this->data[$message_type]);
        $this->message_object = json_decode(json_encode($this->data[$message_type]));
    }

    public function data_edited_message_init()
    {
        $this->data_message_init('edited_message');
    }
    public function data_channel_post_init()
    {
        $this->data_message_init("channel_post");
    }
    public function data_edited_channel_post_init()
    {
        $this->data_message_init("edited_channel_post");
    }

    public function data_callback_query_init()
    {
        $this->chat_id = $this->data["callback_query"]["message"]["chat"]["id"];
        $this->callback = $this->data["callback_query"];
        $this->text = $this->data["callback_query"]["data"];

        $chat = Chat::find($this->chat_id);
        if (empty($chat)) {
            $chat = new Chat();
            $chat->id = $this->chat_id;
            $chat->type = $this->data["callback_query"]["message"]['chat']['type'];
            $chat->status = 0;
            $chat->save();
        }
        $this->chat = $chat;
        $this->callback = $this->data["callback_query"];
        $this->callback_model = $this->callback_init($this->data['callback_query']);
        $this->callback_object = json_decode(json_encode($this->data['callback_query']));

    }


    public function message_init($json_data, $resource_type = "received")
    {
        $message = Message::where('message_id', $json_data['message_id'])
            ->where('chat_id', $json_data['chat']["id"])->first();
        if (!empty($message)) {
            return $message;
        }
        $message = new Message();
        $message->message_id = $json_data["message_id"];
        if (isset($json_data['text'])) {
            $message->text = $json_data['text'];
        } elseif (isset($json_data['caption'])) {
            $message->text = $json_data['caption'];
        } else {
            $message->text = '';
        }
        $message->json_data = json_encode($json_data);
        $message->chat_id = $json_data['chat']["id"];
        $message->resource_type = $resource_type;
        if (isset($json_data["reply_to_message"])) {
            $reply_message = $this->message_init($json_data["reply_to_message"]);
            /*            $reply_message = Message::where('message_id', $json_data["reply_to_message"]['message_id'])
                            ->where('chat_id', $json_data["reply_to_message"]['chat']["id"])->first();
                        if (empty($reply_message)) {
                            $reply_message = $this->message_init($json_data["reply_to_message"]);
                        }*/
            $message->reply_to_message_id = $reply_message->id;
        }
        $message->save();
        return $message;
    }

    public function callback_init($json_data)
    {
        $callback = CallbackQuery::find($json_data["id"]);
        if (!empty($callback)) {
            return $callback;
        }
        $callback = new CallbackQuery();
        $callback->id = $json_data["id"];
        $callback->data = $json_data["data"];
        $callback->message_id = $this->message_init($json_data["message"])->id;
        $callback->chat_instance = $json_data["chat_instance"];
        $callback->save();
        return $callback;
    }

    public function test()
    {
        echo $this->set_webhook();
    }


    public static function Routes()
    {
        \Route::post('bot', \App\TelegramBot\Http\Controllers\BotController::class . '@bot');
        \Route::get('set_webhook', \App\TelegramBot\Http\Controllers\BotController::class . '@set_webhook');
        \Route::get('delete_webhook', \App\TelegramBot\Http\Controllers\BotController::class . '@delete_webhook');
        \Route::get('restart_webhook', \App\TelegramBot\Http\Controllers\BotController::class . '@restart_webhook');
    }

    public function set_webhook()
    {
        return file_get_contents("https://api.telegram.org/bot{$this->token}/setWebhook?url={$this->config['site_url']}");
    }

    public function delete_webhook()
    {
        return file_get_contents("https://api.telegram.org/bot{$this->token}/deleteWebhook?drop_pending_updates=True");
    }

    public function restart_webhook()
    {
        $this->delete_webhook();
        return $this->set_webhook();
    }


    public function set_reply_message_id($message_id = 0)
    {
        if ($message_id == 0) {
            $this->reply_message_id = $this->message['message_id'];
        } else {
            $this->reply_message_id = $message_id;
        }
    }

    public function set_keyboard($keyboard)
    {
        $this->keyboard = $keyboard;
    }

    public function set_chat_id($chat_id = 0)
    {
        if ($chat_id == 0) {
            $this->chat_id = $this->data["message"]["chat"]["id"];
        } else {
            $this->chat_id = $chat_id;
        }
    }

    public function get_message_type($message = false)
    {
        $out = false;
        $types = BotRout::$types;
        if (!$message) {
            $message = $this->message;
        }
        foreach ($types as $type) {
            if (isset($message[$type])) {
                $out = $type;
            }
        }
        return $out;
    }

    public function download_file($file_id, $file_path)
    {
        $file = json_decode(file_get_contents($this->bot_url . '/getFile?file_id=' . $file_id), true);
        $file_download_path = $file["result"]['file_path'];
        Storage::put($file_path . '/' . $file_download_path,
            Http::get("https://api.telegram.org/file/bot{$this->token}/{$file_download_path}"));
        return $file_path . '/' . $file_download_path;
    }

    public function change_status($status = 0)
    {
        $this->chat->status = $status;
        $this->chat->save();
    }
    public function pass_middlewares($middleware)
    {
        if (is_bool($middleware)) {
            return $middleware;
        } elseif (is_string($middleware)) {
            $matches = null;
            preg_match('/([^:]+):?(.+)?/',$middleware,$matches);
            $class = BotKernel::$middlewares[$matches[1]];
            $new = new $class();
            return call_user_func_array([$new,'handle'],array_merge([$this],(isset($matches[2])?explode(',',$matches[2]):[])));
        }elseif (is_array($middleware)){
            foreach ($middleware as $m){
                $matches = null;
                preg_match('/([^:]+):?(.+)?/',$m,$matches);
                $class = BotKernel::$middlewares[$matches[1]];
                $new = new $class();
                $res = call_user_func_array([$new,'handle'],array_merge([$this],(isset($matches[2])?explode(',',$matches[2]):[])));
                if(!$res){
                    return false;
                }
            }
        }
        return true;
    }

    public function get_chat_member($chat_id,$user_id){
        return $this->send_reply('getChatMember',['user_id'=>$user_id,'chat_id'=>$chat_id]);
    }
}
