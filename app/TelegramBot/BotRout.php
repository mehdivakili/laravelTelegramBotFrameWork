<?php


namespace App\TelegramBot;


class BotRout
{


    /**
     * @var array
     */
    public static $routes = [0 => ['only' => [], 'except' => [], 'any' => []]];
    private static $status = 0;
    private static $allowed_updates = ['message'];
    public static $types = ['text', 'animation', 'audio', 'document', 'photo', 'sticker', 'video', 'video_note', 'voice', 'contact', 'dice', 'game', 'poll', 'venue', 'location'];
    private static $middleware = true;


    /**
     *
     * @param $regex
     * @param $action
     */


    public static function any($regex, $action, $status = 0, $allowed_updates = ['message'],$middleware = true)
    {
        $status = ($status == 0) ? self::$status : $status;
        $middleware = ($middleware === true) ? self::$middleware : $middleware;
        foreach ($allowed_updates as $allowed_update)
            self::$routes[$status][$allowed_update]['any'][] = ['regex' => $regex, 'action' => $action,'middleware'=>$middleware];
    }

    public static function default($action, $status = 0, $allowed_updates = ['message'])
    {
        $status = ($status == 0) ? self::$status : $status;
        foreach ($allowed_updates as $allowed_update)
            self::$routes[$status][$allowed_update]['default'] = $action;
    }

    public static function group($options, $callback)
    {
        $defaults = ["status" => 0, "allowed_updates" => ['message'],'middleware'=>true];
        $options = array_merge($defaults, $options);
        $temp_status = self::$status;
        $temp_allowed_updates = self::$allowed_updates;
        $temp_middleware = self::$middleware;
        self::$status = $options["status"];
        self::$allowed_updates = $options["allowed_updates"];
        if (!isset(self::$routes[self::$status]))
            self::$routes[self::$status] = ['only' => [], 'except' => [], 'any' => []];
        $callback();
        self::$status = $temp_status;
        self::$allowed_updates = $temp_allowed_updates;
        self::$middleware = $temp_middleware;
    }

    public static function only($types, $regex, $action, $status = 0, $allowed_updates = ['message'],$middleware = true)
    {
        $status = ($status == 0) ? self::$status : $status;
        $middleware = ($middleware == true) ? self::$middleware : $middleware;

        foreach ($allowed_updates as $allowed_update)
            foreach ($types as $type) {
                self::$routes[$status][$allowed_update]['only'][$type][] = ['regex' => $regex, 'action' => $action,'middleware'=>$middleware];
            }
    }

    public static function except($types, $regex, $action, $status = 0, $allowed_updates = ['message'],$middleware = true)
    {
        $status = ($status == 0) ? self::$status : $status;
        $middleware = ($middleware == true) ? self::$middleware : $middleware;
        foreach ($allowed_updates as $allowed_update)
            foreach ($types as $type)
                self::$routes[$status][$allowed_update]['except'][$type][] = ['regex' => $regex, 'action' => $action,'middleware'=>$middleware];
    }

    public static function callback($regex, $action, $status = 0)
    {
        $status = ($status == 0) ? self::$status : $status;
        self::$routes[$status]['callback'][] = ['regex' => $regex, 'action' => $action];
    }
}
