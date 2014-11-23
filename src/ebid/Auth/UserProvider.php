<?php
namespace ebid\Auth;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use ebid\Entity\User;
use ebid\Db\MySQLParser;

/**
 *
 * @author yanwsh
 *        
 */
class UserProvider implements UserProviderInterface
{
    private $parser;
    
    function __construct(MySQLParser $parser)
    {
        $this->parser = $parser;
    }
    
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = new User(0, $username);
        $result = $this->parser->select($user, "username = '". addslashes($username). "'");
        if(count($result) == 1){
            $user->set($result[0]);
        }
        else{
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);
        
            throw $ex;
        }
    
        return new User($user->getId(), $user->getUsername(), $user->getPassword(), 
            $user->getEmail(), $user->getAddress(), $user->getState(),$user->getZipCode(),
            $user->getRolesString(), $user->isEnabled(), $user->isAccountNonExpired(),
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
        return $class === 'ebid\Entity\User';
    }
}

?>