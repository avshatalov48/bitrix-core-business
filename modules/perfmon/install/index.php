<?
IncludeModuleLangFile(__FILE__);

if(class_exists("perfmon")) return;
Class perfmon extends CModule
{
	var $MODULE_ID = "perfmon";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("PERF_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("PERF_MODULE_DESCRIPTION");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_perf_hit WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/db/".mb_strtolower($DB->type)."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("perfmon");
			CModule::IncludeModule("perfmon");
			RegisterModuleDependences("perfmon", "OnGetTableSchema", "perfmon", "perfmon", "OnGetTableSchema");
			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		UnRegisterModuleDependences("main", "OnPageStart", "perfmon", "CPerfomanceKeeper", "OnPageStart");
		UnRegisterModuleDependences("main", "OnEpilog", "perfmon", "CPerfomanceKeeper", "OnEpilog");
		UnRegisterModuleDependences("main", "OnAfterEpilog", "perfmon", "CPerfomanceKeeper", "OnBeforeAfterEpilog");
		UnRegisterModuleDependences("main", "OnAfterEpilog", "perfmon", "CPerfomanceKeeper", "OnAfterAfterEpilog");
		UnRegisterModuleDependences("main", "OnLocalRedirect", "perfmon", "CPerfomanceKeeper", "OnAfterAfterEpilog");
		UnRegisterModuleDependences("perfmon", "OnGetTableSchema", "perfmon", "perfmon", "OnGetTableSchema");

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/db/".mb_strtolower($DB->type)."/uninstall.sql");
		}

		UnRegisterModule("perfmon");

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
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/images/perfmon/");

		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$PERF_RIGHT = $APPLICATION->GetGroupRight("perfmon");
		if($PERF_RIGHT >= "W")
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("PERF_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/step1.php");
			}
			elseif($step==2)
			{
				$this->InstallFiles(array(
				));
				$this->InstallDB(array(
				));
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("PERF_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$PERF_RIGHT = $APPLICATION->GetGroupRight("perfmon");
		if($PERF_RIGHT == "W")
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("PERF_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("PERF_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/unstep2.php");
			}
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","R","W"),
			"reference" => array(
				"[D] ".GetMessage("PERF_DENIED"),
				"[R] ".GetMessage("PERF_VIEW"),
				"[W] ".GetMessage("PERF_ADMIN"))
			);
		return $arr;
	}

	public static function OnGetTableSchema()
	{
		return array(
			"perfmon" => array(
				"b_perf_hit" => array(
					"ID" => array(
						"b_perf_component" => "HIT_ID",
						"b_perf_sql" => "HIT_ID",
						"b_perf_cache" => "HIT_ID",
						"b_perf_error" => "HIT_ID",
					)
				),
				"b_perf_component" => array(
					"ID" => array(
						"b_perf_sql" => "COMPONENT_ID",
						"b_perf_cache" => "COMPONENT_ID",
					),
				),
				"b_perf_sql" => array(
					"ID" => array(
						"b_perf_sql_backtrace" => "SQL_ID",
						"b_perf_index_suggest_sql" => "SQL_ID",
					),
				),
				"b_perf_index_suggest" => array(
					"ID" => array(
						"b_perf_index_suggest_sql" => "SUGGEST_ID",
					),
				),
			),
			"cluster" => array(
				"b_cluster_dbnode" => array(
					"ID" => array(
						"b_perf_sql" => "NODE_ID",
					)
				),
			),
		);
	}
}
?>