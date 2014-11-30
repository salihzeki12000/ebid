<?php
/**
 * Created by PhpStorm.
 * User: yanwsh
 * Date: 11/29/14
 * Time: 11:48 PM
 */

namespace ebid\Event;

use Symfony\Component\EventDispatcher\Event;

class BidResultEvent extends Event {
    const BIDRESULT = 'ebid.bid.result';

    protected $winlists, $loselists;

    public function __construct($winlists, $loselists)
    {
        $this->winlists = $winlists;
        $this->loselists = $loselists;
    }

    public function getWinLists(){
        return $this->winlists;
    }

    public function getLoseLists(){
        return $this->loselists;
    }
} 