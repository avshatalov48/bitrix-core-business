<?php
IncludeModuleLangFile(__FILE__);

if(class_exists("advertising")) return;

class advertising extends CModule
{
	var $MODULE_ID = "advertising";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";

	function advertising()
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
			$this->MODULE_VERSION = ADVERTISING_VERSION;
			$this->MODULE_VERSION_DATE = ADVERTISING_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("AD_INSTALL_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("AD_INSTALL_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		global $APPLICATION, $errors;
		$ADV_RIGHT = $APPLICATION->GetGroupRight("advertising");
		if ($ADV_RIGHT=="W")
		{
			$errors = false;

			$this->InstallFiles();
			$this->InstallDB();
			$this->InstallEvents();

			$APPLICATION->IncludeAdminFile(
				GetMessage("AD_INSTALL"),
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/inst.php"
			);
		}
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/public", $_SERVER["DOCUMENT_ROOT"]."/bitrix");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/public/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/advertising/", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", True, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", True, True);
		}
		return true;
	}

	function InstallDB()
	{
		global $APPLICATION, $DB, $errors;

		if (!$DB->Query("SELECT 'x' FROM b_adv_banner", true)) $EMPTY = "Y"; else $EMPTY = "N";

		if ($EMPTY=="Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/db/".strtolower($DB->type)."/install.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		RegisterModule("advertising");

		RegisterModuleDependences("main", "OnBeforeProlog", "advertising");
		RegisterModuleDependences("main", "OnEndBufferContent", "advertising", "CAdvBanner", "FixShowAll");
		RegisterModuleDependences("main", "OnBeforeRestartBuffer", "advertising", "CAdvBanner", "BeforeRestartBuffer");

		RegisterModuleDependences('conversion', 'OnGetCounterTypes' , 'advertising', '\Bitrix\Advertising\Internals\ConversionHandlers', 'onGetCounterTypes');
		RegisterModuleDependences('conversion', 'OnGetRateTypes' , 'advertising', '\Bitrix\Advertising\Internals\ConversionHandlers', 'onGetRateTypes');
		RegisterModuleDependences('advertising', 'onBannerClick', 'advertising', '\Bitrix\Advertising\Internals\ConversionHandlers', 'onBannerClick');

		CAgent::AddAgent("CAdvContract::SendInfo();","advertising", "N", 7200);
		CAgent::AddAgent("CAdvBanner::CleanUpDynamics();","advertising", "N", 86400);

		if ($EMPTY=="Y")
		{
			CModule::IncludeModule('advertising');

			$arSites = array();
			$rs = CSite::GetList($b="sort", $o="asc");
			while($ar = $rs->Fetch())
			{
				$arSites[] = $ar['ID'];
			}

			$ac = new CAdvContract();
			$arFields = array(
				'ACTIVE' => 'Y',
				'NAME' => 'Default',
				'SORT' => 10000,
				'DESCRIPTION' => 'all site without any restrictions',
				'EMAIL_COUNT' => 1,
				'arrTYPE' => array('ALL'),
				'arrWEEKDAY' => array(
					'MONDAY'	=> array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
					'SATURDAY'	=> array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
					'SUNDAY'	=> array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
					'THURSDAY'	=> array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
					'TUESDAY'	=> array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
					'WEDNESDAY'	=> array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
					'FRIDAY'	=> array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
				),
				'arrSITE' => $arSites,
			);
			$ac->Set($arFields, 0, 'N');
		}

		return true;
	}

	function InstallEvents()
	{
		global $DB;
		$sIn = "'ADV_BANNER_STATUS_CHANGE', 'ADV_CONTRACT_INFO'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/events/set_events.php");
		}
		return true;
	}

	function DoUninstall()
	{
		global $APPLICATION, $DB, $errors, $step;
		$ADV_RIGHT = $APPLICATION->GetGroupRight("advertising");
		if ($ADV_RIGHT=="W")
		{
			$step = IntVal($step);
			$errors = false;

			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(
					GetMessage("AD_DELETE_TITLE"),
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/uninst1.php"
				);
			}
			elseif ($step == 2)
			{
				$errors = false;

				$this->UnInstallDB();
				$this->UnInstallFiles();
				$this->UnInstallEvents();

				$APPLICATION->IncludeAdminFile(
					GetMessage("AD_DELETE_TITLE"),
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/uninst2.php"
				);
			}
		}
	}

	function UnInstallFiles()
	{
		global $DB;

		if (!array_key_exists("savedata", $_REQUEST) || $_REQUEST["savedata"] !== "Y")
		{
			$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'advertising'");
			while ($arRes = $db_res->Fetch())
			{
				CFile::Delete($arRes["ID"]);
			}
		}

		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/advertising/");//icons
		DeleteDirFilesEx("/bitrix/images/advertising/");//images
		DeleteDirFilesEx("/bitrix/js/advertising/");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/public", $_SERVER["DOCUMENT_ROOT"]."/bitrix");

		return true;
	}

	function UnInstallDB()
	{
		global $APPLICATION, $DB, $errors;

		if (!array_key_exists("savedata", $_REQUEST) || $_REQUEST["savedata"] !== "Y")
		{
			$errors = false;
			// delete whole base
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/db/".strtolower($DB->type)."/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		// agents
		CAgent::RemoveModuleAgents("advertising");

		// module events
		UnRegisterModuleDependences("main", "OnBeforeRestartBuffer", "advertising", "CAdvBanner", "BeforeRestartBuffer");
		UnRegisterModuleDependences("main", "OnEndBufferContent", "advertising", "CAdvBanner", "FixShowAll");
		UnRegisterModuleDependences("main", "OnBeforeProlog", "advertising");

		UnRegisterModuleDependences('conversion', 'OnGetCounterTypes' , 'advertising', '\Bitrix\Advertising\Internals\ConversionHandlers', 'onGetCounterTypes');
		UnRegisterModuleDependences('conversion', 'OnGetRateTypes' , 'advertising', '\Bitrix\Advertising\Internals\ConversionHandlers', 'onGetRateTypes');
		UnRegisterModuleDependences('advertising', 'onBannerClick', 'advertising', '\Bitrix\Advertising\Internals\ConversionHandlers', 'onBannerClick');

		UnRegisterModule("advertising");

		return true;
	}

	function UnInstallEvents()
	{
		global $DB;

		if (!array_key_exists("savedata", $_REQUEST) || $_REQUEST["savedata"] !== "Y")
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/install/events/del_events.php");
		}

		return true;
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","R","T","V","W"),
			"reference" => array(
				"[D] ".GetMessage("AD_DENIED"),
				"[R] ".GetMessage("AD_ADVERTISER"),
				"[T] ".GetMessage("AD_BANNERS_MANAGER"),
				"[V] ".GetMessage("AD_DEMO"),
				"[W] ".GetMessage("AD_ADMIN"))
			);
		return $arr;
	}
}
