<?php


namespace App\TelegramBot;


use App\TelegramBot\BotMiddleware\MustJoinChannels;

class BotKernel
{
    public static $middlewares = [
        'join' => MustJoinChannels::class
    ];
}
