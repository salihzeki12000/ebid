<?php
/**
 * Created by PhpStorm.
 * User: Elaine
 * Date: 11/23/14
 * Time: 4:52 PM
 */

namespace ebid\Entity;


class baseEntity {
    public function set($data) {
        foreach ($data AS $key => $Value)
            if(property_exists($this, $key)){
                if(is_array($Value)){
                    foreach ($Value as $item) {
                        $this->{$key}[] = $item;
                    }
                }else {
                    $this->{$key} = $Value;
                }
            }
    }

    public function setFromDb($data){
        foreach ($data AS $key => $Value)
            if(property_exists($this, $key)){
                if(is_array($Value)){
                    foreach ($Value as $item) {
                        $this->{$key}[] = $item;
                    }
                }else {
                    $this->{$key} = stripslashes($Value);
                }
            }
    }

    public function isValid($data){
        foreach ($data AS $key => $Value){
            if(!property_exists($this, $key)){
                return false;
            }
        }
        return true;
    }
} 