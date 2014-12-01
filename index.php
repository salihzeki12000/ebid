<?php
require_once __DIR__ . '/vendor/autoload.php';

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
use Symfony\Component\Routing\Router; 
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader as DIYamlFileLoader;
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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use ebid\RouterExceptionListener;
use ebid\Auth\AuthenticationSuccessHandler;
use ebid\Auth\AuthenticationFailureHandler;
use ebid\Auth\LogoutSuccessHandler;
use ebid\Event\RegisterEvent;
use ebid\Event\EmailListener;
use ebid\Event\BidResultEvent;

error_reporting(E_ERROR);
ini_set('display_errors','On');

$isDebug = true;
$sendEmail = true;
$root = __DIR__;
$file = __DIR__ .'/cache/container.php';

date_default_timezone_set("America/New_York");

$containerConfigCache = new ConfigCache($file, $isDebug);

if (!$isDebug && file_exists($file) && $containerConfigCache->isFresh()) {
    require_once $file;
    $container = new CachedContainer();
} else {
    //Dependency Injection
    $container = new ContainerBuilder();
    
    $loader = new DIYamlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('config/services.yml');
    
    $container->compile();

    if (!$isDebug) {
        $dumper = new PhpDumper($container);
        file_put_contents(
        $file,
        $dumper->dump(array('class' => 'CachedContainer'))
        );
    }
}


$request = Request::createFromGlobals();
//$session = new Session();
$session = $container->get('Session');
$session->start();
$request->setSession($session);

//$dispatcher = new EventDispatcher();
$dispatcher = $container->get('EventDispatcher');
//$resolver = new ControllerResolver();
$resolver = $container->get('ControllerResolver');
// instantiate the kernel
//$kernel = new HttpKernel($dispatcher, $resolver);
$kernel = $container->get('HttpKernel');

$SmtpTransport = $container->get('SmtpTransport');
$SmtpTransport->setUsername($container->getParameter('mail_username'))
    ->setPassword($container->getParameter('mail_password'));

//routing

$requestContext = new RequestContext();
$requestContext->fromRequest($request);

$router = new Router(
    new YamlFileLoader(new FileLocator(__DIR__)),
    'config/routes.yml',
    array('cache_dir' => __DIR__.'/cache'),
    $requestContext
);
$routeCollection = $router->getRouteCollection();

$matcher = new UrlMatcher($routeCollection, $requestContext);
    

$dispatcher->addSubscriber(new RouterListener($matcher));
$dispatcher->addSubscriber(new ResponseListener('UTF-8'));

//security
$map = $session->get("firewall_context");
$defaultEncoder = $container->get('MessageDigestPasswordEncoder');
$encoders = array(
    'ebid\\Entity\\User' => $defaultEncoder
);

$encoderFactory = new EncoderFactory($encoders);
if($map == null){
    $urlgenerator = new UrlGenerator($routeCollection, $requestContext);
    $map = setupFireWall($kernel, $dispatcher, $urlgenerator);
    $session->set("firewall_context", $map);
}
$firewall = new Firewall($map, $dispatcher);
$dispatcher->addListener(
    KernelEvents::REQUEST,
    array($firewall, 'onKernelRequest')
);

if($sendEmail){
    $dispatcher->addListener(
        BidResultEvent::BIDRESULT, array(new EmailListener(), 'sendEmailOnBidFinish'),0
    );
    $dispatcher->addListener(
        RegisterEvent::REGISTER, array(new EmailListener(), 'sendEmailOnRegistration'),0
    );
}

$dispatcher->addListener(
   KernelEvents::EXCEPTION, array(new RouterExceptionListener(), 'onKernelException'), 0
);

$mysql = $container->get('db');
$mysql->connect();
// actually execute the kernel, which turns the request into a response
// by dispatching events, calling a controller, and returning the response
$response = $kernel->handle($request);

// send the headers and echo the content
$response->send();

// triggers the kernel.terminate event
$kernel->terminate($request, $response);


function _table($table) {
    global $container;
    $db = $container->get('db');
    return $db->getPrefix() . $table;
}

function setupFireWall($kernel, $dispatcher, $urlgenerator){  
    global $session, $container, $encoderFactory;
    $userProvider = $container->get('UserProvider');
    /*
    $userProvider = new InMemoryUserProvider(
        array(
            'admin' => array(
                // password is "foo"
                'password' => '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
                'roles'    => array('ROLE_ADMIN','ROLE_USER'),
            ),
        )
    );*/
    
    // for some extra checks: is account enabled, locked, expired, etc.?
    $userChecker = new UserChecker();
    
    $provider = new DaoAuthenticationProvider(
        $userProvider,
        $userChecker,
        'ebid.security',
        $encoderFactory,
        false
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

    $session->set("security_context", $securityContext);
    $httpUtils = new HttpUtils($urlgenerator);
    
    //$basicentrypoint = new BasicAuthenticationEntryPoint('Secured Demo Area');
    //$basiclistener = new BasicAuthenticationListener($securityContext, $authenticationManager, "ebid.security",$basicentrypoint);
    //$userlistener = new UserAuthenticationListener($securityContext, $authenticationManager, "ebid.security", new SessionAuthenticationStrategy(SessionAuthenticationStrategy::INVALIDATE));
    /*$userlistener = new UsernamePasswordFormAuthenticationListener($securityContext, $authenticationManager,
        new SessionAuthenticationStrategy(SessionAuthenticationStrategy::NONE), $httpUtils, "ebid.security",
        new DefaultAuthenticationSuccessHandler($httpUtils, array('default_target_path'=>'/admin/success')),
        new DefaultAuthenticationFailureHandler($kernel, $httpUtils, array('failure_path'=>'/foo/error')),
        array('check_path' => '/auth/check'));*/
    $userlistener = new UsernamePasswordFormAuthenticationListener($securityContext, $authenticationManager,
        new SessionAuthenticationStrategy(SessionAuthenticationStrategy::NONE), $httpUtils, "ebid.security",
        new AuthenticationSuccessHandler(),
        new AuthenticationFailureHandler(),
        array('check_path' => '/auth/login', 'require_previous_session'=> false));
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
    
    $requestMatcher = new RequestMatcher('^/auth/logout');
    $logoutlistener = new LogoutListener($securityContext, $httpUtils, new LogoutSuccessHandler(),
        array('logout_path'=> '/auth/logout'));
    $listeners = array($accessListener, $logoutlistener);
    $exceptionListener = new ExceptionListener($securityContext, $trustResolver, $httpUtils, "ebid.security",
        new FormAuthenticationEntryPoint($kernel, $httpUtils, '/auth/login', false));
    $map->add($requestMatcher, $listeners, $exceptionListener);
    
    $requestMatcher = new RequestMatcher('^/');
    $listeners = array($anonymouslistener, $userlistener, $accessListener);

    $map->add($requestMatcher, $listeners, $exceptionListener);
    

    return $map;
    //return new Firewall($map, $dispatcher);
    
}
