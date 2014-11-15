<?php
namespace ebid\Entity;

class Result
{
    const SUCCESS = 0;
    const FAILURE = 1;
    const LOGIN_REQUIRE = 2;
    const DUPLICATE = 3;
    const INTERNAL_ERROR = 4;
    public $type;
    public $data;
    
    public function __construct($type, $data = NUll){
        $this->type = $type;
        $this->data = $data;
    }
    
    public function set($data) {
        foreach ($data AS $key => $Value)
            $this->{$key} = $Value;
    }
}

?>