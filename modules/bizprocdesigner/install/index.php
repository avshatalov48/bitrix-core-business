<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class bizprocdesigner extends CModule
{
	var $MODULE_ID = "bizprocdesigner";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function bizprocdesigner()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("BIZPROCDESIGNER_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("BIZPROCDESIGNER_INSTALL_DESCRIPTION");
	}


	function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION;

		$arCurPhpVer = Explode(".", PhpVersion());
		if (IntVal($arCurPhpVer[0]) < 5)
			return true;

		RegisterModule("bizprocdesigner");

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $DBType, $APPLICATION;

		UnRegisterModule("bizprocdesigner");

		return true;
	}

	function InstallEvents()
	{
		$arCurPhpVer = Explode(".", PhpVersion());
		if (IntVal($arCurPhpVer[0]) < 5)
			return true;

		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}
		return true;
	}

	function InstallPublic()
	{
		$arCurPhpVer = Explode(".", PhpVersion());
		if (IntVal($arCurPhpVer[0]) < 5)
			return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/components/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix");
		}

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$curPhpVer = PhpVersion();
		$arCurPhpVer = Explode(".", $curPhpVer);
		if (IntVal($arCurPhpVer[0]) < 5)
		{
			$this->errors = array(GetMessage("BIZPROC_PHP_L439", array("#VERS#" => $curPhpVer)));
		}
		elseif (!IsModuleInstalled("bizproc"))
		{
			$this->errors = array(GetMessage("BIZPROC_ERROR_BPM"));
		}
		elseif (!CBXFeatures::IsFeatureEditable("BizProc"))
		{
			$this->errors = array(GetMessage("BIZPROC_ERROR_EDITABLE"));
		}
		else
		{
			$this->InstallDB(false);
			$this->InstallFiles();
			CBXFeatures::SetFeatureEnabled("BizProc", true);
		}

		$GLOBALS["errors"] = $this->errors;
		$APPLICATION->IncludeAdminFile(GetMessage("BIZPROC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/step2.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("BIZPROC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallFiles();
			$this->UnInstallDB(false);
			CBXFeatures::SetFeatureEnabled("BizProc", false);
			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("BIZPROC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/install/unstep2.php");
		}
	}
}
?>