<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Main\Routing;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Routing\Exceptions\ParameterNotFoundException;

/**
 * @package    bitrix
 * @subpackage main
 */
class Router
{
	/** @var Route[] */
	protected $routes = [];

	/** @var Route[] */
	protected $routesByName = [];

	/** @var RoutingConfiguration[] */
	protected $configurations = [];

	public function registerConfiguration($configuration)
	{
		$this->configurations[] = $configuration;
	}

	public function releaseRoutes()
	{
		// go recursively through routes tree
		$i = -1;
		while (isset($this->configurations[++$i]))
		{
			$this->routes = array_merge($this->routes, $this->configurations[$i]->release());
		}

		// reindex
		$this->reindexRoutes();

		// don't need them anymore
		$this->configurations = [];
	}

	protected function reindexRoutes()
	{
		$this->routesByName = [];

		foreach ($this->routes as $route)
		{
			if ($route->getOptions() && $route->getOptions()->hasName())
			{
				$this->routesByName[$route->getOptions()->getFullName()] = $route;
			}
		}
	}

	/**
	 * @param HttpRequest $request
	 *
	 * @return Route|void
	 */
	public function match($request)
	{
		$path = urldecode($this->getUriPath($request));

		foreach ($this->routes as $route)
		{
			if ($matchResult = $route->match($path))
			{
				// check method
				if (!empty($route->getOptions()->getMethods())
					&& !in_array($request->getRequestMethod(), $route->getOptions()->getMethods(), true))
				{
					continue;
				}

				if (is_array($matchResult))
				{
					$route->getParametersValues()->setValues($matchResult);
				}

				return $route;
			}
		}
	}

	/**
	 * @param HttpRequest $request
	 *
	 * @return string
	 */
	protected function getUriPath($request)
	{
		// cut GET parameters
		$path = str_replace(
			'?'.$request->getServer()->get('QUERY_STRING'),
			'',
			$request->getRequestUri()
		);

		// cut scheme and domain
		$scheme = $request->isHttps() ? 'https://' : 'http://';
		$schemeLen = strlen($scheme);

		if (substr($path, 0, $schemeLen) === $scheme)
		{
			$pathSlashPos = strpos(substr($path, $schemeLen), '/') + $schemeLen;
			$path = substr($path, $pathSlashPos);
		}

		return $path;
	}

	public function url($url, $parameters = [])
	{
		// scheme, domain?
		$finalUrl = $url;

		if (!empty($parameters))
		{
			$finalUrl .= '?'.http_build_query($parameters);
		}

		return $finalUrl;
	}

	public function route($name, $parameters = [])
	{
		if (!empty($this->routesByName[$name]))
		{
			// route should be compiled
			$route = $this->routesByName[$name];
			$route->compile();

			$uri = $route->getUri();

			if (!empty($routeParameters = $route->getParameters()))
			{
				foreach ($routeParameters as $parameterName => $pattern)
				{
					if (array_key_exists($parameterName, $parameters))
					{
						// get from user
						$value = $parameters[$parameterName];

						// remove from user list
						unset($parameters[$parameterName]);
					}
					elseif ($route->getOptions() && $route->getOptions()->hasDefault($parameterName))
					{
						$value = $route->getOptions()->getDefault($parameterName);
					}
					else
					{
						throw new ParameterNotFoundException;
					}

					// check with pattern?

					$uri = str_replace("{{$parameterName}}", urlencode($value), $uri);
				}
			}

			// additional parameters as query string
			if (!empty($parameters))
			{
				$uri .= '?'.http_build_query($parameters);
			}

			return $uri;
		}
	}

	/**
	 * @return Route[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}
}