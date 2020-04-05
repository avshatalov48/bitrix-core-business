<?
IncludeModuleLangFile(__FILE__);

class socialservices extends CModule
{
	var $MODULE_ID = "socialservices";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

	function socialservices()
	{
		$arModuleVersion = array();

		include(substr(__FILE__, 0,  -10)."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("socialservices_install_name");
		$this->MODULE_DESCRIPTION = GetMessage("socialservices_install_desc");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$errors = false;
		if(!$DB->Query("SELECT 'x' FROM b_socialservices_user", true))
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/db/".$DBType."/install.sql");
		}

		if ($errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		RegisterModule("socialservices");

		RegisterModuleDependences("main", "OnUserDelete", "socialservices", "CSocServAuthDB", "OnUserDelete");
		RegisterModuleDependences('timeman', 'OnAfterTMReportDailyAdd', 'socialservices', 'CSocServAuthDB', 'OnAfterTMReportDailyAdd');
		RegisterModuleDependences('timeman', 'OnAfterTMDayStart', 'socialservices', 'CSocServAuthDB', 'OnAfterTMDayStart');
		RegisterModuleDependences('timeman', 'OnTimeManShow', 'socialservices', 'CSocServEventHandlers', 'OnTimeManShow');
		RegisterModuleDependences('main', 'OnFindExternalUser', 'socialservices', 'CSocServAuthDB', 'OnFindExternalUser');

		RegisterModuleDependences('socialservices', 'OnFindSocialservicesUser', 'socialservices', "CSocServAuthManager", "checkOldUser");
		RegisterModuleDependences('socialservices', 'OnFindSocialservicesUser', 'socialservices', "CSocServAuthManager", "checkAbandonedUser");

		if(
			\Bitrix\Main\Loader::includeModule('socialservices')
			&& \Bitrix\Main\Config\Option::get('socialservices', 'bitrix24net_id', '') === ''
		)
		{
			$request = \Bitrix\Main\Context::getCurrent()->getRequest();
			$host = ($request->isHttps() ? 'https://' : 'http://').$request->getHttpHost();

			$registerResult = \CSocServBitrix24Net::registerSite($host);

			if(is_array($registerResult) && isset($registerResult["client_id"]) && isset($registerResult["client_secret"]))
			{
				\Bitrix\Main\Config\Option::set('socialservices', 'bitrix24net_domain', $host);
				\Bitrix\Main\Config\Option::set('socialservices', 'bitrix24net_id', $registerResult["client_id"]);
				\Bitrix\Main\Config\Option::set('socialservices', 'bitrix24net_secret', $registerResult["client_secret"]);
			}
		}

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $APPLICATION, $DB, $DOCUMENT_ROOT;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$errors = $DB->RunSQLBatch($DOCUMENT_ROOT."/bitrix/modules/socialservices/install/db/".strtolower($DB->type)."/uninstall.sql");
			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}
		UnRegisterModuleDependences("main", "OnUserDelete", "socialservices", "CSocServAuthDB", "OnUserDelete");
		UnRegisterModuleDependences('socialnetwork', 'OnFillSocNetLogEvents', 'socialservices', 'CSocServEventHandlers', 'OnFillSocNetLogEvents');
		UnRegisterModuleDependences('timeman', 'OnAfterTMReportDailyAdd', 'socialservices', 'CSocServAuthDB', 'OnAfterTMReportDailyAdd');
		UnRegisterModuleDependences('timeman', 'OnAfterTMDayStart', 'socialservices', 'CSocServAuthDB', 'OnAfterTMDayStart');
		UnRegisterModuleDependences('timeman', 'OnTimeManShow', 'socialservices', 'CSocServEventHandlers', 'OnTimeManShow');
		UnRegisterModuleDependences('main', 'OnFindExternalUser', 'socialservices', 'CSocServAuthDB', 'OnFindExternalUser');

		UnRegisterModuleDependences('socialservices', 'OnFindSocialservicesUser', 'socialservices', "CSocServAuthManager", "checkOldUser");
		UnRegisterModuleDependences('socialservices', 'OnFindSocialservicesUser', 'socialservices', "CSocServAuthManager", "checkAbandonedUser");

		$dbSites = CSite::GetList($b="sort", $o="asc", array("ACTIVE" => "Y"));
		while ($arSite = $dbSites->Fetch())
		{
			$siteId = $arSite['ID'];
			CAgent::RemoveAgent("CSocServAuthManager::GetTwitMessages($siteId);", "socialservices");
		}
		CAgent::RemoveAgent("CSocServAuthManager::SendSocialservicesMessages();", "socialservices");

		UnRegisterModule("socialservices");

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
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/gadgets", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFilesEx("/bitrix/js/socialservices/");
			DeleteDirFilesEx("/bitrix/images/socialservices/");
			DeleteDirFilesEx("/bitrix/tools/oauth/");
		}
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("socialservices_install_title_inst"), $DOCUMENT_ROOT."/bitrix/modules/socialservices/install/step1.php");
		}
		else
		{
			$this->InstallFiles();
			$this->InstallDB();
			$APPLICATION->IncludeAdminFile(GetMessage("socialservices_install_title_inst"), $DOCUMENT_ROOT."/bitrix/modules/socialservices/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step, $errors;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("socialservices_install_title_inst"), $DOCUMENT_ROOT."/bitrix/modules/socialservices/install/unstep1.php");
		}
		elseif($step==2)
		{
			$errors = false;

			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));

			$this->UnInstallFiles();

			$APPLICATION->IncludeAdminFile(GetMessage("socialservices_install_title_inst"), $DOCUMENT_ROOT."/bitrix/modules/socialservices/install/unstep2.php");
		}
	}
}
?>