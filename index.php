<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use ebid\Db\mysql;
use ebid\RouterExceptionListener;

error_reporting(E_ERROR);
ini_set('display_errors','On');
//database setting
global $dbhost, $dbuser,$dbpass,$dbname;
$mysql = new mysql($dbhost, $dbuser, $dbpass, $dbname);


$request = Request::createFromGlobals();
$session = new Session();
$session->start();
$request->setSession($session);

$dispatcher = new EventDispatcher();

$resolver = new ControllerResolver();
// instantiate the kernel
$kernel = new HttpKernel($dispatcher, $resolver);

//routing

$context = new RequestContext();
$context->fromRequest($request);
if(null == $routes = $session->get("route_setting")){
     $routes = getRoutesConfig();
     $session->set("route_setting", $routes);
}
$matcher = new UrlMatcher($routes, $context);
    

$dispatcher->addSubscriber(new RouterListener($matcher));
$dispatcher->addSubscriber(new ResponseListener('UTF-8'));

//security
$map = $session->get("firewall_context");
if($map == null){
    $urlgenerator = new UrlGenerator($routes, $context);
    $map = setupFireWall($kernel, $dispatcher, $urlgenerator);
    $session->set("firewall_context", $map);
}
$firewall = new Firewall($map, $dispatcher);
$dispatcher->addListener(
    KernelEvents::REQUEST,
    array($firewall, 'onKernelRequest')
);

$dispatcher->addListener(
   KernelEvents::EXCEPTION, array(new RouterExceptionListener(), 'onKernelException'), 0
);

// actually execute the kernel, which turns the request into a response
// by dispatching events, calling a controller, and returning the response
$response = $kernel->handle($request);

// send the headers and echo the content
$response->send();

// triggers the kernel.terminate event
$kernel->terminate($request, $response);

//$contentType = $request->server->get("CONTENT_TYPE");
//echo $path = $request->getPathInfo();
/*
if($contentType != "application/json"){
    $response = new RedirectResponse('error.html');
}*/

function getRoutesConfig(){ 
    $locator = new FileLocator(array(__DIR__));
    $loader = new YamlFileLoader($locator);
    $collection = $loader->load('config/routes.yml');
    return $collection;
}

function _table($table) {
    global $prefix;
    return $prefix . $table;
}

function setupFireWall($kernel, $dispatcher, $urlgenerator){    
    $userProvider = new InMemoryUserProvider(
        array(
            'admin' => array(
                // password is "foo"
                'password' => '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
                'roles'    => array('ROLE_ADMIN'),
            ),
        )
    );
    
    // for some extra checks: is account enabled, locked, expired, etc.?
    $userChecker = new UserChecker();
    
    $defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
    $encoders = array(
        'Symfony\\Component\\Security\\Core\\User\\User' => $defaultEncoder
    );
    
    $encoderFactory = new EncoderFactory($encoders);
    
    $provider = new DaoAuthenticationProvider(
        $userProvider,
        $userChecker,
        'ebid.security',
        $encoderFactory
    );
    $authenticationManager = new AuthenticationProviderManager(array($provider));
    
    $anonymousClass = 'Symfony\Component\Security\Core\Authentication\Token\AnonymousToken';
    $rememberMeClass = 'Symfony\Component\Security\Core\Authentication\Token\RememberMeToken';
    
    $trustResolver = new AuthenticationTrustResolver($anonymousClass, $rememberMeClass);
    $authenticatedVoter = new AuthenticatedVoter($trustResolver);
    $roleVoter = new RoleVoter('ROLE_');
    $voters = array($authenticatedVoter, $roleVoter);
    $accessDecisionManager = new AccessDecisionManager($voters);
    
    $securityContext = new SecurityContext(
        $authenticationManager,
        $accessDecisionManager
    );
    
    $httpUtils = new HttpUtils($urlgenerator);
    
    //$basicentrypoint = new BasicAuthenticationEntryPoint('Secured Demo Area');
    //$basiclistener = new BasicAuthenticationListener($securityContext, $authenticationManager, "ebid.security",$basicentrypoint);
    //$userlistener = new UserAuthenticationListener($securityContext, $authenticationManager, "ebid.security", new SessionAuthenticationStrategy(SessionAuthenticationStrategy::INVALIDATE));
    $userlistener = new UsernamePasswordFormAuthenticationListener($securityContext, $authenticationManager,
        new SessionAuthenticationStrategy(SessionAuthenticationStrategy::NONE), $httpUtils, "ebid.security",
        new DefaultAuthenticationSuccessHandler($httpUtils, array('default_target_path'=>'/admin/success')),
        new DefaultAuthenticationFailureHandler($kernel, $httpUtils, array('failure_path'=>'/foo/error')),
        array('check_path' => '/admin'));
    $anonymouslistener = new AnonymousAuthenticationListener($securityContext, "ebid.security");
    
    $accessMap = new AccessMap();
    $requestMatcher = new RequestMatcher('^/admin');
    $accessMap->add($requestMatcher, array('ROLE_ADMIN'));
    
    $accessListener = new AccessListener(
        $securityContext,
        $accessDecisionManager,
        $accessMap,
        $authenticationManager
    );
    $map = new FirewallMap();
    
    $requestMatcher = new RequestMatcher('^/admin/logout');
    $logoutlistener = new LogoutListener($securityContext, $httpUtils, new DefaultLogoutSuccessHandler($httpUtils, '/foo/logoutsuccess'),
        array('logout_path'=> '/admin/logout'));
    $listeners = array($accessListener, $logoutlistener);
    $exceptionListener = new ExceptionListener($securityContext, $trustResolver, $httpUtils, "ebid.security",
        new FormAuthenticationEntryPoint($kernel, $httpUtils, '/foo/a', false));
    $map->add($requestMatcher, $listeners, $exceptionListener);
    
    $requestMatcher = new RequestMatcher('^/');
    $listeners = array($anonymouslistener, $userlistener, $accessListener);

    $map->add($requestMatcher, $listeners, $exceptionListener);
    

    return $map;
    //return new Firewall($map, $dispatcher);
    
}
