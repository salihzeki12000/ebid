<?php
namespace ebid\Entity;

class Result
{
    const SUCCESS = 0;
    const FAILURE = 1;
    const LOGIN_REQUIRE = 2;
    const DUPLICATE = 3;
    const INTERNAL_ERROR = 4;
    const EXPIRE = 5;
    public $type;
    public $message;
    public $data;
    
    public function __construct($type, $message = NULL, $data = NUll){
        $this->type = $type;
        $this->message = $message;
        $this->data = $data;
    }
    
    public function set($data) {
        foreach ($data AS $key => $Value)
            if(property_exists($this, $key)){
                $this->{$key} = addslashes($Value);
            }
    }
}

?>