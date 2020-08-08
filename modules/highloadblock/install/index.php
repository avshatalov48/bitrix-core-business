<?php

IncludeModuleLangFile(__FILE__);
if (class_exists("highloadblock"))
	return;

class highloadblock extends CModule
{
	var $MODULE_ID = "highloadblock";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	function highloadblock()
	{
		$arModuleVersion = array();
		include(__DIR__.'/version.php');
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("HLBLOCK_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("HLBLOCK_MODULE_DESCRIPTION");
	}

	public function GetModuleTasks()
	{
		return array(
			'hblock_denied' => array(
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => array(),
			),
			'hblock_read' => array(
				'LETTER' => 'R',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'hl_element_read'
				),
			),
			'hblock_write' => array(
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'hl_element_write', 'hl_element_delete'
				),
			),
		);
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		// Database tables creation
		if (!$DB->Query("SELECT 'x' FROM b_hlblock_entity WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/db/".mb_strtolower($DB->type)."/install.sql");
		}
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			$this->InstallTasks();
			RegisterModule("highloadblock");
			CModule::IncludeModule("highloadblock");

			RegisterModuleDependences("main", "OnBeforeUserTypeAdd", "highloadblock", '\Bitrix\Highloadblock\HighloadBlockTable', "OnBeforeUserTypeAdd");
			RegisterModuleDependences("main", "OnAfterUserTypeAdd", "highloadblock", '\Bitrix\Highloadblock\HighloadBlockTable', "onAfterUserTypeAdd");
			RegisterModuleDependences("main", "OnBeforeUserTypeDelete", "highloadblock", '\Bitrix\Highloadblock\HighloadBlockTable', "OnBeforeUserTypeDelete");
			RegisterModuleDependences('main', 'OnUserTypeBuildList', 'highloadblock', 'CUserTypeHlblock', 'GetUserTypeDescription');
			RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'highloadblock', 'CIBlockPropertyDirectory', 'GetUserTypeDescription');
		}
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		if (!array_key_exists("save_tables", $arParams) || $arParams["save_tables"] != "Y")
		{
			// remove user data
			CModule::IncludeModule("highloadblock");

			$result = \Bitrix\Highloadblock\HighloadBlockTable::getList();
			while ($hldata = $result->fetch())
			{
				\Bitrix\Highloadblock\HighloadBlockTable::delete($hldata['ID']);
			}

			$this->UnInstallTasks();

			// remove hl system data
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/db/".mb_strtolower($DB->type)."/uninstall.sql");
		}

		UnRegisterModule("highloadblock");

		UnRegisterModuleDependences("main", "OnBeforeUserTypeAdd", "highloadblock", '\Bitrix\Highloadblock\HighloadBlockTable', "OnBeforeUserTypeAdd");
		UnRegisterModuleDependences("main", "OnAfterUserTypeAdd", "highloadblock", '\Bitrix\Highloadblock\HighloadBlockTable', "onAfterUserTypeAdd");
		UnRegisterModuleDependences("main", "OnBeforeUserTypeDelete", "highloadblock", '\Bitrix\Highloadblock\HighloadBlockTable', "OnBeforeUserTypeDelete");
		UnRegisterModuleDependences('main', 'OnUserTypeBuildList', 'highloadblock', 'CUserTypeHlblock', 'GetUserTypeDescription');
		UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'highloadblock', 'CIBlockPropertyDirectory', 'GetUserTypeDescription');

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/highloadblock/");
		return true;
	}

	function DoInstall()
	{
		global $USER, $APPLICATION;

		if ($USER->IsAdmin())
		{
			if ($this->InstallDB())
			{
				$this->InstallEvents();
				$this->InstallFiles();
			}
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("HLBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/step.php");
		}
	}

	function DoUninstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("HLBLOCK_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/unstep1.php");
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB(array(
					"save_tables" => $_REQUEST["save_tables"],
				));
				//message types and templates
				if ($_REQUEST["save_templates"] != "Y")
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("HLBLOCK_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/highloadblock/install/unstep2.php");
			}
		}
	}
}
