<?
IncludeModuleLangFile(__FILE__);

if(class_exists("cluster")) return;
Class cluster extends CModule
{
	var $MODULE_ID = "cluster";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function cluster()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("CLU_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("CLU_MODULE_DESCRIPTION");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_cluster_dbnode WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/db/".strtolower($DB->type)."/install.sql");

			if($DB->type == "MSSQL")
				$DB->Query("SET IDENTITY_INSERT B_CLUSTER_GROUP ON");
			$DB->Add("b_cluster_group", array(
				"ID" => 1,
				"NAME" => GetMessage("CLU_GROUP_NO_ONE"),
			));
			if($DB->type == "MSSQL")
				$DB->Query("SET IDENTITY_INSERT B_CLUSTER_GROUP OFF");

			if($DB->type == "MSSQL")
				$DB->Query("SET IDENTITY_INSERT B_CLUSTER_DBNODE ON");
			$DB->Add("b_cluster_dbnode", array(
				"ID" => 1,
				"GROUP_ID" => 1,
				"ACTIVE" => "Y",
				"ROLE_ID" => "MAIN",
				"NAME" => GetMessage("CLU_MAIN_DATABASE"),
				"DESCRIPTION" => false,

				"DB_HOST" => false,
				"DB_NAME" => false,
				"DB_LOGIN" => false,
				"DB_PASSWORD" => false,

				"MASTER_ID" => false,
				"SERVER_ID" => false,
				"STATUS" => "ONLINE",
			));
			if($DB->type == "MSSQL")
				$DB->Query("SET IDENTITY_INSERT B_CLUSTER_DBNODE OFF");
		}


		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("cluster");
			CModule::IncludeModule("cluster");
			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/db/".strtolower($DB->type)."/uninstall.sql");
		}

		UnRegisterModule("cluster");

		if($this->errors !== false)
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
		global $DB;
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			if($DB->type == "MYSQL")
				CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/wizards", $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
			DeleteDirFilesEx("/bitrix/themes/.default/icons/cluster/");
		}
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $step, $USER;
		if($USER->IsAdmin())
		{
			$step = IntVal($step);
			if(!CBXFeatures::IsFeatureEditable("Cluster"))
			{
				$this->errors = array(GetMessage("MAIN_FEATURE_ERROR_EDITABLE"));
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->ThrowException(implode("<br>", $this->errors));
				$APPLICATION->IncludeAdminFile(GetMessage("CLU_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/step2.php");
			}
			elseif($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("CLU_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/step1.php");
			}
			elseif($step==2)
			{
				if($this->InstallDB())
				{
					$this->InstallFiles();
					CBXFeatures::SetFeatureEnabled("Cluster", true);
				}
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("CLU_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $APPLICATION, $step, $USER;
		if($USER->IsAdmin())
		{
			$step = IntVal($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("CLU_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"save_tables" => $_REQUEST["save_tables"],
				));
				$this->UnInstallFiles();
				CBXFeatures::SetFeatureEnabled("Cluster", false);
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("CLU_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/install/unstep2.php");
			}
		}
	}
}
?>