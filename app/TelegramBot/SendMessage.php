<?php


namespace App\TelegramBot;


use App\TelegramBot\Types\TelegramBaseKeyboard;
use App\TelegramBot\Types\TelegramFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;


trait SendMessage
{

    public function send_reply($url, $post_params)
    {

        /*["chat_id" => $chat_id, "text" => $text, 'reply_markup' => $kb, "reply_to_message_id" => $reply];*/
        $post_params['chat_id'] = isset($post_params['chat_id'])?$post_params['chat_id']:$this->chat_id;
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
                //Storage::put('file.jpg', $p->data);
                unset($post_params[$k]);
            } elseif ($p instanceof View) {
                $post_params[$k] = $p->render();
            }
        }

        if ($has_file)
            return $request->post($this->bot_url . '/' . $url, $post_params);
        else
            return Http::post($this->bot_url . '/' . $url, $post_params);

    }

    public function send_text($text)
    {
        return $this->message_init($this->send_reply('sendMessage', ['text' => $text])['result'], "sent");
    }

    public function send_photo($photo, $caption)
    {

        return $this->message_init($this->send_reply('sendPhoto', ["photo" => $photo, "caption" => $caption])['result'], "sent");
    }

    public function send_audio($audio, $caption)
    {
        return $this->message_init($this->send_reply('sendAudio', ["audio" => $audio, "caption" => $caption])['result'], "sent");
    }

    public function send_document($document, $caption)
    {
        return $this->message_init($this->send_reply('sendDocument', ["document" => $document, "caption" => $caption])['result'], "sent");
    }

    public function send_video($video, $caption)
    {
        return $this->message_init($this->send_reply('sendVideo', ["video" => $video, "caption" => $caption])['result'], "sent");
    }

    public function send_video_note($video, $caption)
    {
        return $this->message_init($this->send_reply('sendVideoNote', ["videoNote" => $video, "caption" => $caption])['result'], "sent");
    }

    public function send_chat_action($action)
    {
        return $this->send_reply('sendChatAction', ["action" => $action])['result'];
    }

    public function send_poll($question, $options, $type = 'regular')
    {
        $this->send_reply('sendPoll', ["question" => $question, 'options' => $options, 'type' => $type])['result'];
    }

    public function answer_callback($options = [])
    {
        $options['callback_query_id'] = $this->callback_model->id;
        return Http::post($this->bot_url . '/' . 'answerCallbackQuery', $options);
    }

}
