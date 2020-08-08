<?
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
if (class_exists("bitrixcloud"))
	return;

class bitrixcloud extends CModule
{
	var $MODULE_ID = "bitrixcloud";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";
	var $errors = false;

	function bitrixcloud()
	{
		$arModuleVersion = array();
		include(__DIR__.'/version.php');
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("BCL_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("BCL_MODULE_DESCRIPTION");
	}

	function GetModuleTasks()
	{
		return array(
			'bitrixcloud_deny' => array(
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => array(
				)
			),
			'bitrixcloud_control' => array(
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'bitrixcloud_monitoring',
					'bitrixcloud_backup',
					'bitrixcloud_cdn',
				)
			),
		);
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		// Database tables creation
		if (!$DB->Query("SELECT 'x' FROM b_bitrixcloud_option WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/db/".mb_strtolower($DB->type)."/install.sql");
		}
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			$this->InstallTasks();
			RegisterModule("bitrixcloud");
			RegisterModuleDependences("main", "OnAdminInformerInsertItems", "bitrixcloud", "CBitrixCloudBackup", "OnAdminInformerInsertItems");
			RegisterModuleDependences("mobileapp", "OnBeforeAdminMobileMenuBuild", "bitrixcloud", "CBitrixCloudMobile", "OnBeforeAdminMobileMenuBuild");

			CModule::IncludeModule("bitrixcloud");
		}
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		UnRegisterModuleDependences("main", "OnEndBufferContent", "bitrixcloud", "CBitrixCloudCDN", "OnEndBufferContent");
		UnRegisterModuleDependences("main", "OnAdminInformerInsertItems", "bitrixcloud", "CBitrixCloudBackup", "OnAdminInformerInsertItems");
		UnRegisterModuleDependences("mobileapp", "OnBeforeAdminMobileMenuBuild", "bitrixcloud", "CBitrixCloudMobile", "OnBeforeAdminMobileMenuBuild");
		if (!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/db/".mb_strtolower($DB->type)."/uninstall.sql");
		}
		UnRegisterModule("bitrixcloud");
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
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/gadgets", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFilesEx("/bitrix/js/bitrixcloud/");
		}
		return true;
	}

	function DoInstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/step1.php");
			}
			elseif ($step == 2)
			{
				if ($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles();
				}
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/step2.php");
			}
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
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/unstep1.php");
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
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bitrixcloud/install/unstep2.php");
			}
		}
	}

	public function migrateToBox()
	{
		COption::RemoveOption($this->MODULE_ID);
	}
}
