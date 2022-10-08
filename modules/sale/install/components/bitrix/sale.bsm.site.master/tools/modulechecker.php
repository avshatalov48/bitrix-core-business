<?php

namespace Bitrix\Sale\BsmSiteMaster\Tools;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ModuleChecker
 * @package Bitrix\Sale\BsmSiteMaster\Tools
 */
class ModuleChecker
{
	const IS_MODULE_INSTALL = "~IS_MODULE_INSTALL";

	private $modulesRequired = [];

	/**
	 * ModuleChecker constructor.
	 * @param array $modulesRequired
	 */
	public function setRequiredModules(array $modulesRequired)
	{
		$this->modulesRequired = $modulesRequired;
	}

	/**
	 * @return array
	 */
	public function getRequiredModules()
	{
		return $this->modulesRequired;
	}


	/**
	 * Check existence of required modules
	 *
	 * @return array
	 */
	public function getNotExistModules()
	{
		$notExistModule = [];

		foreach ($this->modulesRequired as $moduleName => $moduleData)
		{
			if (!Main\IO\Directory::isDirectoryExists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleName))
			{
				$notExistModule[$moduleName] = $moduleData;
			}
		}

		return $notExistModule;
	}

	/**
	 * Checks required modules
	 */
	public function checkInstalledModules()
	{
		$result = [
			"NOT_INSTALL" => [],
			"MIN_VERSION" => []
		];

		foreach ($this->modulesRequired as $moduleName => $moduleData)
		{
			if (Main\ModuleManager::getVersion($moduleName) === false)
			{
				$result["NOT_INSTALL"][$moduleName] = $moduleData;
			}

			$version = $this->getModuleVersion($moduleName);
			if ($version !== false)
			{
				if ($version && $moduleData["version"]
					&& (version_compare($version, $moduleData["version"]) === -1)
				)
				{
					$result["MIN_VERSION"][$moduleName] = [
						"NAME" => $moduleData["name"],
						"REQUIRED_VERSION" => $moduleData["version"],
						"CURRENT_VERSION" => $version,
					];
				}
			}
		}

		return $result;
	}

	/**
	 * @param $moduleName
	 * @return bool|mixed|string
	 */
	private function getModuleVersion($moduleName)
	{
		$moduleName = preg_replace("/[^a-zA-Z0-9_.]+/i", "", trim($moduleName));
		if ($moduleName == '')
			return false;

		if ($moduleName === "main")
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

			$arModuleVersion = array();
			include($_SERVER["DOCUMENT_ROOT"].$modulePath);
			$version = (array_key_exists("VERSION", $arModuleVersion)? $arModuleVersion["VERSION"] : false);
		}

		return $version;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setInstallStatus()
	{
		Option::set("sale", self::IS_MODULE_INSTALL, "Y");
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function isModuleInstall()
	{
		return Option::get("sale", self::IS_MODULE_INSTALL, "N") === "Y";
	}

	/**
	 * @throws Main\ArgumentNullException
	 */
	public function deleteInstallStatus()
	{
		Option::delete("sale", ["name" => self::IS_MODULE_INSTALL]);
	}
}