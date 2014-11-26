<?php
/**
 * Created by PhpStorm.
 * User: Elaine
 * Date: 11/23/14
 * Time: 4:46 PM
 */

namespace ebid\Entity;


class Product extends baseEntity {
    public $pid;
    public $pname;
    public $description;
    public $startPrice;
    public $expectPrice;
    public $buyNowPrice;
    public $defaultImage;
    public $imageLists;
    public $startTime;
    public $endTime;
    public $categoryId;
    public $shippingType;
    public $shippingCost;
    public $auction;

}