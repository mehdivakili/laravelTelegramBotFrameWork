<?php


namespace App;


use App\TelegramBot\TelegramBot;

abstract class BotMiddleware
{
    public abstract function handle(TelegramBot $telegramBot,...$args);
}
