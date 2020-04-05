<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class ldap extends CModule
{
	var $MODULE_ID = "ldap";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	
	var $errors = array();

	function ldap()
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
			$this->MODULE_VERSION = LDAP_VERSION;
			$this->MODULE_VERSION_DATE = LDAP_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("LDAP_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("LDAP_MODULE_DESC");
	}
	
	function CheckLDAP()
	{
		if(!function_exists("ldap_connect"))
		{
			$this->errors[] = GetMessage("LDAP_MOD_INST_ERROR_PHP");
			return false;
		}
		return true;
	}
	
	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = array();
		if ($this->CheckLDAP())
		{
			$errors = false;
			
			if(!$DB->Query("SELECT 'x' FROM b_ldap_server WHERE 1=0", true))
			{
				$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ldap/install/db/".$DBType."/install.sql");
			}
			
			if (is_array($errors))
			{
				$this->errors = array_merge($this->errors, $errors);
			}
			else 
			{
				RegisterModule("ldap");
				RegisterModuleDependences("main", "OnUserLoginExternal", "ldap", "CLdap", "OnUserLogin", 1);
				RegisterModuleDependences("main", "OnExternalAuthList", "ldap", "CLdap", "OnExternalAuthList");
				RegisterModuleDependences('main', 'OnFindExternalUser', 'ldap', 'CLDAP', 'OnFindExternalUser');
			}
		}
		
		if(count($this->errors) > 0)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;		
	}
	
	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION, $DBType;
		$errors = false;
		if($arParams['savedata']!="Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ldap/install/db/".$DBType."/uninstall.sql");
			if (!is_array($errors))
				COption::RemoveOption('ldap');
		}
		
		if (!is_array($errors))
		{
			UnRegisterModuleDependences("main", "OnUserLoginExternal", "ldap", "CLdap", "OnUserLogin");
			UnRegisterModuleDependences("main", "OnExternalAuthList", "ldap", "CLdap", "OnExternalAuthList");
			UnRegisterModuleDependences('main', 'OnBeforeProlog', 'ldap', 'CLDAP', 'NTLMAuth');
			UnRegisterModuleDependences('main', 'OnFindExternalUser', 'ldap', 'CLDAP', 'OnFindExternalUser');
			UnRegisterModule("ldap");
		}
		else
		{
			$APPLICATION->ThrowException(implode("<br>", $errors));
			return false;
		}

		return true;		
	}
	
	function InstallEvents()
	{
		if (!$this->CheckLDAP())
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		$dbLang = CLanguage::GetList($by = "name", $order = "asc");
		while($arLang = $dbLang->Fetch())
		{
			$lid = $arLang["LID"];
			IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/bitrix/modules/ldap/install/events.php", $lid);

			$et = new CEventType;
			$et->Add(array(
				"LID" => $lid,
				"EVENT_NAME" => "LDAP_USER_CONFIRM",
				"NAME" => GetMessage("LDAP_USER_CONFIRM_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("LDAP_USER_CONFIRM_TYPE_DESC"),
			));

			$arSites = array();
			$sites = CSite::GetList($by = "name", $order = "asc", Array("LANGUAGE_ID"=> $lid));
			while ($site = $sites->Fetch())
				$arSites[] = $site["LID"];

			if(count($arSites) > 0)
			{
				$mess = new CEventMessage;
				$mess->Add(array(
					"ACTIVE" => "Y",
					"EVENT_NAME" => "LDAP_USER_CONFIRM",
					"LID" => $arSites,
					"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
					"EMAIL_TO" => "#EMAIL#",
					"BCC" => "#BCC#",
					"SUBJECT" => GetMessage("LDAP_USER_CONFIRM_EVENT_NAME"),
					"MESSAGE" => GetMessage("LDAP_USER_CONFIRM_EVENT_DESC", array("#LANGUAGE_ID#" => $lid)),
					"BODY_TYPE" => "text",
				));
			}
		}

		return true;
	}

	function UnInstallEvents()
	{	
		$dbEvent = CEventMessage::GetList($by, $order, Array("EVENT_NAME" => "LDAP_USER_CONFIRM"));
		while ($arEvent = $dbEvent->Fetch())
			CEventMessage::Delete($arEvent["ID"]);

		$eventType = new CEventType;
		$eventType->Delete("LDAP_USER_CONFIRM");

		return true;
	}
	
	function InstallFiles($arParams = array())
	{
		global $APPLICATION;
		if ($this->CheckLDAP())
		{
			if($_ENV["COMPUTERNAME"]!='BX')
			{
				CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ldap/install/images", $_SERVER['DOCUMENT_ROOT']."/bitrix/images/ldap");
				CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ldap/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
				CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ldap/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			}
		}

		if(count($this->errors) > 0)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;		
		
	}
	
	function UnInstallFiles($arParams = array())
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ldap/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ldap/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/ldap/");//icons
		DeleteDirFilesEx("/bitrix/images/ldap/");//images
		
		return true;
	}

	function DoInstall()
	{
		global $DB, $DBType, $DOCUMENT_ROOT, $APPLICATION;
		$APPLICATION->ResetException();
		if ($this->InstallDB())
		{
			$this->InstallFiles();
			$this->InstallEvents();
		}
		$APPLICATION->IncludeAdminFile(GetMessage("LDAP_INSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/ldap/install/step1.php");
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step, $DBType;
		$step = IntVal($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(GetMessage("LDAP_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/ldap/install/unstep1.php");
		elseif($step==2)
		{
			$APPLICATION->ResetException();
			if ($this->UnInstallDB(array('savedata' => $_REQUEST['savedata'])))
			{
				$this->UnInstallFiles();
				$this->UnInstallEvents();
			}
			$APPLICATION->IncludeAdminFile(GetMessage("LDAP_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/ldap/install/unstep2.php");
		}
	}
}
?>