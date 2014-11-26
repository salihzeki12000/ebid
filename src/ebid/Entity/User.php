<?php
namespace ebid\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 *
 * @author yanwsh
 *        
 */
class User extends baseEntity implements AdvancedUserInterface
{ 
    public $uid;
    public $username;
    public $password;
    public $email;
    public $address;
    public $state;
    public $zipcode;
    public $roles;
    private $enabled;
    private $accountNonExpired;
    private $credentialsNonExpired;
    private $accountNonLocked;

    public function __construct($id = 0, $username, $password = "", $email= "", $address= "", $state= "", $zipcode= "", $roles= "", $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->uid = $id;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->address = $address;
        $this->state = $state;
        $this->zipcode = $zipcode;
        $this->roles = $roles;
        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    public function getId(){
        return $this->uid;
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    public function getAddress(){
        return $this->address;
    }
    
    public function getState(){
        return $this->state;
    }
    
    public function getZipCode(){
        return $this->zipcode;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getRoles(){
        $arrayroles = explode(",", $this->roles);
        return $arrayroles;
    }
    
    public function getRolesString(){
        return $this->roles;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }
 
}

?>