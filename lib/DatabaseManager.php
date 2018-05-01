<?php

class DatabaseManager {

    private static $__connection;   // $connection   das __ heißt private

    public static function getConnection() {
        
        if (!isset(self::$__connection)) {
            // echo "connecting to database";
            try {
                $s = "mysql:host=localhost;dbname=" . ApplicationConfig::$databaseName . ";charset=utf8";
                self::$__connection = new \PDO($s, ApplicationConfig::$databaseUsername, ApplicationConfig::$databasePassword);
                // echo 'Connected to database';
            }
            catch(PDOException $e)
            {
                \Logger::logError("Fatal Error - could not connect to Database" , $e->getMessage());
                readfile('static/500.html');
                exit();
            }
        }
        // var_dump(self::$__connection);
        if (self::$__connection  == null) {
            \Logger::logError("Fatal Error - could not connect to Database" , $e->getMessage());
        }
        return self::$__connection;
    }

    public static function query($connection, $query, $parameters = array()) {
        // echo "query   querystring = " .$query . "<br>";
        $statement = $connection->prepare($query);
        $i = 1; 


        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        foreach($parameters as $param) {
            if (is_int($param)) {
                $statement->bindValue($i, $param, \PDO::PARAM_INT);

            }
            if (is_string($param)) {
                $statement->bindValue($i, $param, \PDO::PARAM_STR);
            }
            $i++;
        }

        \Logger::logQuery($query, $parameters);

        try {
            $statement->execute();
        }
        catch(PDOException $e)
        {
            \Logger::logDebug("Fatal Error in 'query' - could not exeute query: $query", $e->getMessage());
            \Logger::logError("Fatal Error in 'query' - could not exeute query" , $e->getMessage());
            readfile('static/500.html');
            exit();
        }

        // \Util::my_var_dump($statement->debugDumpParams(), "statement - debugDUmpParams");
        return $statement; 
    }

    public static function fetchObject($cursor) {
        return $cursor->fetchObject();
    }

    public static function fetchAssoz($cursor) {
        return $cursor->fetch();
    }

    public static function closeConnection() {
        self::$__connection == null; 
    }

    public static function getUserByUserName(string $username) {
        $user = null; 
        $con = self::getConnection();
        
        $sql = "SELECT id, username, password FROM user WHERE username = ?";

        $res = self::query($con, $sql, array($username));
        if ($u = self::fetchObject($res)) {
            $user = new User($u->id, $u->username, $u->password);
        }
        self::closeConnection();
        return $user;
      }
}
