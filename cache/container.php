<?php

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * CachedContainer
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class CachedContainer extends Container
{
    private $parameters;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->parameters = $this->getDefaultParameters();

        $this->services =
        $this->scopedServices =
        $this->scopeStacks = array();

        $this->set('service_container', $this);

        $this->scopes = array();
        $this->scopeChildren = array();
        $this->methodMap = array(
            'controllerresolver' => 'getControllerresolverService',
            'db' => 'getDbService',
            'eventdispatcher' => 'getEventdispatcherService',
            'httpkernel' => 'getHttpkernelService',
            'session' => 'getSessionService',
        );

        $this->aliases = array();
    }

    /**
     * Gets the 'controllerresolver' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerResolver A Symfony\Component\HttpKernel\Controller\ControllerResolver instance.
     */
    protected function getControllerresolverService()
    {
        return $this->services['controllerresolver'] = new \Symfony\Component\HttpKernel\Controller\ControllerResolver();
    }

    /**
     * Gets the 'db' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \ebid\Db\mysql A ebid\Db\mysql instance.
     */
    protected function getDbService()
    {
        return $this->services['db'] = new \ebid\Db\mysql('localhost', 'wy590204', 64011402, 'wy590204db', 'ebid_');
    }

    /**
     * Gets the 'eventdispatcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher A Symfony\Component\EventDispatcher\EventDispatcher instance.
     */
    protected function getEventdispatcherService()
    {
        return $this->services['eventdispatcher'] = new \Symfony\Component\EventDispatcher\EventDispatcher();
    }

    /**
     * Gets the 'httpkernel' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel A Symfony\Component\HttpKernel\HttpKernel instance.
     */
    protected function getHttpkernelService()
    {
        return $this->services['httpkernel'] = new \Symfony\Component\HttpKernel\HttpKernel($this->get('eventdispatcher'), $this->get('controllerresolver'));
    }

    /**
     * Gets the 'session' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session A Symfony\Component\HttpFoundation\Session\Session instance.
     */
    protected function getSessionService()
    {
        return $this->services['session'] = new \Symfony\Component\HttpFoundation\Session\Session();
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        $name = strtolower($name);

        if (!(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $this->parameterBag = new FrozenParameterBag($this->parameters);
        }

        return $this->parameterBag;
    }
    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'database_host' => 'localhost',
            'database_name' => 'wy590204db',
            'database_user' => 'wy590204',
            'database_password' => 64011402,
            'database_prefix' => 'ebid_',
        );
    }
}
