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
 */
class RoutingConfiguration
{
	/** @var RoutingConfigurator */
	protected $configurator;

	/** @var Route|\Closure One route or group of routes */
	protected $routeContainer;

	/** @var Options */
	protected $options;

	public static $configurationList = [
		'get', 'post', 'any', 'group'
	];

	public function __call($method, $arguments)
	{
		// setting option
		if (in_array($method, Options::$optionList, true))
		{
			$this->options->$method(...$arguments);
			return $this;
		}

		throw new SystemException(sprintf(
			'Unknown method `%s` for object `%s`', $method, get_called_class()
		));
	}

	/**
	 * @param RoutingConfigurator $configurator
	 */
	public function setConfigurator($configurator)
	{
		$this->configurator = $configurator;
	}

	/**
	 * @param Options $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}

	public function get($uri, $controller)
	{
		$this->options->methods(['GET']);

		$route = new Route($uri, $controller);
		$this->routeContainer = $route;

		return $this;
	}

	public function post($uri, $controller)
	{
		$this->options->methods(['POST']);

		$route = new Route($uri, $controller);
		$this->routeContainer = $route;

		return $this;
	}

	public function any($uri, $controller)
	{
		$this->options->methods(['POST', 'GET']);

		$route = new Route($uri, $controller);
		$this->routeContainer = $route;

		return $this;
	}

	public function group($callback)
	{
		$this->routeContainer = $callback;
	}

	public function release()
	{
		$routes = [];

		if ($this->routeContainer instanceof Route)
		{
			$route = $this->routeContainer;
			$route->setOptions($this->options);

			$routes[] = $route;
		}
		elseif ($this->routeContainer instanceof \Closure)
		{
			$subConfigurator = clone $this->configurator;
			$subConfigurator->mergeOptionsWith($this->options);

			// call
			$callback = $this->routeContainer;
			$callback($subConfigurator);
		}

		return $routes;
	}
}