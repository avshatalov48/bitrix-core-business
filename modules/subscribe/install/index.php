<?
IncludeModuleLangFile(__FILE__);

if(class_exists("subscribe")) return;
class subscribe extends CModule
{
	var $MODULE_ID = "subscribe";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	var $errors;

	function subscribe()
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
			$this->MODULE_VERSION = SUBSCRIBE_VERSION;
			$this->MODULE_VERSION_DATE = SUBSCRIBE_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("inst_module_name");
		$this->MODULE_DESCRIPTION = GetMessage("inst_module_desc");
		$this->MODULE_CSS = "/bitrix/modules/subscribe/styles.css";
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_list_rubric WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/db/".$DBType."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("subscribe");
			CModule::IncludeModule("subscribe");

			RegisterModuleDependences("main", "OnBeforeLangDelete", "subscribe", "CRubric", "OnBeforeLangDelete");
			RegisterModuleDependences("main", "OnUserDelete", "subscribe", "CSubscription", "OnUserDelete");
			RegisterModuleDependences("main", "OnUserLogout", "subscribe", "CSubscription", "OnUserLogout");
			RegisterModuleDependences("main", "OnGroupDelete", "subscribe", "CPosting", "OnGroupDelete");
			RegisterModuleDependences("sender", "OnConnectorList", "subscribe", "Bitrix\\Subscribe\\SenderEventHandler", "onConnectorListSubscriber");

			//agents
			CAgent::RemoveAgent("CSubscription::CleanUp();", "subscribe");

			CTimeZone::Disable();
			CAgent::Add(array(
				"NAME"=>"CSubscription::CleanUp();",
				"MODULE_ID"=>"subscribe",
				"ACTIVE"=>"Y",
				"NEXT_EXEC"=>date("d.m.Y H:i:s", mktime(3,0,0,date("m"),date("j")+1,date("Y"))),
				"AGENT_INTERVAL"=>86400,
				"IS_PERIOD"=>"Y"
			));
			CTimeZone::Enable();

			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("save_tables", $arParams) || ($arParams["save_tables"] != "Y"))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/db/".$DBType."/uninstall.sql");
			$strSql = "SELECT ID FROM b_file WHERE MODULE_ID='subscribe'";
			$rsFile = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($arFile = $rsFile->Fetch())
				CFile::Delete($arFile["ID"]);
		}

		UnRegisterModuleDependences("main", "OnBeforeLangDelete", "subscribe", "CRubric", "OnBeforeLangDelete");
		UnRegisterModuleDependences("main", "OnUserDelete", "subscribe", "CSubscription", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnGroupDelete", "subscribe", "CPosting", "OnGroupDelete");
		UnRegisterModuleDependences("main", "OnUserLogout", "subscribe", "CSubscription", "OnUserLogout");
		UnRegisterModuleDependences("sender", "OnConnectorList", "subscribe", "Bitrix\\Subscribe\\SenderEventHandler", "onConnectorListSubscriber");

		UnRegisterModule("subscribe");

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
		$sIn = "'LIST_MESSAGE','SUBSCRIBE_CONFIRM'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		$sIn = "'LIST_MESSAGE','SUBSCRIBE_CONFIRM'";
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", false, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", True, True);
		}

		if(array_key_exists("install_auto_templates", $arParams) && $arParams["install_auto_templates"] == "Y")
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/php_interface", $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface", false, true);
		}

		$bReWriteAdditionalFiles = ($arParams["public_rewrite"] == "Y");

		if(
			array_key_exists("install_public", $arParams) && ($arParams["install_public"] == "Y")
			&& array_key_exists("public_dir", $arParams) && strlen($arParams["public_dir"])
		)
		{
			$rsSite = CSite::GetList(($by="sort"),($order="asc"));
			while ($site = $rsSite->Fetch())
			{
				$source = $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/subscribe/public/";
				$target = $site['ABS_DOC_ROOT'].$site["DIR"].$arParams["public_dir"]."/";
				if(file_exists($source))
				{
					CheckDirPath($target);
					$dh = opendir($source);
					while($file = readdir($dh))
					{
						if($file == "." || $file == "..")
							continue;
						if($bReWriteAdditionalFiles || !file_exists($target.$file))
						{
							$fh = fopen($source.$file, "rb");
							$php_source = fread($fh, filesize($source.$file));
							fclose($fh);
							if(preg_match_all('/GetMessage\("(.*?)"\)/', $php_source, $matches))
							{
								IncludeModuleLangFile($source.$file, $site["LANGUAGE_ID"]);
								foreach($matches[0] as $i => $text)
								{
									$php_source = str_replace(
										$text,
										'"'.GetMessage($matches[1][$i]).'"',
										$php_source
									);
								}
							}
							$fh = fopen($target.$file, "wb");
							fwrite($fh, $php_source);
							fclose($fh);
						}
					}
				}
			}
		}

		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			//admin files
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			//css
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		}
		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
		if($POST_RIGHT == "W")
		{
			$step = IntVal($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("inst_inst_title"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/inst1.php");
			}
			elseif($step==2)
			{
				if($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles(array(
						"install_auto_templates" => $_REQUEST["install_auto_templates"],
						"install_public" => $_REQUEST["install_public"],
						"public_dir" => $_REQUEST["public_dir"],
						"public_rewrite" => $_REQUEST["public_rewrite"],
					));
				}
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("inst_inst_title"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/inst2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
		if($POST_RIGHT == "W")
		{
			$step = IntVal($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("inst_uninst_title"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/uninst1.php");
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
				$APPLICATION->IncludeAdminFile(GetMessage("inst_uninst_title"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/uninst2.php");
			}
		}
	}

}
?>