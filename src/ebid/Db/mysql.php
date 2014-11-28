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
        private $prefix;
        private $conn;

        public function __construct($dbhost, $dbuser, $dbpass, $dbname, $prefix){
            $this->dbhost = $dbhost;
            $this->dbuser = $dbuser;
            $this->dbpass = $dbpass;
            $this->dbname = $dbname;
            $this->prefix = $prefix;
        }
        
        public function connect(){
            $this->conn = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
            mysql_select_db($this->dbname);
        }
        
        public function executeSQLRowResult($dbquery){
            //*** execute the query
            $result = mysql_query($dbquery);
            
            //*** die if no result
            if (!$result)
                throw new \Exception(mysql_error());
            
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
                throw new \Exception(mysql_error());
            
            $array = array();
            for ($i = 0; $i < mysql_num_rows($result); $i++){
                array_push($array, mysql_fetch_assoc($result));
            }
            mysql_free_result($result);
            
            return $array;
        }
        
        public function executeSQL($dbquery) {
            $result = mysql_query($dbquery);
            if(mysql_errno()){
                throw new \Exception("MySQL error ". mysql_errno() . ": ". mysql_error());
            }
            return $result;
        }
        
        public function disconnect(){
            if($this->conn){
                mysql_close($this->conn);
            }
        }
        
        public function getPrefix(){
            return $this->prefix;
        }
    }
?>