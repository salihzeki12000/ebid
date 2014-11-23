<?php
namespace ebid\Db;

use ebid\Db\mysql;
/**
 *
 * @author yanwsh
 *        
 */
class MySQLParser
{
    private $mysql;
    
    function __construct(mysql $mysql)
    {
        $this->mysql = $mysql;
    }
    
    public function select($entity, $condition = NULL, $sort = NULL){
        $sql = "SELECT * FROM ". _table($this->parse_classname(get_class($entity)));
        if($condition){
            $sql .= " WHERE $condition";
        }
        if($sort){
            $sql .= " ORDER BY $sort";
        }

        $result = $this->mysql->executeSQLASSOCResult($sql);

        return $result;
    }
    
    public function insert($entity, $exclude = array("id"), $restrict = array()){
        $keys = array();
        $values = array();
        $sql = "INSERT INTO ". _table($this->parse_classname(get_class($entity)));
        foreach ($entity AS $key => $value){
            if(!in_array($key, $exclude)){
                $keys[] = "`{$key}`";
                $value = addslashes($value);
                if(in_array($key, $restrict)){
                    $values[] = "{$value}";
                }else{
                    $values[] = "'{$value}'";
                }
            }
        }
        
        $sql .= "(" . implode(",", $keys) . ") VALUES (" . implode(",", $values) . ")";
        $this->mysql->executeSQL($sql);
    }
    
    public function update($entity, $exclude = array("id"), $restrict = array(), $searchId = "id"){
        $field = array();
        $sql = "UPDATE ". _table($this->parse_classname(get_class($entity))) . " SET ";
        foreach ($entity AS $key => $value){
            if(!in_array($key, $exclude)){
                $value = addslashes($value);
                if(in_array($key, $restrict)){
                    $field[] = " $key = $value ";
                }else{
                    $field[] = " $key = '$value' "; 
                }
            }
        }
        $sql .= implode(",", $field) . "WHERE $searchId=". $this->{$searchId};
        $this->mysql->executeSQL($sql);
    }
    
    public function updateSpecific($entity, $include = array(), $restrict = array(), $searchId = "id"){
        $field = array();
        $sql = "UPDATE ". _table($this->parse_classname(get_class($entity))) . " SET ";
        foreach ($include AS $key => $value){
                $value = addslashes($value);
                if(in_array($key, $restrict)){
                    $field[] = "$key = $value";
                }else{
                    $field[] = "$key = '$value'";
                }
        }
        $sql .= implode(",", $field) . "WHERE $searchId=". $this->{$searchId};
        $this->mysql->executeSQL($sql);
    }
    
    public function delete($entity, $searchId = "id"){
        $sql = "DELETE FROM ". _table($this->parse_classname(get_class($entity))) . "WHERE $searchId=". $this->{$searchId};
        $this->mysql->executeSQL($sql);
    }
    
    function parse_classname($name){
        return join('', array_slice(explode('\\', $name), -1));
    }
}

?>