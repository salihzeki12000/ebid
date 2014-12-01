<?php
namespace ebid\Db;

use ebid\Db\mysql;
use Symfony\Component\Config\Definition\Exception\Exception;

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

    /**
     * fetch data from database
     * @param $entity model need to fetch
     * @param null $condition fetch data condition
     * @param null $sort sort condition
     * @return array an array of objects
     */
    public function select($entity, $condition = NULL, $sort = NULL, $field = NULL){
        if($field == NULL){
            $field = '*';
        }else{
            $field = implode(",", $field);
        }
        $sql = "SELECT ". $field. " FROM ". _table($this->parse_classname(get_class($entity)));
        if($condition){
            $sql .= " WHERE $condition";
        }
        if($sort){
            $sql .= " ORDER BY $sort";
        }

        $result = $this->mysql->executeSQLASSOCResult($sql);

        return $result;
    }

    /**
     * insert data to database
     * @param $entity model need to insert
     * @param array $exclude the fields which do not need to insert
     * @param array $restrict the fields which don't need to add quote mark
     */
    public function insert($entity, $exclude = array("id"), $restrict = array()){
        $keys = array();
        $values = array();
        $sql = "INSERT INTO ". _table($this->parse_classname(get_class($entity)));
        foreach ($entity AS $key => $value){
            if(!in_array($key, $exclude)){
                $keys[] = "`{$key}`";
                if(is_array($value)){
                    $value = json_encode($value);
                }
                $value = addslashes($value);
                if(in_array($key, $restrict)){
                    if($value == null){
                        $values[] = "NULL";
                    }else{
                        $values[] = "{$value}";
                    }
                }else{
                    $values[] = "'{$value}'";
                }
            }
        }
        
        $sql .= "(" . implode(",", $keys) . ") VALUES (" . implode(",", $values) . ")";
        $this->mysql->executeSQL($sql);
    }

    /**
     * update model to database
     * @param $entity model need to update
     * @param array $exclude the fields which don't need to update
     * @param array $restrict the fields which don't need to add quote mark
     * @param string $searchId update condition
     */
    public function update($entity, $exclude = array("id"), $restrict = array(), $searchId = "id"){
        $field = array();
        $sql = "UPDATE ". _table($this->parse_classname(get_class($entity))) . " SET ";
        foreach ($entity AS $key => $value){
            if(!in_array($key, $exclude)){
                if(is_array($value)){
                    $value = json_encode($value);
                }
                if($key == 'condition'){
                    $key = '`condition`';
                }
                if($key == 'status'){
                    $key = '`status`';
                }
                $value = addslashes($value);
                if(in_array($key, $restrict)){
                    if($value == null){
                        $field[] = " $key = NULL ";
                    }else{
                        $field[] = " $key = $value ";
                    }

                }else{
                    $field[] = " $key = '$value' "; 
                }
            }
        }
        $sql .= implode(",", $field) . "WHERE $searchId=". $entity->{$searchId};
        $this->mysql->executeSQL($sql);
    }

    /**
     * update model to database
     * @param $entity model need to update
     * @param array $include the fields which need to update
     * @param array $restrict the fields which don't need to add quote mark
     * @param string $searchId update condition
     */
    public function updateSpecific($entity, $include = array(), $restrict = array(), $searchId = "id"){
        $field = array();
        $sql = "UPDATE ". _table($this->parse_classname(get_class($entity))) . " SET ";
        foreach ($entity AS $key => $value){
            if(!in_array($key, $include)) continue;
            if(is_array($value)){
                $value = json_encode($value);
            }
            if($key == 'condition'){
                $key = '`condition`';
            }
            if($key == 'status'){
                $key = '`status`';
            }
            $value = addslashes($value);
            if(in_array($key, $restrict)){
                if($value == null){
                    $field[] = " $key = NULL ";
                }else{
                    $field[] = "$key = $value";
                }

            }else{
                $field[] = "$key = '$value'";
            }
        }
        $sql .= implode(",", $field) . " WHERE $searchId=". $entity->{$searchId};
        $this->mysql->executeSQL($sql);
    }

    /**
     * delete model to database
     * @param $entity model need to update
     * @param string $searchId delete condition
     */
    public function delete($entity, $searchId = "id"){
        $sql = "DELETE FROM ". _table($this->parse_classname(get_class($entity))) . "WHERE $searchId=". $entity->{$searchId};
        $this->mysql->executeSQL($sql);
    }

    public function query($sql){
        return $this->mysql->executeSQLASSOCResult($sql);
    }
    
    function parse_classname($name){
        return join('', array_slice(explode('\\', $name), -1));
    }
}

?>