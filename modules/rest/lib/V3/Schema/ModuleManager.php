<?php

namespace Bitrix\Rest\V3\Schema;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\ModuleManager as MainModuleManager;
use Bitrix\Rest\V3\CacheManager;

final class ModuleManager
{
	private const CONFIGS_CACHE_KEY = 'rest.v3.ModuleManager.module.configs.cache.key';
	private const CONFIGURATION_KEY = 'rest';

	public function getConfigs(): array
	{
		$configs = CacheManager::get(self::CONFIGS_CACHE_KEY);
		if ($configs === null)
		{
			foreach (MainModuleManager::getInstalledModules() as $moduleId => $moduleData)
			{
				$config = Configuration::getInstance($moduleId)->get(self::CONFIGURATION_KEY);
				if ($config !== null)
				{
					$configs[$moduleId] = $config;
				}
			}

			CacheManager::set(self::CONFIGS_CACHE_KEY, $configs);
		}

		return $configs;
	}
}