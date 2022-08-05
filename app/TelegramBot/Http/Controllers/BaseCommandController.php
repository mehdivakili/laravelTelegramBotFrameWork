<?php


namespace App\TelegramBot\Http\Controllers;


use App\TelegramBot\TelegramBot;

abstract class BaseCommandController
{
    protected $telegramBot;

    public function __construct(TelegramBot $telegramBot)
    {
        $this->telegramBot = $telegramBot;
    }
}
