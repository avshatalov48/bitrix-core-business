<?php

namespace Bitrix\Sale\CrmSiteMaster\Tools;

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
 * @package Bitrix\Sale\CrmSiteMaster\Tools
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
	 * Check availability of required modules
	 *
	 * @param array $notExistModule
	 * @return array
	 */
	public function checkAvailableModules($notExistModule)
	{
		$result = [
			"MODULES" => [],
			"ERROR" => []
		];

		$moduleList = $this->getUpdatesList();
		$result["MODULES"] = array_diff(array_keys($notExistModule), $moduleList["MODULES"]);
		$result["ERROR"] = $moduleList["ERROR"];

		return $result;
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
			if ($version !== false
				&& !empty($moduleData["version"])
				&& (version_compare($version, $moduleData["version"]) === -1))
			{
					$result["MIN_VERSION"][$moduleName] = [
						"NAME" => $moduleData["name"],
						"REQUIRED_VERSION" => $moduleData["version"],
						"CURRENT_VERSION" => $version,
					];
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
	 * Get modules for update
	 *
	 * @return array
	 */
	private function getUpdatesList()
	{
		$result = [
			"MODULES" => [],
			"ERROR" => []
		];

		try
		{
			$stableVersionsOnly = Option::get("main", "stable_versions_only", "N");
		}
		catch (\Exception $ex)
		{
			$stableVersionsOnly = "N";
		}

		$errorMessage = "";
		if ($arUpdateList = \CUpdateClient::GetUpdatesList($errorMessage, LANGUAGE_ID, $stableVersionsOnly))
		{
			if (isset($arUpdateList["ERROR"]))
			{
				/** @noinspection PhpVariableNamingConventionInspection */
				for ($i = 0, $cnt = count($arUpdateList["ERROR"]); $i < $cnt; $i++)
				{
					if (
						($arUpdateList["ERROR"][$i]["@"]["TYPE"] != "RESERVED_KEY")
						&& ($arUpdateList["ERROR"][$i]["@"]["TYPE"] != "NEW_UPDATE_SYSTEM")
					)
					{
						$errorMessage .= "[".$arUpdateList["ERROR"][$i]["@"]["TYPE"]."] ".$arUpdateList["ERROR"][$i]["#"];
					}
					elseif ($arUpdateList["ERROR"][$i]["@"]["TYPE"] == "NEW_UPDATE_SYSTEM")
					{
						$errorMessage .= Loc::getMessage("SALE_CSM_WIZARD_MODULECHECKER_UPDATE_SYSTEM_HINT");
					}
					else
					{
						$errorMessage .= Loc::getMessage("SALE_CSM_WIZARD_MODULECHECKER_RESERVED_KEY_HINT");
					}
				}
			}
			elseif (isset($arUpdateList["MODULES"][0]["#"]["MODULE"]))
			{
				/** @noinspection PhpVariableNamingConventionInspection */
				for ($i = 0, $cnt = count($arUpdateList["MODULES"][0]["#"]["MODULE"]); $i < $cnt; $i++)
				{
					$arModuleTmp = $arUpdateList["MODULES"][0]["#"]["MODULE"][$i];
					$moduleId = $arModuleTmp["@"]["ID"];
					$result["MODULES"][] = $moduleId;
				}
			}
		}

		if ($errorMessage)
		{
			$result["ERROR"] = $errorMessage;
		}

		return $result;
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