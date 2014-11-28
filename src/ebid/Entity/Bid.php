<?php
/**
 * Created by PhpStorm.
 * User: Elaine
 * Date: 11/25/14
 * Time: 10:49 PM
 */

namespace ebid\Entity;


class Bid extends baseEntity {
    const WIN = 0;
    const NOTWIN = 1;

    public $bid;
    public $uid;
    public $pid;
    public $bidPrice;
    public $bidTime;
    public $status;
} 