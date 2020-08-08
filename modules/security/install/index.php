<?
IncludeModuleLangFile(__FILE__);

if(class_exists("security")) return;
Class security extends CModule
{
	var $MODULE_ID = "security";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function security()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("SEC_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SEC_MODULE_DESCRIPTION");
	}

	function GetModuleTasks()
	{
		return array(
			'security_denied' => array(
				"LETTER" => "D",
				"BINDING" => "module",
				"OPERATIONS" => array(
				),
			),
			'security_filter' => array(
				"LETTER" => "F",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'security_filter_bypass',
				),
			),
			'security_otp' => array(
				"LETTER" => "S",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'security_edit_user_otp',
				),
			),
			'security_view_all_settings' => array(
				"LETTER" => "T",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'security_module_settings_read',
					'security_panel_view',
					'security_filter_settings_read',
					'security_otp_settings_read',
					'security_iprule_admin_settings_read',
					'security_session_settings_read',
					'security_redirect_settings_read',
					'security_stat_activity_settings_read',
					'security_iprule_settings_read',
					'security_antivirus_settings_read',
					'security_frame_settings_read',
				),
			),
			'security_full_access' => array(
				"LETTER" => "W",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'security_edit_user_otp',
					'security_filter_bypass',
					'security_module_settings_read',
					'security_module_settings_write',
					'security_panel_view',
					'security_filter_settings_read',
					'security_filter_settings_write',
					'security_otp_settings_read',
					'security_otp_settings_write',
					'security_iprule_admin_settings_read',
					'security_iprule_admin_settings_write',
					'security_session_settings_read',
					'security_session_settings_write',
					'security_redirect_settings_read',
					'security_redirect_settings_write',
					'security_stat_activity_settings_read',
					'security_stat_activity_settings_write',
					'security_iprule_settings_read',
					'security_iprule_settings_write',
					'security_file_verifier_sign',
					'security_file_verifier_collect',
					'security_file_verifier_verify',
					'security_antivirus_settings_read',
					'security_antivirus_settings_write',
					'security_frame_settings_read',
					'security_frame_settings_write',
				),
			),
		);
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_sec_iprule WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/db/".mb_strtolower($DB->type)."/install.sql");
		}


		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			$this->InstallTasks();

			RegisterModule("security");
			RegisterModuleDependences("main", "OnUserDelete", "security", "CSecurityUser", "OnUserDelete");
			RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "security", "CSecurityFilter", "GetAuditTypes");
			RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "security", "CSecurityAntiVirus", "GetAuditTypes");
			RegisterModuleDependences("main", "OnAdminInformerInsertItems", "security", "CSecurityFilter", "OnAdminInformerInsertItems");
			RegisterModuleDependences("main", "OnAdminInformerInsertItems", "security", "CSecuritySiteChecker", "OnAdminInformerInsertItems");
			CModule::IncludeModule("security");

			//agents
			CAgent::RemoveAgent("CSecuritySession::CleanUpAgent();", "security");
			CAgent::Add(array(
				"NAME"=>"CSecuritySession::CleanUpAgent();",
				"MODULE_ID"=>"security",
				"ACTIVE"=>"Y",
				"AGENT_INTERVAL"=>1800,
				"IS_PERIOD"=>"N",
			));

			CAgent::RemoveAgent("CSecurityIPRule::CleanUpAgent();", "security");
			CAgent::Add(array(
				"NAME"=>"CSecurityIPRule::CleanUpAgent();",
				"MODULE_ID"=>"security",
				"ACTIVE"=>"Y",
				"AGENT_INTERVAL"=>3600,
				"IS_PERIOD"=>"N",
			));

			if(!COption::GetOptionString("security", "ipcheck_disable_file"))
				COption::SetOptionString("security", "ipcheck_disable_file", "/bitrix/modules/ipcheck_disable_".md5(mt_rand()));

			CAgent::RemoveAgent("CSecurityFilter::ClearTmpFiles();", "security");
			CSecurityFilter::SetActive(true);
			CSecurityRedirect::SetActive(true);

			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		UnRegisterModuleDependences("main", "OnPageStart", "security", "CSecurityIPRule", "OnPageStart");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "security", "CSecurityFilter", "OnBeforeProlog");
		UnRegisterModuleDependences("main", "OnEndBufferContent", "security", "CSecurityXSSDetect", "OnEndBufferContent");
		UnRegisterModuleDependences("main", "OnBeforeUserLogin", "security", "CSecurityUser", "OnBeforeUserLogin");
		UnRegisterModuleDependences("main", "OnUserDelete", "security", "CSecurityUser", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "security", "CSecurityFilter", "GetAuditTypes");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "security", "CSecurityAntiVirus", "GetAuditTypes");
		UnRegisterModuleDependences("main", "OnBeforeLocalRedirect", "security", "CSecurityRedirect", "BeforeLocalRedirect");
		UnRegisterModuleDependences("main", "OnEndBufferContent", "security", "CSecurityRedirect", "EndBufferContent");
		UnRegisterModuleDependences("main", "OnAdminInformerInsertItems", "security", "CSecurityFilter", "OnAdminInformerInsertItems");
		UnRegisterModuleDependences("main", "OnAdminInformerInsertItems", "security", "CSecuritySiteChecker", "OnAdminInformerInsertItems");

		COption::SetOptionString("main", "session_id_ttl", "60");
		COption::SetOptionString("main", "use_session_id_ttl", "N");
		COption::SetOptionInt("main", "session_id_ttl", 60);
		COption::SetOptionString("security", "session", "N");

		if(!array_key_exists("save_tables", $arParams) || $arParams["save_tables"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/db/".mb_strtolower($DB->type)."/uninstall.sql");
			$this->UnInstallTasks();
		}

		UnRegisterModule("security");

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
		$sIn = "'VIRUS_DETECTED'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		$sIn = "'VIRUS_DETECTED'";
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/otp", $_SERVER["DOCUMENT_ROOT"]."/bitrix/otp", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/js/security", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/security", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/security", false, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", True, True);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/otp/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/otp");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/js/security/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/security");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/security/");
		DeleteDirFilesEx("/bitrix/images/security/");

		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$SEC_RIGHT = $APPLICATION->GetGroupRight("security");
		if($SEC_RIGHT >= "W")
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("SEC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/step1.php");
			}
			elseif($step==2)
			{
				if($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles();
				}
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("SEC_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$SEC_RIGHT = $APPLICATION->GetGroupRight("security");
		if($SEC_RIGHT >= "W")
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("SEC_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"save_tables" => $_REQUEST["save_tables"],
				));
				//message types and templates
				if($_REQUEST["save_templates"] != "Y")
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("SEC_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/unstep2.php");
			}
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","F","S","T","W"),
			"reference" => array(
				"[D] ".GetMessage("SEC_DENIED"),
				"[F] ".GetMessage("SEC_FILTER"),
				"[S] ".GetMessage("SEC_PASSWORD"),
				"[T] ".GetMessage("SEC_VIEW"),
				"[W] ".GetMessage("SEC_ADMIN"),
			)
		);
		return $arr;
	}

	public function migrateToBox()
	{
		CModule::IncludeModule('security');
		CSecuritySession::deactivate();
	}
}
