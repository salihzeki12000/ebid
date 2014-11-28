<?php
/**
 * Created by PhpStorm.
 * User: Elaine
 * Date: 11/23/14
 * Time: 4:46 PM
 */

namespace ebid\Entity;


class Product extends baseEntity {
    const INITIAL = 0;
    const BIDDING = 1;
    const END = 2;
    const CLOSE = 3;
    public $pid;
    public $pname;
    public $description;
    public $startPrice;
    public $expectPrice;
    public $buyNowPrice;
    public $currentPrice;
    public $defaultImage;
    public $imageLists;
    public $startTime;
    public $endTime;
    public $categoryId;
    public $shippingType;
    public $shippingCost;
    public $auction;
    public $seller;
    public $condition;
    public $status;

}