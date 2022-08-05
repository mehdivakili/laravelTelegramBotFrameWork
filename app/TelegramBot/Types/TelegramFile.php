<?php

namespace App\TelegramBot\Types;

use Illuminate\Support\Facades\Storage;

class TelegramFile
{
    public $data;

    public function __construct($path, $data = null)
    {
        $this->path = $path;
        if ($data == null) {
            if (Storage::exists($path)) {
                $this->data = Storage::get($path);
            }
        }
        else {
            $this->data = $data;
        }
        preg_match('/[\/\\\]?(.+)$/', $path, $matches);
        $this->name = $matches[1];
    }
}
