<?php

namespace Bitrix\Main\UrlPreview;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Uri;

class Router
{
	const CACHE_ID = 'UrlPreviewRouteCache';
	const CACHE_TTL = 315360000;

	/** @var array
	 * Allowed keys: ID, MODULE, CLASS, BUILD_METHOD, CHECK_METHOD, PARAMETERS
	 */
	protected static $routeTable = array();

	/** @var \Bitrix\Main\Data\ManagedCache */
	protected static $managedCache;

	/** @var bool */
	protected static $initialized = false;

	/**
	 * Adds, or, if route already exists, changes route handling method.
	 * @param string $route Route URL template.
	 * Route parameters should be enclosed in hash symbols, like '/user/#userId#/'.
	 * @param string $handlerModule Route handler module.
	 * @param string $handlerClass Route handler class should implement methods:
	 * <ul>
	 * 		<li>buildPreview($params): string. Method must accept array of parameters and return rendered preview
	 * 		<li>checkUserReadAccess($params): boolean. Method must accept array of parameters. Method must return true if
	 * 			currently logged in user has read access to the entity; false otherwise.
	 * 		<li>getCacheTag(): string. Method must return cache tag for the entity.
	 * </ul>.
	 * @param array $handlerParameters Array of parameters, passed to the handler methods.
	 * Will be passed as the argument when calling handler's method for building preview or checking access.
	 * Array values may contain variables referencing route parameters.
	 * e.g. ['userId' => '$userId'].
	 * @return void
	 * @throws ArgumentException
	 */
	public static function setRouteHandler($route, $handlerModule, $handlerClass, array $handlerParameters)
	{
		static::init();

		if(!is_string($route) || $route == '')
			throw new ArgumentException('Route could not be empty', '$route');
		if(!is_string($handlerModule) || $handlerModule == '')
			throw new ArgumentException('Handler module could not be empty', '$handler');
		if(!is_string($handlerClass) || $handlerClass == '')
			throw new ArgumentException('Handler class could not be empty', '$handler');

		$newRoute = true;
		if(isset(static::$routeTable[$route]))
		{
			if (   $handlerModule === static::$routeTable[$route]['MODULE']
				&& $handlerClass === static::$routeTable[$route]['CLASS']
				&& $handlerParameters == static::$routeTable[$route]['PARAMETERS']
			)
			{
				return;
			}
			$newRoute = false;
		}

		$allowSlashes = ($handlerParameters['allowSlashes'] ?? 'N') === 'Y';
		static::$routeTable[$route]['ROUTE'] = $route;
		static::$routeTable[$route]['REGEXP'] = static::convertRouteToRegexp($route, $allowSlashes);
		static::$routeTable[$route]['MODULE'] = $handlerModule;
		static::$routeTable[$route]['CLASS'] = $handlerClass;
		static::$routeTable[$route]['PARAMETERS'] = $handlerParameters;

		static::persistRoute($route, $newRoute);
	}

	/**
	 * Returns handler for the url
	 *
	 * @param Uri $uri Absolute or relative URL.
	 * @return array|false Handler for this URL if found, false otherwise.
	 */
	public static function dispatch(Uri $uri)
	{
		static::init();

		$urlPath = $uri->getPath();
		//todo: replace cycle with compiled regexp for all routes
		foreach(static::$routeTable as $routeRecord)
		{
			if(preg_match($routeRecord['REGEXP'], $urlPath, $matches))
			{
				$result = $routeRecord;
				//replace parameters variables with values
				foreach($result['PARAMETERS'] as $parameterName => &$parameterValue)
				{
					if(mb_strpos($parameterValue, '$') === 0)
					{
						$variableName = mb_substr($parameterValue, 1);
						if(isset($matches[$variableName]))
						{
							$parameterValue = $matches[$variableName];
						}
					}
				}
				unset($parameterValue);

				$uriQuery = $uri->getQuery();
				if (mb_strlen($uriQuery) > 0)
				{
					$uriQueryParams = static::parseQueryParams($uriQuery);
					foreach ($result['PARAMETERS'] as $parameterName => &$parameterValue)
					{
						if (mb_strpos($parameterValue, '$') === 0)
						{
							$variableName = mb_substr($parameterValue, 1);
							if (isset($uriQueryParams[$variableName]))
							{
								$parameterValue = $uriQueryParams[$variableName];
							}
						}
					}
					unset($parameterValue);
				}

				return $result;
			}
		}

		return false;
	}

	protected static function parseQueryParams($uriQuery): array
	{
		$data = preg_replace_callback(
			'/(?:^|(?<=&))[^=[]+/',
			function($match)
			{
				return bin2hex(urldecode($match[0]));
			},
			$uriQuery
		);

		parse_str($data, $values);

		return array_combine(array_map('hex2bin', array_keys($values)), $values);
	}

	/**
	 * Initializes router and prepares routing table.
	 * @return void
	 */
	protected static function init()
	{
		if(static::$initialized)
			return;

		static::$managedCache = Application::getInstance()->getManagedCache();

		if(static::$managedCache->read(static::CACHE_TTL, static::CACHE_ID))
		{
			static::$routeTable = (array)static::$managedCache->get(static::CACHE_ID);
		}
		else
		{
			$queryResult = RouteTable::getList(array(
				'select' => array('*')
			));

			while($routeRecord = $queryResult->fetch())
			{
				$allowSlashes = ($routeRecord['PARAMETERS']['allowSlashes'] ?? 'N') === 'Y';
				$routeRecord['REGEXP'] = static::convertRouteToRegexp($routeRecord['ROUTE'], $allowSlashes);
				static::$routeTable[$routeRecord['ROUTE']] = $routeRecord;
			}

			uksort(static::$routeTable, function($a, $b)
			{
				$lengthOfA = mb_strlen($a);
				$lengthOfB = mb_strlen($b);
				if($lengthOfA > $lengthOfB)
					return -1;
				else if($lengthOfA == $lengthOfB)
					return 0;
				else
					return 1;
			});

			static::$managedCache->set(static::CACHE_ID, static::$routeTable);
		}

		static::$initialized = true;
	}

	/**
	 * Persists routing table record in database
	 *
	 * @param string $route Route URL template.
	 * @param bool $isNew True if handler record was not encountered in router cache.
	 * @return bool Returns true if route is successfully stored in the database table, and false otherwise.
	 */
	protected static function persistRoute($route, $isNew)
	{
		static::invalidateRouteCache();
		//Oracle does not support 'merge ... returning field into :field' clause, thus we can't merge clob fields into the table.
		$routeData = array(
			'ROUTE' => static::$routeTable[$route]['ROUTE'],
			'MODULE' => static::$routeTable[$route]['MODULE'],
			'CLASS' => static::$routeTable[$route]['CLASS'],
		);

		if($isNew)
		{
			$addResult = RouteTable::merge($routeData);
			if($addResult->isSuccess())
			{
				static::$routeTable[$route]['ID'] = $addResult->getId();
				RouteTable::update(
						static::$routeTable[$route]['ID'],
						array(
								'PARAMETERS' => static::$routeTable[$route]['PARAMETERS']
						)
				);
			}
			$result = $addResult->isSuccess();
		}
		else
		{
			$routeData['PARAMETERS'] = static::$routeTable[$route]['PARAMETERS'];
			$updateResult = RouteTable::update(static::$routeTable[$route]['ID'], $routeData);
			$result = $updateResult->isSuccess();
		}
		return $result;
	}

	/**
	 * Return regexp string for checking URL against route template.
	 * @param string $route Route URL template.
	 * @param bool $allowSlashes Allow slashes in regex search.
	 * @return string
	 */
	protected static function convertRouteToRegexp(string $route, bool $allowSlashes = false): string
	{
		$result = preg_replace(
			"/#(\w+)#/",
			$allowSlashes ? "(?'\\1'.*?)" : "(?'\\1'[^/]+)",
			$route
		);
		$result = str_replace('/', '\/', $result);
		$result = '/^'.$result.'$/';

		return $result;
	}

	/**
	 * Resets router cache
	 * @return void
	 */
	public static function invalidateRouteCache()
	{
		Application::getInstance()->getManagedCache()->clean(static::CACHE_ID);
	}
}
