<?php

namespace App\TelegramBot\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\TelegramBot\BotRout;
use App\TelegramBot\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BotController extends Controller
{
    public function bot(TelegramBot $telegramBot, Request $request)
    {
        $telegramBot->data_init();

        $status = $telegramBot->chat->status;
        $routes = BotRout::$routes;
        $route_types = ['only', 'except', 'any'];
        $update_type = $telegramBot->get_update_type();
        if ($update_type == "callback_query"){
            foreach ($routes[$status]["callback"] as $route){
                if (preg_match($route['regex'], $telegramBot->text, $matches)) {
                    $class = $route['action'][0];
                    $method = $route['action'][1];
                    $class = new $class($telegramBot);
                    return call_user_func_array([$class, $method], array_slice($matches, 1));
                }
            }
            return 0;
        }

        foreach ($route_types as $route_type) {
            /*$cat_types = array_keys($routes[$status][$route_types]);*/
            if (!isset($routes[$status][$update_type][$route_type])) continue;
            foreach ($routes[$status][$update_type][$route_type] as $t => $route) {

                if ($route_type == 'only') {
                    if ($t != $telegramBot->message_type) continue;
                    else {
                        foreach ($route as $r) {
                            if ($telegramBot->pass_middlewares($r["middleware"])) {
                                if (preg_match($r['regex'], $telegramBot->text, $matches)) {
                                    $class = $r['action'][0];
                                    $method = $r['action'][1];
                                    $class = new $class($telegramBot);
                                    return call_user_func_array([$class, $method], array_slice($matches, 1));
                                }
                            }
                        }
                    }
                    continue;
                }
                if ($route_type == 'except') {
                    if ($t == $telegramBot->message_type) continue;
                    else {
                        foreach ($route as $r) {

                            //$telegramBot->send_text(json_encode($r));
                            if ($telegramBot->pass_middlewares($r["middleware"])) {
                                if (preg_match($r['regex'], $telegramBot->text, $matches)) {
                                    $class = $r['action'][0];
                                    $method = $r['action'][1];
                                    $class = new $class($telegramBot);
                                    return call_user_func_array([$class, $method], array_slice($matches, 1));
                                }

                            }
                        }
                    }
                    continue;
                }
                if (preg_match($route['regex'], $telegramBot->text, $matches)) {

                    if ($telegramBot->pass_middlewares($route["middleware"])) {
                        $class = $route['action'][0];
                        $method = $route['action'][1];
                        $class = new $class($telegramBot);
                        return call_user_func_array([$class, $method], array_slice($matches, 1));
                    }else{
                        return 0;
                    }
                }
            }
        }
        if (isset($routes[$status][$update_type]['default'])) {
            $class = $routes[$status][$update_type]['default'][0];
            $method = $routes[$status][$update_type]['default'][1];
            $class = new $class($telegramBot);
            return call_user_func_array([$class, $method], array());
            //return call_user_func_array($routes[$status]['default'], [$telegramBot]);
        } else {
            return false;
        }
    }


    public function set_webhook(TelegramBot $telegramBot)
    {
        return $telegramBot->set_webhook();
    }

    public function delete_webhook(TelegramBot $telegramBot)
    {
        return $telegramBot->delete_webhook();
    }

    public function restart_webhook(TelegramBot $telegramBot)
    {
        return $telegramBot->restart_webhook();
    }

}
