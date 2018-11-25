<?php

namespace Info;

class db
{
    public $connection;
    private $server, $username, $password, $database;
    
    public function __construct($host,$database,$db_username,$db_password)
    {
        $this->server = $host;
        $this->username = $db_username;
        $this->password = $db_password;
        $this->database = $database;
        $this->connect();
    }
    
    public function get_connection()
    {
        return $this->connection;
    }
    
    private function connect()
    {
        try {
            $this->connection = new \PDO("mysql:host=".$this->server.";dbname=".$this->database, $this->username, $this->password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    public function __sleep()
    {
        return array('server','username','password','database');
    }
    
    public function __wakeup()
    {
        $this->connect();
    }
}
