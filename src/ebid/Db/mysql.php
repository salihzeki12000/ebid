<?php
/*
 Name: Wensheng Yan
 */
namespace ebid\Db;

   class mysql{
        private $dbhost;
        private $dbuser;
        private $dbpass;
        private $dbname;
        private $conn;

        public function __construct($dbhost, $dbuser, $dbpass, $dbname){
            $this->dbhost = $dbhost;
            $this->dbuser = $dbuser;
            $this->dbpass = $dbpass;
            $this->dbname = $dbname;
        }
        
        public function connect(){
            $this->conn = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass)
                or die (mysql_error());
            
            mysql_select_db($this->dbname)
                or die (mysql_error());
        }
        
        public function executeSQLRowResult($dbquery){
            //*** execute the query
            $result = mysql_query($dbquery);
            
            //*** die if no result
            if (!$result)
                die("Query Failed.");
            
            $array = array();
            for ($i = 0; $i < mysql_num_rows($result); $i++){
                array_push($array, mysql_fetch_row($result));
            }
            
            //*** Free the resources associated with the result
            mysql_free_result($result);
            
            return $array;
        }
        
        public function executeSQLASSOCResult($dbquery){
            //*** execute the query
            $result = mysql_query($dbquery);
            
            //*** die if no result
            if (!$result)
                die("Query Failed.");
            $array = array();
            for ($i = 0; $i < mysql_num_rows($result); $i++){
                array_push($array, mysql_fetch_assoc($result));
            }
            mysql_free_result($result);
            
            return $array;
        }
        
        public function executeSQL ($dbquery) {
            return mysql_query($dbquery);
        }
        
        public function disconnect(){
            if($this->conn){
                mysql_close($this->conn);
            }
        }
    }
?>