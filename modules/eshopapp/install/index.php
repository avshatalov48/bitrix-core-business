<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));


Class eshopapp extends CModule
{
	var $MODULE_ID = "eshopapp";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function eshopapp()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("SCOM_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SCOM_INSTALL_DESCRIPTION");
	}


	function InstallDB()
	{
		RegisterModule("eshopapp");
		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		UnRegisterModule("eshopapp");
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

	function InstallFiles($site_dir="/", $default_site_id=false)
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp/install/templates/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/", true, true);

		if (!$default_site_id)
			$default_site_id = CSite::GetDefSite();
		if ($default_site_id)
		{
			$arAppTempalate = Array(
				"SORT" => 1,
				"CONDITION" => "CSite::InDir('".$site_dir."eshop_app/')",
				"TEMPLATE" => "eshop_app"
			);

			$arFields = Array("TEMPLATE"=>Array());
			$dbTemplates = CSite::GetTemplateList($default_site_id);
			$eshopAppFound = false;
			while($template = $dbTemplates->Fetch())
			{
				if ($template["TEMPLATE"] == "eshop_app")
				{
					$eshopAppFound = true;
					$template = $arAppTempalate;
				}
				$arFields["TEMPLATE"][] = array(
					"TEMPLATE" => $template['TEMPLATE'],
					"SORT" => $template['SORT'],
					"CONDITION" => $template['CONDITION']
				);
			}
			if (!$eshopAppFound)
				$arFields["TEMPLATE"][] = $arAppTempalate;

			$obSite = new CSite;
			$arFields["LID"] = $default_site_id;
			$obSite->Update($default_site_id, $arFields);
		}

		return true;
	}

	function InstallPublic($iblock_type = false, $iblock_id = false, $site_dir="/")
	{
		global $APPLICATION;
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp/public/".LANGUAGE_ID."/eshop_app/", $_SERVER["DOCUMENT_ROOT"].$site_dir."eshop_app/", true, true);
		/*if (!intval($_REQUEST["eshopapp_iblock_type"]))
			$this->errors = GetMessage("APP_IBLOCK_TYPE_ERROR");
		if (!intval($_REQUEST["eshopapp_iblock_id"]))
			$this->errors = GetMessage("APP_IBLOCK_ID_ERROR");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}       */
		$curCatalogIblockId = ($iblock_id) ? $iblock_id : intval($_REQUEST["eshopapp_iblock_id"]);
		$curCatalogIblockType = ($iblock_type) ? $iblock_type : $_REQUEST["eshopapp_iblock_type"];

		require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/general/wizard_util.php");
		CWizardUtil::ReplaceMacrosRecursive($_SERVER["DOCUMENT_ROOT"].$site_dir."eshop_app/", Array("CATALOG_IBLOCK_ID" => $curCatalogIblockId));
		CWizardUtil::ReplaceMacrosRecursive($_SERVER["DOCUMENT_ROOT"].$site_dir."eshop_app/", Array("CATALOG_IBLOCK_TYPE" => $curCatalogIblockType));
		CWizardUtil::ReplaceMacrosRecursive($_SERVER["DOCUMENT_ROOT"].$site_dir."eshop_app/", Array("SITE_DIR" => $site_dir));
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx('/bitrix/templates/eshop_app/');
		return true;
	}
	function UnInstallPublic($site_dir="/")
	{
		DeleteDirFilesEx($site_dir.'eshop_app/');
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;   
		$step = IntVal($step);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("APP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp/install/step1.php");
		}
		elseif($step == 2)
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mobileapp") && !IsModuleInstalled("mobileapp"))
			{
				$mobapp = new mobileapp();
				$mobapp->InstallDB();
				$mobapp->InstallFiles();
			}
			
			$this->InstallFiles();
			$this->InstallDB(false);
			//$this->InstallEvents();
			$this->InstallPublic($_REQUEST["eshopapp_iblock_type"], intval($_REQUEST["eshopapp_iblock_id"]));

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("APP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp/install/step2.php");
			//return true;
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallPublic();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile(GetMessage("APP_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp/install/unstep1.php");
		//return true;
	}
}
?>