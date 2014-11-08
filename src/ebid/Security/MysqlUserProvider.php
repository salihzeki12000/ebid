<?php
namespace Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
/**
 *
 * @author yanwsh
 *        
 */
class MysqlUserProvider implements UserProviderInterface
{
    function __construct()
    {
    }
    
    /**
     * Adds a new User to the provider.
     *
     * @param UserInterface $user A UserInterface instance
     *
     * @throws \LogicException
     */
    public function createUser(UserInterface $user)
    {
        if (isset($this->users[strtolower($user->getUsername())])) {
            throw new \LogicException('Another user with the same username already exists.');
        }
    
        //$this->users[strtolower($user->getUsername())] = $user;
    }
    
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (!isset($this->users[strtolower($username)])) {
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);
    
            throw $ex;
        }
    
        $user = $this->users[strtolower($username)];
    
        return new User($user->getUsername(), $user->getPassword(), $user->getRoles(), $user->isEnabled(), $user->isAccountNonExpired(),
            $user->isCredentialsNonExpired(), $user->isAccountNonLocked());
    }
    
    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
    
        return $this->loadUserByUsername($user->getUsername());
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}

?>