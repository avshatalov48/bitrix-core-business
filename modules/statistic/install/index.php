<?
IncludeModuleLangFile(__FILE__);

if(class_exists("statistic")) return;
Class statistic extends CModule
{
	var $MODULE_ID = "statistic";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	var $errors;

	function statistic()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = $STATISTIC_VERSION;
			$this->MODULE_VERSION_DATE = $STATISTIC_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("STAT_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("STAT_MODULE_DESCRIPTION");
		$this->MODULE_CSS = "/bitrix/modules/statistic/statistic.css";
	}

	function InstallDB($arParams = array())
	{
		global $DBType, $APPLICATION;

		$node_id = strlen($arParams["DATABASE"]) > 0? intval($arParams["DATABASE"]): false;

		if($node_id !== false)
			$DB = $GLOBALS["DB"]->GetDBNodeConnection($node_id);
		else
			$DB = $GLOBALS["DB"];

		$this->errors = false;
		$arAllErrors = array();

		// check if module was deinstalled without table save
		$DATE_INSTALL_TABLES = "";
		$no_tables = "N";
		if(!$DB->Query("SELECT count('x') FROM b_stat_day WHERE 1=0", true))
		{
			// last installation date have to be current
			$DATE_INSTALL_TABLES = date("d.m.Y H:i:s",time());
			$no_tables = "Y";
		}

		if($no_tables == "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/db/".strtolower($DB->type)."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		RegisterModule("statistic");

		RegisterModuleDependences("main", "OnPageStart", "statistic", "CStopList", "Check", "100");
		RegisterModuleDependences("main", "OnBeforeProlog", "statistic", "CStatistics", "Keep", "100");
		RegisterModuleDependences("main", "OnEpilog", "statistic", "CStatistics", "Set404", "100");
		RegisterModuleDependences("main", "OnBeforeProlog", "statistic", "CStatistics", "StartBuffer", "1000");
		RegisterModuleDependences("main", "OnAfterEpilog", "statistic", "CStatistics", "EndBuffer", "10");
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "statistic", "CStatistics", "GetAuditTypes", 10);

		RegisterModuleDependences("statistic", "OnCityLookup", "statistic", "CCityLookup_geoip_mod", "OnCityLookup", "100");
		RegisterModuleDependences("statistic", "OnCityLookup", "statistic", "CCityLookup_geoip_extension", "OnCityLookup", "200");
		RegisterModuleDependences("statistic", "OnCityLookup", "statistic", "CCityLookup_geoip_pure", "OnCityLookup", "300");
		RegisterModuleDependences("statistic", "OnCityLookup", "statistic", "CCityLookup_stat_table", "OnCityLookup", "400");

		RegisterModuleDependences("cluster", "OnGetTableList", "statistic", "statistic", "OnGetTableList");

		if (strlen($DATE_INSTALL_TABLES)>0)
			COption::SetOptionString("main", "INSTALL_STATISTIC_TABLES", $DATE_INSTALL_TABLES, "Date of installation of statistics module tables");

		if($node_id !== false)
		{
			COption::SetOptionString("statistic", "dbnode_id", $node_id);
			if(CModule::IncludeModule('cluster'))
				CClusterDBNode::SetOnline($node_id);
		}
		else
		{
			COption::SetOptionString("statistic", "dbnode_id", "N");
		}
		COption::SetOptionString("statistic", "dbnode_status", "ok");

		// init counters
		if(array_key_exists("allow_initial", $arParams) && ($arParams["allow_initial"] == "Y"))
		{
			$strSql = "SELECT ID FROM b_stat_day";
			$e = $DB->Query($strSql, false, $err_mess.__LINE__);
			if (!($er = $e->Fetch()))
			{
				if (intval($arParams["START_HITS"])>0 || intval($arParams["START_HOSTS"])>0 || intval($arParams["START_GUESTS"])>0)
				{
					$arFields = Array(
						"DATE_STAT"	=> $DB->GetNowDate(),
						"HITS"		=> intval($arParams["START_HITS"]),
						"C_HOSTS"	=> intval($arParams["START_HOSTS"]),
						"GUESTS"	=> intval($arParams["START_GUESTS"]),
						"NEW_GUESTS"	=> intval($arParams["START_GUESTS"]),
						);
					$DB->Insert("b_stat_day",$arFields, $err_mess.__LINE__);
				}
			}
		}

		$arr = getdate();

		$ndate = mktime(0, 1, 0, $arr["mon"], $arr["mday"], $arr["year"]);
		CAgent::AddAgent("CStatistics::SetNewDay();","statistic", "Y", 86400, "", "Y", ConvertTimeStamp($ndate+CTimeZone::GetOffset(), "FULL"), 200);
		$ndate = mktime(3, 0, 0, $arr["mon"], $arr["mday"], $arr["year"]);
		CAgent::AddAgent("CStatistics::CleanUpStatistics_1();","statistic", "Y", 86400, "", "Y", ConvertTimeStamp($ndate+CTimeZone::GetOffset(), "FULL"), 50);
		$ndate = mktime(4, 0, 0, $arr["mon"], $arr["mday"], $arr["year"]);
		CAgent::AddAgent("CStatistics::CleanUpStatistics_2();","statistic", "Y", 86400, "", "Y", ConvertTimeStamp($ndate+CTimeZone::GetOffset(), "FULL"), 30);
		CAgent::AddAgent("CStatistics::CleanUpSessionData();","statistic","N",7200);
		CAgent::AddAgent("CStatistics::CleanUpPathCache();","statistic", "N", 3600);

		CAgent::RemoveAgent("SendDailyStatistics();","statistic");
		if(strpos($_SERVER["SERVER_SOFTWARE"], "(Win32)")<=0)
		{
			$ndate = mktime(9, 0, 0, $arr["mon"], $arr["mday"], $arr["year"]);
			CAgent::AddAgent("SendDailyStatistics();", "statistic", "Y", 86400, "", "Y", ConvertTimeStamp($ndate+CTimeZone::GetOffset(), "FULL"), 25);
		}

		if($no_tables=="Y")
		{
			$arAllErrors[] = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]. "/bitrix/modules/statistic/install/db/".strtolower($DB->type)."/searchers.sql");
			$arAllErrors[] = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]. "/bitrix/modules/statistic/install/db/".strtolower($DB->type)."/browsers.sql");
			$arAllErrors[] = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]. "/bitrix/modules/statistic/install/db/".strtolower($DB->type)."/adv.sql");
		}

		// ip-to-country
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/ip_tools.php");
		i2c_load_countries();
		if(!array_key_exists("CREATE_I2C_INDEX", $arParams) || ($arParams["CREATE_I2C_INDEX"] == "Y"))
			i2c_create_db($total_reindex, $reindex_success, $step_reindex, $int_prev);

		$fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/db/".strtolower($DB->type)."/optimize.sql";
		if(file_exists($fname))
		{
			$arAllErrors[] = $DB->RunSQLBatch($fname);
		}

		$this->errors = array();
		foreach($arAllErrors as $ar)
		{
			if(is_array($ar))
			{
				foreach($ar as $strError)
					$this->errors[] = $strError;
			}
		}
		if(count($this->errors) < 1)
			$this->errors = false;

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DBType, $APPLICATION;

		$DB = CDatabase::GetModuleConnection('statistic', true);

		$this->errors = false;

		if(is_object($DB))
		{
			if(!array_key_exists("savedata", $arParams) || ($arParams["savedata"] != "Y"))
			{
				$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/db/".strtolower($DB->type)."/uninstall.sql");
				COption::RemoveOption("main","INSTALL_STATISTIC_TABLES");

				$db_res = $GLOBALS["DB"]->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'statistic'");
				while($arRes = $db_res->Fetch())
					CFile::Delete($arRes["ID"]);
			}
		}

		UnRegisterModuleDependences("main", "OnPageStart", "statistic", "CStopList", "Check");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "statistic", "CStatistics", "Keep");
		UnRegisterModuleDependences("main", "OnEpilog", "statistic", "CStatistics", "Set404");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "statistic", "CStatistics", "GetAuditTypes");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "statistic", "CStatistics", "StartBuffer");
		UnRegisterModuleDependences("main", "OnAfterEpilog", "statistic", "CStatistics", "EndBuffer");
		UnRegisterModuleDependences("cluster", "OnGetTableList", "statistic", "statistic", "OnGetTableList");

		UnRegisterModule("statistic");

		COption::SetOptionString("statistic", "dbnode_id", "N");
		COption::SetOptionString("statistic", "dbnode_status", "ok");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function InstallEvents()
	{
		global $DB;
		$sIn = "'STATISTIC_DAILY_REPORT', 'STATISTIC_ACTIVITY_EXCEEDING'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		$sIn = "'STATISTIC_DAILY_REPORT', 'STATISTIC_ACTIVITY_EXCEEDING'";
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/public/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix", true, true);//all from bitrix
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/components/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/gadgets/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets/bitrix", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/public/bitrix/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/public/bitrix/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
			DeleteDirFilesEx("/bitrix/themes/.default/icons/statistic/");//icons
			DeleteDirFilesEx("/bitrix/images/statistic/");//images
		}
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;
		$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
		$step = intval($step);

		if($STAT_RIGHT < "W")
			return;

		if(!CBXFeatures::IsFeatureEditable("Analytics"))
		{
			$this->errors = array(GetMessage("MAIN_FEATURE_ERROR_EDITABLE"));
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			$APPLICATION->IncludeAdminFile(GetMessage("STAT_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/step2.php");
		}
		elseif($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("STAT_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/step1.php");
		}
		elseif($step == 2)
		{
			$db_install_ok = $this->InstallDB(array(
				"allow_initial" => $_REQUEST["allow_initial"],
				"START_HITS" => $_REQUEST["START_HITS"],
				"START_HOSTS" => $_REQUEST["START_HOSTS"],
				"START_GUESTS" => $_REQUEST["START_GUESTS"],
				"CREATE_I2C_INDEX" => $_REQUEST["CREATE_I2C_INDEX"],
				"DATABASE" => $_REQUEST["DATABASE"],
			));
			if($db_install_ok)
			{
				$this->InstallEvents();
				$this->InstallFiles();
				CBXFeatures::SetFeatureEnabled("Analytics", true);
			}
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("STAT_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
		if ($STAT_RIGHT>="W")
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("STAT_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));
				//message types and templates
				if($_REQUEST["save_templates"] != "Y")
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				CBXFeatures::SetFeatureEnabled("Analytics", false);
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("STAT_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/install/unstep2.php");
			}
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","M","R","W"),
			"reference" => array(
				"[D] ".GetMessage("STAT_DENIED"),
				"[M] ".GetMessage("STAT_VIEW_WITHOUT_MONEY"),
				"[R] ".GetMessage("STAT_VIEW"),
				"[W] ".GetMessage("STAT_ADMIN"))
			);
		return $arr;
	}

	public static function OnGetTableList()
	{
		return array(
			"MODULE" => new statistic,
			"TABLES" => array(
				"b_stat_adv_searcher" =>"ID",
				"b_stat_adv" =>"ID",
				"b_stat_adv_event" =>"ID",
				"b_stat_adv_guest" =>"ID",
				"b_stat_adv_page" =>"ID",
				"b_stat_day" =>"ID",
				"b_stat_day_site" =>"ID",
				"b_stat_event" =>"ID",
				"b_stat_event_day" =>"ID",
				"b_stat_event_list" =>"ID",
				"b_stat_guest" =>"ID",
				"b_stat_hit" =>"ID",
				"b_stat_searcher_hit" =>"ID",
				"b_stat_phrase_list" =>"ID",
				"b_stat_referer" =>"ID",
				"b_stat_referer_list" =>"ID",
				"b_stat_searcher" =>"ID",
				"b_stat_searcher_params" =>"ID",
				"b_stat_session" =>"ID",
				"b_stat_page" =>"ID",
				"b_stop_list" =>"ID",
				"b_stat_browser" =>"ID",
				"b_stat_adv_day" =>"ID",
				"b_stat_adv_event_day" =>"ID",
				"b_stat_searcher_day" =>"ID",
				"b_stat_country" =>"ID",
				"b_stat_city" =>"ID",
				"b_stat_city_day" =>"ID",
				"b_stat_city_ip" =>"START_IP",
				"b_stat_session_data" =>"ID",
				"b_stat_country_day" =>"ID",
				"b_stat_path" =>"ID",
				"b_stat_path_adv" =>"ID",
				"b_stat_path_cache" =>"ID",
				"b_stat_page_adv" =>"ID",
				"b_stat_ddl" =>"ID",
			),
		);
	}
}
?>