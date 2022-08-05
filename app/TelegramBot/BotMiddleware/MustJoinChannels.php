<?php


namespace App\TelegramBot\BotMiddleware;


use App\BotMiddleware;
use App\TelegramBot\TelegramBot;

class MustJoinChannels extends BotMiddleware
{

    public function handle(TelegramBot $telegramBot, ...$args)
    {
        $status = $telegramBot->get_chat_member($args[0],$telegramBot->user["id"])["result"]["status"];
        if($status== "left" or $status == "kicked") {
            $telegramBot->send_text(__("you must join the groups"));
            return false;
        }
        return true;
    }
}
