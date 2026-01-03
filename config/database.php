    <?php

    class Database
    {
        private static $instance = null;

        private static $host = "localhost";
        private static $db   = "buymatch";
        private static $user = "root";
        private static $pass = "abha11228899";

        private function __construct() {}
        private function __clone() {}

        public static function getInstance()
        {
            if (self::$instance === null) {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8mb4";

                self::$instance = new PDO($dsn, self::$user, self::$pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }

            return self::$instance;
        }
    }
    ?>