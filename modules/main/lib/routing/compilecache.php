<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Main\Routing;

use Bitrix\Main\Data\Cache;

/**
 * @package    bitrix
 * @subpackage main
 */
class CompileCache
{
	/**
	 * @param string[] $files
	 * @param Router   $router
	 */
	public static function handle($files, $router)
	{
		$cacheKeyElements = [];

		foreach ($files as $file)
		{
			$cacheKeyElements[] = $file.':'.filemtime($file);
		}

		$cacheKey = 'compiled_'.md5(join(',', $cacheKeyElements));
		$cacheDir = 'routing';

		$cache = Cache::createInstance();

		if ($cache->initCache(3600*24*365, $cacheKey, $cacheDir))
		{
			$cacheData = $cache->getVars();
		}

		if (empty($cacheData))
		{
			// compile all the routes
			$cacheData = [];

			foreach ($router->getRoutes() as $k => $route)
			{
				$cacheData[$k] = $route->getCompileCache();
			}

			if ($cache->startDataCache())
			{
				$cache->endDataCache($cacheData);
			}
		}
		else
		{
			// compile all the routes from cache
			foreach ($router->getRoutes() as $k => $route)
			{
				$route->compileFromCache($cacheData[$k]);
			}
		}
	}
}
