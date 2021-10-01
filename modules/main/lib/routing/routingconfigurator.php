<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Main\Routing;

use Bitrix\Main\SystemException;

/**
 * @package    bitrix
 * @subpackage main
 *
 * @method RoutingConfiguration middleware($middleware)
 * @method RoutingConfiguration prefix($prefix)
 * @method RoutingConfiguration name($name)
 * @method RoutingConfiguration domain($domain)
 * @method RoutingConfiguration where($parameter, $pattern)
 * @method RoutingConfiguration default($parameter, $value)
 *
 * @method RoutingConfiguration get($uri, $controller)
 * @method RoutingConfiguration post($uri, $controller)
 * @method RoutingConfiguration put($uri, $controller)
 * @method RoutingConfiguration patch($uri, $controller)
 * @method RoutingConfiguration options($uri, $controller)
 * @method RoutingConfiguration delete($uri, $controller)
 * @method RoutingConfiguration head($uri, $controller)
 * @method RoutingConfiguration any($uri, $controller)
 *
 * @method RoutingConfiguration group($callback)
 */
class RoutingConfigurator
{
	/** @var Router */
	protected $router;

	/** @var Options Acts inside groups as a stack */
	protected $scopeOptions;

	/**
	 * RoutingConfigurator constructor.
	 */
	public function __construct()
	{
		$this->scopeOptions = new Options;
	}

	public function __call($method, $arguments)
	{
		// setting option
		if (in_array($method, Options::$optionList, true))
		{
			$configuration = $this->createConfiguration();
			return $configuration->$method(...$arguments);
		}

		// setting route
		if (in_array($method, RoutingConfiguration::$configurationList, true))
		{
			$configuration = $this->createConfiguration();
			return $configuration->$method(...$arguments);
		}

		throw new SystemException(sprintf(
			'Unknown method `%s` for object `%s`', $method, get_called_class()
		));
	}

	public function createConfiguration()
	{
		$configuration = new RoutingConfiguration;

		$configuration->setConfigurator($this);
		$this->router->registerConfiguration($configuration);

		$configuration->setOptions(clone $this->scopeOptions);

		return $configuration;
	}

	public function mergeOptionsWith($anotherOptions)
	{
		$this->scopeOptions->mergeWith($anotherOptions);
	}

	/**
	 * @return Router
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * @param Router $router
	 */
	public function setRouter($router)
	{
		$this->router = $router;
	}

	public function __clone()
	{
		$this->scopeOptions = clone $this->scopeOptions;
		$this->scopeOptions->clearCurrent();
	}
}