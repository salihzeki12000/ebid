<?php
/**
 * Created by PhpStorm.
 * User: Elaine
 * Date: 11/23/14
 * Time: 8:01 PM
 */

namespace ebid\Entity;


class Category extends baseEntity {
    public $categoryId;
    public $cname;
    public $parentId;
    public $childrenId;
} 