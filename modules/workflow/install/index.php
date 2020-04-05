<?
IncludeModuleLangFile(__FILE__);

if(class_exists("workflow")) return;
Class workflow extends CModule
{
	var $MODULE_ID = "workflow";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function workflow()
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
			$this->MODULE_VERSION = WORKFLOW_VERSION;
			$this->MODULE_VERSION_DATE = WORKFLOW_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("FLOW_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("FLOW_MODULE_DESCRIPTION");
		$this->MODULE_CSS = "/bitrix/modules/workflow/workflow.css";
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		$bDBInstall = !$DB->Query("SELECT 'x' FROM b_workflow_document WHERE 1=0", true);
		if($bDBInstall)
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/db/".$DBType."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("workflow");
			CModule::IncludeModule("workflow");

			if($bDBInstall)
			{
				$obWorkflowStatus = new CWorkflowStatus;
				$obWorkflowStatus->Add(array(
					"~TIMESTAMP_X" => $DB->GetNowFunction(),
					"C_SORT" => 300,
					"ACTIVE" => "Y",
					"TITLE" => GetMessage("FLOW_INSTALL_PUBLISHED"),
					"IS_FINAL" => "Y",
					"NOTIFY" => "N",
				));
				$obWorkflowStatus->Add(array(
					"~TIMESTAMP_X" => $DB->GetNowFunction(),
					"C_SORT" => 100,
					"ACTIVE" => "Y",
					"TITLE" => GetMessage("FLOW_INSTALL_DRAFT"),
					"IS_FINAL" => "N",
					"NOTIFY" => "N",
				));
				$obWorkflowStatus->Add(array(
					"~TIMESTAMP_X" => $DB->GetNowFunction(),
					"C_SORT" => 200,
					"ACTIVE" => "Y",
					"TITLE" => GetMessage("FLOW_INSTALL_READY"),
					"IS_FINAL" => "N",
					"NOTIFY" => "Y",
				));
			}

			RegisterModuleDependences("main", "OnPanelCreate", "workflow", "CWorkflow", "OnPanelCreate", "200");
			RegisterModuleDependences("main", "OnChangeFile", "workflow", "CWorkflow", "OnChangeFile");

			//agents
			CAgent::RemoveAgent("CWorkflow::CleanUp();", "workflow");
			CAgent::AddAgent("CWorkflow::CleanUp();", "workflow", "N");

			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || ($arParams["savedata"] != "Y"))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/db/".$DBType."/uninstall.sql");
		}

		UnRegisterModuleDependences("main", "OnPanelCreate", "workflow", "CWorkflow", "OnPanelCreate");
		UnRegisterModuleDependences("main", "OnChangeFile", "workflow", "CWorkflow", "OnChangeFile");

		UnRegisterModule("workflow");

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
		$sIn = "'WF_STATUS_CHANGE', 'WF_NEW_DOCUMENT', 'WF_IBLOCK_STATUS_CHANGE', 'WF_NEW_IBLOCK_ELEMENT'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		$sIn = "'WF_STATUS_CHANGE', 'WF_NEW_DOCUMENT', 'WF_IBLOCK_STATUS_CHANGE', 'WF_NEW_IBLOCK_ELEMENT'";
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/workflow", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/images/workflow/");
		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
		if($WORKFLOW_RIGHT == "W")
		{
			$step = IntVal($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("FLOW_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/step1.php");
			}
			elseif($step == 2)
			{
				if($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles();
				}
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("FLOW_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
		if($WORKFLOW_RIGHT == "W")
		{
			$step = IntVal($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("FLOW_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/unstep1.php");
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
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("FLOW_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/unstep2.php");
			}
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","R","U","W"),
			"reference" => array(
				"[D] ".GetMessage("FLOW_DENIED"),
				"[R] ".GetMessage("FLOW_READ"),
				"[U] ".GetMessage("FLOW_MODIFY"),
				"[W] ".GetMessage("FLOW_WRITE"))
			);
		return $arr;
	}
}
?>