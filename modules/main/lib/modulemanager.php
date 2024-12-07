<?php

namespace Bitrix\Main;

class ModuleManager
{
	protected static $installedModules = [];

	public static function getInstalledModules()
	{
		if (empty(static::$installedModules))
		{
			$rs =  ModuleTable::getList([
				'select' => ['ID'],
				'order' => ['ID' => 'ASC'],
				'cache' => ['ttl' => 86400],
			]);
			while ($ar = $rs->fetch())
			{
				static::$installedModules[$ar['ID']] = $ar;
			}
		}

		return static::$installedModules;
	}

	/**
	 * Returns all modules from disk
	 *
	 * @return array
	 */
	public static function getModulesFromDisk($withLocal = true)
	{
		$modules = [];

		$folders = [
			"/bitrix/modules"
		];

		if ($withLocal)
		{
			$folders[] = "/local/modules";
		}

		foreach ($folders as $folder)
		{
			$folderPath = $_SERVER["DOCUMENT_ROOT"] . $folder;
			$handle = null;

			if (is_dir($folderPath) && is_readable($folderPath))
			{
				$handle = opendir($folderPath);
			}

			if (!empty($handle))
			{
				while (false !== ($dir = readdir($handle)))
				{
					if (
						!isset($modules[$dir])
						&& is_dir($folderPath . "/" . $dir)
						&& !in_array($dir, ['.', '..'], true)
					)
					{
						if ($info = \CModule::CreateModuleObject($dir))
						{
							$modules[$dir]["id"] = $info->MODULE_ID;
							$modules[$dir]["name"] = $info->MODULE_NAME;
							$modules[$dir]["description"] = $info->MODULE_DESCRIPTION;
							$modules[$dir]["version"] = $info->MODULE_VERSION;
							$modules[$dir]["versionDate"] = $info->MODULE_VERSION_DATE;
							$modules[$dir]["sort"] = $info->MODULE_SORT;
							$modules[$dir]["isInstalled"] = $info->IsInstalled();
						}
					}
				}

				closedir($handle);
			}
		}

		return $modules;
	}

	public static function getVersion($moduleName)
	{
		if (!static::isValidModule($moduleName))
		{
			return false;
		}

		if (!static::isModuleInstalled($moduleName))
		{
			return false;
		}

		if ($moduleName == 'main')
		{
			if (!defined("SM_VERSION"))
			{
				include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/version.php");
			}
			$version = SM_VERSION;
		}
		else
		{
			$modulePath = getLocalPath("modules/" . $moduleName . "/install/version.php");
			if ($modulePath === false)
			{
				return false;
			}

			$arModuleVersion = [];
			include($_SERVER["DOCUMENT_ROOT"] . $modulePath);
			$version = (array_key_exists("VERSION", $arModuleVersion) ? $arModuleVersion["VERSION"] : false);
		}

		return $version;
	}

	public static function isModuleInstalled($moduleName)
	{
		if (empty(static::$installedModules))
		{
			static::getInstalledModules();
		}

		return isset(static::$installedModules[$moduleName]);
	}

	public static function delete($moduleName)
	{
		ModuleTable::delete($moduleName);

		$con = Application::getConnection();
		$module = $con->getSqlHelper()->forSql($moduleName);
		$con->queryExecute("UPDATE b_agent SET ACTIVE = 'N' WHERE MODULE_ID = '" . $module . "' AND ACTIVE = 'Y'");

		static::clearCache($moduleName);
	}

	public static function add($moduleName)
	{
		ModuleTable::add(['ID' => $moduleName]);

		$con = Application::getConnection();
		$module = $con->getSqlHelper()->forSql($moduleName);
		$con->queryExecute("UPDATE b_agent SET ACTIVE = 'Y' WHERE MODULE_ID = '" . $module . "' AND ACTIVE = 'N'");

		static::clearCache($moduleName);
	}

	public static function registerModule($moduleName)
	{
		static::add($moduleName);

		$event = new Event("main", "OnAfterRegisterModule", [$moduleName]);
		$event->send();
	}

	public static function unRegisterModule($moduleName)
	{
		$con = Application::getInstance()->getConnection();

		$con->queryExecute("DELETE FROM b_agent WHERE MODULE_ID='" . $con->getSqlHelper()->forSql($moduleName) . "'");
		\CMain::DelGroupRight($moduleName);

		static::delete($moduleName);

		$event = new Event("main", "OnAfterUnRegisterModule", [$moduleName]);
		$event->send();
	}

	protected static function clearCache($moduleName)
	{
		static::$installedModules = [];

		Loader::clearModuleCache($moduleName);
		EventManager::getInstance()->clearLoadedHandlers();
	}

	public static function isValidModule(string $moduleName): bool
	{
		$originalModuleName = $moduleName;
		$moduleName = preg_replace("/[^a-zA-Z0-9_.]+/i", "", trim($moduleName));

		return $moduleName === $originalModuleName;
	}
}
