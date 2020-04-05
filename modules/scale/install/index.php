<?

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (class_exists("scale"))
	return;

class scale extends CModule
{
	var $MODULE_ID = "scale";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	var $errors = false;

	function scale()
	{
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("SCALE_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("SCALE_MODULE_DESCRIPTION");
	}

	function InstallDB()
	{
		\Bitrix\Main\ModuleManager::registerModule("scale");
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "scale", "\\Bitrix\\Scale\\Logger", 'onEventLogGetAuditTypes');
		return true;
	}

	function UnInstallDB()
	{
			UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "scale", "\\Bitrix\\Scale\\Logger", 'onEventLogGetAuditTypes');
		$result = \Bitrix\Main\ModuleManager::unRegisterModule("scale");
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
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/scale/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/scale/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		}

		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/scale/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFilesEx("/bitrix/js/scale/");
		}

		return true;
	}

	function DoInstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = IntVal($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(Loc::getMessage("SCALE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/scale/install/step1.php");
			}
			elseif ($step == 2)
			{
				if (!IsModuleInstalled("scale"))
				{
					$this->InstallDB();
					$this->InstallEvents();
					$this->InstallFiles();
					$GLOBALS["errors"] = $this->errors;
					$APPLICATION->IncludeAdminFile(Loc::getMessage("SCALE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/scale/install/step2.php");
				}
			}
		}
	}

	function DoUninstall()
	{
		global $USER, $APPLICATION, $step;

		if ($USER->IsAdmin())
		{
			$step = IntVal($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(Loc::getMessage("SCALE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/scale/install/unstep1.php");
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB();
				$this->UnInstallEvents();
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(Loc::getMessage("SCALE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/scale/install/unstep2.php");
			}
		}
	}
}
?>
