<?php


namespace App\TelegramBot\Types;


abstract class TelegramBaseKeyboard
{
    protected $keyboard;

    public abstract function newLine();

    public abstract function newButton($text, $options, $line = -1);

    public function render()
    {
        return json_encode($this->keyboard);
    }

}
