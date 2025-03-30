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
	public static function getModulesFromDisk($withLocal = true, $withPartners = true, $withKernel = true)
	{
		$modules = [];

		$folders = [
			"/bitrix/modules",
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
						&& is_dir($folderPath . '/' . $dir)
						&& !in_array($dir, ['.', '..'], true)
						&& ($withPartners || !str_contains($dir, '.'))
						&& ($withKernel || str_contains($dir, '.'))
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
							$modules[$dir]["partner"] = $info->PARTNER_NAME;
							$modules[$dir]["partnerUri"] = $info->PARTNER_URI;
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
		\CAgent::RemoveModuleAgents($moduleName);
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

	public static function decreaseVersion(string $moduleId, int $count, ?string $fromVersion = null): ?string
	{
		if (!self::isValidModule($moduleId))
		{
			return null;
		}

		if ($moduleId === 'main')
		{
			$versionFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/version.php';
		}
		else
		{
			$versionFile = $_SERVER['DOCUMENT_ROOT'] . getLocalPath('modules/' . $moduleId . '/install/version.php');
		}

		$count = $count > 0 ? $count : 1;

		if (file_exists($versionFile) && is_file($versionFile))
		{
			$fileContent = file_get_contents($versionFile);

			if (preg_match("/(\\d+)\\.(\\d+)\\.(\\d+)/", $fileContent, $match))
			{
				$oldVersion = $match[0];

				if ($fromVersion !== null)
				{
					if (!preg_match("/(\\d+)\\.(\\d+)\\.(\\d+)/", $fromVersion, $match))
					{
						return null;
					}
				}

				if ($match[3] - $count >= 0)
				{
					$match[3] -= $count;
				}
				else
				{
					$match[3] = 10000 - $count + (int)$match[3];
					if ($match[2] == 0)
					{
						$match[2] = 9999;
						$match[1] -= 1;
					}
					else
					{
						$match[2] -= 1;
					}
				}

				if ($match[1] > 0 && $match[2] >= 0 && $match[3] >= 0)
				{
					$fileContent = str_replace($oldVersion, $match[1] . '.' . $match[2] . '.' . $match[3], $fileContent);
					file_put_contents($versionFile, $fileContent);

					Application::resetAccelerator($versionFile);
				}

				return $match[1] . '.' . $match[2] . '.' . $match[3];
			}
		}

		return null;
	}
}
