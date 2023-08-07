<?php
namespace Bitrix\Main;

class ModuleManager
{
	protected const CACHE_ID = 'b_module';

	protected static $installedModules = [];

	public static function getInstalledModules()
	{
		if (empty(self::$installedModules))
		{
			$cacheManager = Application::getInstance()->getManagedCache();
			if ($cacheManager->read(3600, self::CACHE_ID))
			{
				self::$installedModules = $cacheManager->get(self::CACHE_ID);
			}

			if (empty(self::$installedModules))
			{
				self::$installedModules = [];
				$con = Application::getConnection();
				$rs = $con->query("SELECT ID FROM b_module ORDER BY ID");
				while ($ar = $rs->fetch())
				{
					self::$installedModules[$ar['ID']] = $ar;
				}
				$cacheManager->set(self::CACHE_ID, self::$installedModules);
			}
		}

		return self::$installedModules;
	}

	public static function getVersion($moduleName)
	{
		$moduleName = preg_replace("/[^a-zA-Z0-9_.]+/i", "", trim($moduleName));
		if ($moduleName == '')
			return false;

		if (!self::isModuleInstalled($moduleName))
			return false;

		if ($moduleName == 'main')
		{
			if (!defined("SM_VERSION"))
			{
				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/version.php");
			}
			$version = SM_VERSION;
		}
		else
		{
			$modulePath = getLocalPath("modules/".$moduleName."/install/version.php");
			if ($modulePath === false)
				return false;

			$arModuleVersion = [];
			include($_SERVER["DOCUMENT_ROOT"].$modulePath);
			$version = (array_key_exists("VERSION", $arModuleVersion)? $arModuleVersion["VERSION"] : false);
		}

		return $version;
	}

	public static function isModuleInstalled($moduleName)
	{
		$arInstalledModules = self::getInstalledModules();
		return isset($arInstalledModules[$moduleName]);
	}

	public static function delete($moduleName)
	{
		$con = Application::getConnection();
		$module = $con->getSqlHelper()->forSql($moduleName);

		$con->queryExecute("DELETE FROM b_module WHERE ID = '" . $module . "'");
		$con->queryExecute("UPDATE b_agent SET ACTIVE = 'N' WHERE MODULE_ID = '" . $module . "' AND ACTIVE = 'Y'");

		static::clearCache($moduleName);
	}

	public static function add($moduleName)
	{
		$con = Application::getConnection();
		$module = $con->getSqlHelper()->forSql($moduleName);

		$con->queryExecute("INSERT INTO b_module(ID) VALUES('" . $module . "')");
		$con->queryExecute("UPDATE b_agent SET ACTIVE = 'Y' WHERE MODULE_ID = '" . $module . "' AND ACTIVE = 'N'");

		static::clearCache($moduleName);
	}

	public static function registerModule($moduleName)
	{
		static::add($moduleName);

		$event = new Event("main", "OnAfterRegisterModule", array($moduleName));
		$event->send();
	}

	public static function unRegisterModule($moduleName)
	{
		$con = Application::getInstance()->getConnection();

		$con->queryExecute("DELETE FROM b_agent WHERE MODULE_ID='".$con->getSqlHelper()->forSql($moduleName)."'");
		\CMain::DelGroupRight($moduleName);

		static::delete($moduleName);

		$event = new Event("main", "OnAfterUnRegisterModule", array($moduleName));
		$event->send();
	}

	protected static function clearCache($moduleName)
	{
		self::$installedModules = [];
		Application::getInstance()->getManagedCache()->clean(self::CACHE_ID);

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
