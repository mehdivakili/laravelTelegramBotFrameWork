<?php


namespace App\TelegramBot\Types;


class InlineKeyboard extends TelegramBaseKeyboard
{
    protected $keyboard = array(
        'inline_keyboard' => array(
            array()
        )
    );

    public function newLine()
    {
        $this->keyboard['inline_keyboard'][] = array();
    }

    public function newButton($text,$options, $line = -1)
    {
        $line = ($line < 0)? count($this->keyboard['inline_keyboard']) -$line - 2: $line;
        $options['text'] = $text;
        $this->keyboard['inline_keyboard'][$line][] = $options;
    }

}
