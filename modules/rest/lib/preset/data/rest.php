<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\ModuleManager;
use Bitrix\Rest\Engine\RestManager;
use CRestProvider;
use Bitrix\Rest\Engine\ScopeManager;

/**
 * Class Rest
 * @package Bitrix\Rest\Preset\Data
 */
class Rest
{
	public const SCOPE = 'SCOPE';
	public const METHOD = 'METHOD';
	public const PLACEMENT = 'PLACEMENT';
	public const EVENT = 'EVENT';
	private const CACHE_TIME = 86400;
	private const CACHE_DIR = '/rest/integration/data/rest/';

	/**
	 * @return array
	 */
	public static function getAllBasicDescription() : array
	{
		$result = [
			static::SCOPE => [],
			static::METHOD => [],
			static::PLACEMENT => [],
			static::EVENT => [],
		];
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'allRestDescription' . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$provider = new CRestProvider();
			$allScope = $provider->getDescription();

			foreach ($allScope as $scope => $list)
			{
				foreach ($list as $method => $data)
				{
					if ($method === '_events')
					{
						if (!isset($result[static::EVENT][$scope]))
						{
							$result[static::EVENT][$scope] = [];
						}
						$result[static::EVENT][$scope] = array_merge($result[static::EVENT][$scope], array_keys($data));
					}
					elseif ($method === '_placements')
					{
						if (!isset($result[static::PLACEMENT][$scope]))
						{
							$result[static::PLACEMENT][$scope] = [];
						}
						$result[static::PLACEMENT][$scope] = array_merge($result[static::PLACEMENT][$scope], array_keys($data));
					}
					else
					{
						$result[static::METHOD][$scope][] = $method;
					}
				}
			}
			$result[static::SCOPE] = array_keys($allScope);
			$installedModuleList = ModuleManager::getInstalledModules();
			foreach ($installedModuleList as $moduleId => $moduleDescription)
			{
				if (!isset($description[$moduleId]))
				{
					$controllersConfig = Configuration::getInstance($moduleId);

					if (!empty($controllersConfig['controllers']['restIntegration']['enabled']))
					{
						$result[static::SCOPE][] = RestManager::getModuleScopeAlias($moduleId);
					}
				}
			}
			$result[static::SCOPE] = array_values(array_unique($result[static::SCOPE]));

			$cache->endDataCache($result);
		}

		return $result;
	}

	/**
	 * @param $scopeList
	 *
	 * @return array
	 */
	public static function getAccessPlacement($scopeList) : array
	{
		$result = [];
		if (!is_array($scopeList))
		{
			return $result;
		}
		$placementList = static::getAllBasicDescription()[static::PLACEMENT];

		$placementList = array_intersect_key($placementList, array_flip($scopeList));
		foreach ($placementList as $values)
		{
			$result = array_merge($result, $values);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function getBaseMethod() : array
	{
		$result = [];
		$data = static::getAllBasicDescription();
		if (!empty($data[static::METHOD]))
		{
			$result = $data[static::METHOD];
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function getScope() : array
	{
		return ScopeManager::getInstance()->listScope();
	}
}