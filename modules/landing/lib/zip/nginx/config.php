<?php
namespace Bitrix\Landing\Zip\Nginx;

use \Bitrix\Landing\Manager;
use \Bitrix\Main\ModuleManager;

class Config
{
	/**
	 * Enable or not main option.
	 * @return bool
	 */
	public static function serviceEnabled()
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			return true;
		}
		else
		{
			return Manager::getOption('enable_mod_zip', 'N') == 'Y';
		}
	}
}