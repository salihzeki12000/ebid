<?php
/**
 * Created by PhpStorm.
 * User: yanwsh
 * Date: 11/29/14
 * Time: 10:10 PM
 */

namespace ebid\Event;

use Symfony\Component\EventDispatcher\Event;
use ebid\Entity\User;

class RegisterEvent extends Event {
    const REGISTER = 'ebid.register';

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(){
        return $this->user;
    }
} 