<?php


namespace App;


use App\TelegramBot\BotRout;
use App\TelegramBot\Http\Controllers\BaseCommandController;
use App\TelegramBot\TelegramBot;
use App\TelegramBot\Types\InlineKeyboard;
use App\TelegramBot\Types\ReplyKeyboard;
use App\TelegramBot\Types\TelegramFile;
use Illuminate\Support\Facades\Storage;


class CommandController extends BaseCommandController
{
    public function start()
    {
        $keyboard = new ReplyKeyboard();
        $keyboard->newButton('request',['request_poll'=>['type'=>'regular']]);

        $this->telegramBot->set_reply_message_id();
        $this->telegramBot->set_keyboard($keyboard);
        $this->telegramBot->set_reply_message_id();
        $this->telegramBot->send_text('hello');
    }
    public function download(){
        $this->telegramBot->send_photo(new TelegramFile('telegram/photos/file_5.jpg'),'se');
        $this->telegramBot->send_text('hello');
    }
    public function start_with_arg($name,$family)
    {


        $this->telegramBot->set_reply_message_id();
        $this->telegramBot->send_text('your name is '.$name.' and your family is '.$family);
    }

    public function default_2()
    {
        $this->telegramBot->send_photo(new TelegramFile('telegram/photos/file_1.jpg'),'hello');
        //$this->telegramBot->send_text('the command is wronge');
        //$this->telegramBot->set_reply_message_id();
        //$this->telegramBot->send_text($this->telegramBot->message_object->text);
        //$this->telegramBot->change_status(3);
    }

    public function default_3()
    {
        $this->telegramBot->send_text('the command is wronge is status 3');
        //$this->telegramBot->change_status();
    }
    public function default_func()
    {
        $this->telegramBot->set_reply_message_id();
        $this->telegramBot->send_text($this->telegramBot->get_message_type());
    }

    public function reply_photo()
    {
        $path = $this->telegramBot->download_file($this->telegramBot->message['photo'][0]['file_id'], 'telegram');

        //Storage::put('log3.json', $this->telegramBot->send_photo(new TelegramFile($path), 'mirror'));
    }

    public function default_func_status_1()
    {
        $keyboard = new InlineKeyboard();
        $keyboard->newButton('start',['callback_data'=>'start']);


        $this->telegramBot->set_reply_message_id();
        $this->telegramBot->set_keyboard($keyboard);
        $this->telegramBot->send_text("i'm in status 1");

    }

    public function only_status_1()
    {
        $this->telegramBot->send_text('1 in');
    }

    public function view()
    {
        $this->telegramBot->send_text(view('hello',['name'=>'mehdi']));
    }

    public function start_callback(){
        $this->telegramBot->answer_callback(["text"=>"callback answered"]);
        $this->telegramBot->send_text('callback');
    }

    public function poll()
    {
        $this->telegramBot->send_poll('poll?',['s','d','f']);
    }

    public function edited_message()
    {
        $this->telegramBot->set_reply_message_id();
        $this->telegramBot->send_text('edited');
    }
}

