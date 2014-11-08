<?php
namespace ebid\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
/**
 *
 * @author yanwsh
 *        
 */
class UserAuthenticationListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;
    
    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;
    
    private $sessionStrategy;
    
    /**
     * @var string Uniquely identifies the secured area
     */
    private $providerKey;
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager,
        $providerKey, SessionAuthenticationStrategyInterface $sessionStrategy){
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->sessionStrategy = $sessionStrategy;
    }
    
    public function handle(GetResponseEvent $event){
        $request = $event->getRequest();
        
        $token = $this->securityContext->getToken();
        
        $username = $request->query->get("uname");
        $password = $request->query->get("passwd");
        
        if($username == null || $password == null){
            return;
        }
        
        $unauthenticatedToken = new UsernamePasswordToken(
            $username,
            $password,
            $this->providerKey
        );
        
        $authenticatedToken = $this
        ->authenticationManager
        ->authenticate($unauthenticatedToken);
        
        $this->securityContext->setToken($authenticatedToken);
        
        if ($authenticatedToken instanceof TokenInterface) {
            $this->sessionStrategy->onAuthentication($request, $authenticatedToken);
        }
    }
}

?>