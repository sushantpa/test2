<?php

    class DBDriver{
        private $mysql_con;
        private $pg_con;


        function connectPgSql($dbName){
          require_once(__DIR__.'/constants.php');
          try {
              $this->pg_con = new PDO("pgsql:host=" . PG_DB_HOST . ";dbname=" . $dbName, PG_DB_USER, PG_DB_PASSWORD);
              $this->pg_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              return $this->pg_con;
              }
            catch(PDOException $e) {
              echo "Connection to ". $dbName. " failed: " . $e->getMessage(). "\n";
              return null;
            }
      }

        function connectMKT(){
            require_once(__DIR__.'/constants.php');
            try {
                $this->mysql_con = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
                $this->mysql_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo "Connected successfully \n";
                return $this->mysql_con;
                }
              catch(PDOException $e) {
                echo "Connection failed: " . $e->getMessage(). "\n";
                return null;
              }
        }  
    }
    
    ?> 