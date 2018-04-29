<?php

class RouteHandler {

    private static $requestParams = null;
    private static $requestPath = null; 

    private function sanitizeRequestParams() {
        //TODO iterate over $_REQUEST and extract all params with prefix grg_ and sanitize them 
    }

    private static function sanitizeURL() {
        self::$requestPath = $_SERVER['REDIRECT_URL'];
        //TODO  sanitize $_SERVER['REDIRECT_URL'] 
    }

    public static function handleRoute($db_conn)  {
        self::sanitizeURL();

        $verb = $_SERVER['REQUEST_METHOD'];
        $requestPath2 = self::$requestPath;

        if ($verb == "POST") {
            sanitizeRequestParams();
        }

        $sql = "SELECT * FROM route WHERE verb = ? AND route = ?";
        $params = array($verb);
        $params[] = self::$requestPath;
        // possible parameter in path
        if (substr_count(self::$requestPath, '/') > 1) {
            $pos = strrpos(self::$requestPath, "/");

            $requestPath2 = substr(self::$requestPath, 0, $pos);

            // \Util::my_var_dump($requestPath, "requestpath = ");
            // \Util::my_var_dump($requestPath2, "requestpath2 = ");
            $sql = "SELECT * FROM route WHERE verb = ? AND (route = ? OR (route = ? AND routeparam IS NOT NULL))";
            $params[] = $requestPath2;
        }
        $res = \DatabaseManager::query($db_conn, $sql, $params);
       //  \Util::my_var_dump($res, "res = ");
        // exit();

        if ($res->rowCount() == 0) {
            if ($_GET)
            \Logger::logWarning("404 - could not find page: " , $self::$requestPath);
            readfile('static/404.html');
            exit();
        } else {
            $route = \DatabaseManager::fetchAssoz($res);
            // \Util::my_var_dump($route, "route = ");
        }

        return $route; 
    }
}