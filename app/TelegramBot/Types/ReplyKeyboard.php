<?php


namespace App\TelegramBot\Types;


class ReplyKeyboard extends TelegramBaseKeyboard
{
    protected $keyboard = array(
    'keyboard' => array(
        array()
    )
);

    public function __construct($resize_keyboard = true,$one_time_keyboard =false,$selective = false)
    {
        $this->keyboard['resize_keyboard'] = $resize_keyboard;
        $this->keyboard['one_time_keyboard'] = $one_time_keyboard;
        $this->keyboard['selective'] = $selective;

    }
    public function newLine()
    {
        $this->keyboard['keyboard'][] = array();
    }

    public function newButton($text, $options = [], $line = -1)
    {
        $line = ($line < 0)? count($this->keyboard['keyboard']) -$line -2: $line;
        $options['text'] = $text;
        $this->keyboard['keyboard'][$line][] = $options;
    }
}
