<?php
class Database
{
    public static $conn;

    public static function setupConn()
    {
        $servername = "localhost";
        $username = "root";
        $password = "Vimandya@20030908";
        $dbname = "nowaste_db"; 
        $port = 3306;

        if (!isset(Database::$conn)) {
            Database::$conn = new mysqli($servername, $username, $password, $dbname, $port);

             
            // Check for connection errors
            if (Database::$conn->connect_error) {
                die("Connection failed: " . Database::$conn->connect_error);
            }
        }
    }

    public static function iud($q)
    {
        Database::setupConn();
        if (Database::$conn->query($q) === FALSE) {
            die("Error executing query: " . Database::$conn->error);
        }
    }

    public static function search($q)
    {
        Database::setupConn();
        $resultSet = Database::$conn->query($q);
        if ($resultSet === FALSE) {
            die("Error executing query: " . Database::$conn->error);
        }
        return $resultSet;
    }

    public static function closeConn()
    {
        if (isset(Database::$conn)) {
            Database::$conn->close();
            //unset(Database::$conn);
            Database::$conn = null; 
        }
    }
}
?>
