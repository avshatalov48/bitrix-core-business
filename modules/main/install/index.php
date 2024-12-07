<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\Localization\CultureTable;
use Bitrix\Main\Service\GeoIp;
use Bitrix\Main\ModuleTable;
use Bitrix\Main\DB\SqlQueryException;

IncludeModuleLangFile(__FILE__);

class main extends CModule
{
	var $MODULE_ID = "main";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	public function __construct()
	{
		include_once(__DIR__ . '/../classes/general/version.php');

		$this->MODULE_VERSION = SM_VERSION;
		$this->MODULE_VERSION_DATE = SM_VERSION_DATE;

		$this->MODULE_NAME = GetMessage("MAIN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("MAIN_MODULE_DESC");
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;

		if (!is_object($APPLICATION))
		{
			$APPLICATION = new CMain;
		}

		$application = \Bitrix\Main\HttpApplication::getInstance();

		$connectionType = $application->getConnection()->getType();
		$DB->DebugToFile = false;
		$DB->debug = true;

		try
		{
			$application->getConnectionPool()->useMasterOnly(true);
			$res = ModuleTable::getById('main')->fetch();
			$application->getConnectionPool()->useMasterOnly(false);
			if ($res)
			{
				return true;
			}
		}
		catch (SqlQueryException)
		{
		}

		if (defined("MYSQL_TABLE_TYPE") && MYSQL_TABLE_TYPE <> '')
		{
			$DB->Query("SET storage_engine = '" . MYSQL_TABLE_TYPE . "'", true);
		}

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/" . $connectionType . "/install.sql");
		if ($errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/" . $connectionType . "/install_add.sql"))
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/" . $connectionType . "/install_add.sql");
			if ($errors !== false)
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		if (\Bitrix\Main\ORM\Fields\CryptoField::cryptoAvailable())
		{
			\Bitrix\Main\UserPhoneAuthTable::enableCrypto("OTP_SECRET");
			\Bitrix\Main\Authentication\Internal\UserAuthCodeTable::enableCrypto("OTP_SECRET");
		}

		$this->InstallTasks();

		if ($this->InstallGroups() === false)
		{
			return false;
		}

		self::InstallRatings();

		if ($this->InstallLanguages() === false)
		{
			return false;
		}

		if ($this->InstallSites() === false)
		{
			return false;
		}

		RegisterModule("main");
		RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'main', 'CIBlockPropertyUserID', 'GetUserTypeDescription', 100, '/modules/main/tools/prop_userid.php');
		RegisterModuleDependences('main', 'OnUserDelete', 'main', 'CFavorites', 'OnUserDelete', 100, "/modules/main/classes/mysql/favorites.php");
		RegisterModuleDependences('main', 'OnLanguageDelete', 'main', 'CFavorites', 'OnLanguageDelete', 100, "/modules/main/classes/mysql/favorites.php");
		RegisterModuleDependences('main', 'OnUserDelete', 'main', 'CUserOptions', 'OnUserDelete');
		RegisterModuleDependences('main', 'OnChangeFile', 'main', 'CMain', 'OnChangeFileComponent');
		RegisterModuleDependences('main', 'OnUserTypeRightsCheck', 'main', 'CUser', 'UserTypeRightsCheck');
		RegisterModuleDependences('main', 'OnUserLogin', 'main', 'UpdateTools', 'CheckUpdates');
		RegisterModuleDependences('main', 'OnModuleUpdate', 'main', 'UpdateTools', 'SetUpdateResult');
		RegisterModuleDependences('main', 'OnUpdateCheck', 'main', 'UpdateTools', 'SetUpdateError');
		RegisterModuleDependences('main', 'OnPanelCreate', 'main', 'CUndo', 'CheckNotifyMessage');
		RegisterModuleDependences('main', 'OnAfterAddRating', 'main', 'CRatingsComponentsMain', 'OnAfterAddRating');
		RegisterModuleDependences('main', 'OnAfterUpdateRating', 'main', 'CRatingsComponentsMain', 'OnAfterUpdateRating');
		RegisterModuleDependences('main', 'OnSetRatingsConfigs', 'main', 'CRatingsComponentsMain', 'OnSetRatingConfigs');
		RegisterModuleDependences('main', 'OnGetRatingsConfigs', 'main', 'CRatingsComponentsMain', 'OnGetRatingConfigs');
		RegisterModuleDependences('main', 'OnGetRatingsObjects', 'main', 'CRatingsComponentsMain', 'OnGetRatingObject');
		RegisterModuleDependences('main', 'OnGetRatingContentOwner', 'main', 'CRatingsComponentsMain', 'OnGetRatingContentOwner');
		RegisterModuleDependences('main', 'OnAfterAddRatingRule', 'main', 'CRatingRulesMain', 'OnAfterAddRatingRule');
		RegisterModuleDependences('main', 'OnAfterUpdateRatingRule', 'main', 'CRatingRulesMain', 'OnAfterUpdateRatingRule');
		RegisterModuleDependences('main', 'OnGetRatingRuleObjects', 'main', 'CRatingRulesMain', 'OnGetRatingRuleObjects');
		RegisterModuleDependences('main', 'OnGetRatingRuleConfigs', 'main', 'CRatingRulesMain', 'OnGetRatingRuleConfigs');
		RegisterModuleDependences('main', 'OnAfterUserAdd', 'main', 'CRatings', 'OnAfterUserRegister');
		RegisterModuleDependences('main', 'OnUserDelete', 'main', 'CRatings', 'OnUserDelete');
		RegisterModuleDependences('main', 'OnAfterGroupAdd', 'main', 'CGroupAuthProvider', 'OnAfterGroupAdd');
		RegisterModuleDependences('main', 'OnBeforeGroupUpdate', 'main', 'CGroupAuthProvider', 'OnBeforeGroupUpdate');
		RegisterModuleDependences('main', 'OnBeforeGroupDelete', 'main', 'CGroupAuthProvider', 'OnBeforeGroupDelete');
		RegisterModuleDependences('main', 'OnAfterSetUserGroup', 'main', 'CGroupAuthProvider', 'OnAfterSetUserGroup');
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "main", "CEventMain", "GetAuditTypes");
		RegisterModuleDependences("main", "OnEventLogGetAuditHandlers", "main", "CEventMain", "MakeMainObject");
		RegisterModuleDependences("perfmon", "OnGetTableSchema", "main", "CTableSchema", "OnGetTableSchema");
		RegisterModuleDependences("sender", "OnConnectorList", "main", "\\Bitrix\\Main\\SenderEventHandler", "onConnectorListUser");
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeString", "GetUserTypeDescription", 110);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeInteger", "GetUserTypeDescription", 120);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeDouble", "GetUserTypeDescription", 130);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeDateTime", "GetUserTypeDescription", 140);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeDate", "GetUserTypeDescription", 145);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeBoolean", "GetUserTypeDescription", 150);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeUrl", "GetUserTypeDescription", 155);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeFile", "GetUserTypeDescription", 160);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeEnum", "GetUserTypeDescription", 170);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeIBlockSection", "GetUserTypeDescription", 180);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeIBlockElement", "GetUserTypeDescription", 190);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "CUserTypeStringFormatted", "GetUserTypeDescription", 200);
		RegisterModuleDependences("main", "OnUserTypeBuildList", "main", "\\Bitrix\\Main\\UrlPreview\\UrlPreviewUserType", "getUserTypeDescription", 210);
		RegisterModuleDependences("main", "OnBeforeEndBufferContent", "main", "\\Bitrix\\Main\\Analytics\\Counter", "onBeforeEndBufferContent");
		RegisterModuleDependences("main", "OnBeforeRestartBuffer", "main", "\\Bitrix\\Main\\Analytics\\Counter", "onBeforeRestartBuffer");
		RegisterModuleDependences("main", "OnFileDelete", "main", "\\Bitrix\\Main\\UI\\Viewer\\FilePreviewTable", "onFileDelete");
		RegisterModuleDependences("socialnetwork", "OnSocNetLogDelete", "main", "CUserCounter", "OnSocNetLogDelete");
		RegisterModuleDependences("socialnetwork", "OnSocNetLogCommentDelete", "main", "CUserCounter", "OnSocNetLogCommentDelete");
		RegisterModuleDependences("main", "OnAdminInformerInsertItems", "main", "CMpNotifications", "OnAdminInformerInsertItemsHandlerMP");
		RegisterModuleDependences("rest", "OnRestServiceBuildDescription", "main", '\Bitrix\Main\Rest\Handlers', "onRestServiceBuildDescription");
		RegisterModuleDependences("main", "OnBeforeUserTypeAdd", "main", '\Bitrix\Main\UserField\Internal\UserFieldHelper', "OnBeforeUserTypeAdd");
		RegisterModuleDependences("main", "OnAfterUserTypeAdd", "main", '\Bitrix\Main\UserField\Internal\UserFieldHelper', "onAfterUserTypeAdd");
		RegisterModuleDependences("main", "OnBeforeUserTypeDelete", "main", '\Bitrix\Main\UserField\Internal\UserFieldHelper', "OnBeforeUserTypeDelete");
		RegisterModuleDependences("main", "OnAuthProvidersBuildList", "main", "\\Bitrix\\Main\\Access\\Auth\\AccessAuthProvider", "getProviders");
		RegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", "main", "\\Bitrix\\Main\\Access\\Auth\\AccessEventHandler", "onBeforeIBlockSectionUpdate");
		RegisterModuleDependences("iblock", "OnBeforeIBlockSectionAdd", "main", "\\Bitrix\\Main\\Access\\Auth\\AccessEventHandler", "onBeforeIBlockSectionAdd");
		RegisterModuleDependences("iblock", "OnBeforeIBlockSectionDelete", "main", "\\Bitrix\\Main\\Access\\Auth\\AccessEventHandler", "onBeforeIBlockSectionDelete");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler("sale", "OnSaleBasketItemSaved", "main", "\\Bitrix\\Main\\Analytics\\Catalog", "catchCatalogBasket");
		$eventManager->registerEventHandler("sale", "OnSaleOrderSaved", "main", "\\Bitrix\\Main\\Analytics\\Catalog", "catchCatalogOrder");
		$eventManager->registerEventHandler("sale", "OnSaleOrderPaid", "main", "\\Bitrix\\Main\\Analytics\\Catalog", "catchCatalogOrderPayment");
		$eventManager->registerEventHandlerCompatible("sale", "onBuildDiscountConditionInterfaceControls", "main", "\\Bitrix\\Main\\Discount\\UserConditionControl", "onBuildDiscountConditionInterfaceControls", 1000);
		$eventManager->registerEventHandler('main', 'OnBeforePhpMail', 'main', '\Bitrix\Main\Mail\Sender', 'applyCustomSmtp');
		$eventManager->registerEventHandler('main', 'OnBuildFilterFactoryMethods', 'main', '\Bitrix\Main\Filter\FactoryMain', 'onBuildFilterFactoryMethods');
		$eventManager->registerEventHandler('main', 'onGetUserFieldValues', 'main', '\Bitrix\Main\UserField\Internal\UserFieldHelper', 'onGetUserFieldValues');
		$eventManager->registerEventHandler('main', 'onUpdateUserFieldValues', 'main', '\Bitrix\Main\UserField\Internal\UserFieldHelper', 'onUpdateUserFieldValues');
		$eventManager->registerEventHandler('main', 'onDeleteUserFieldValues', 'main', '\Bitrix\Main\UserField\Internal\UserFieldHelper', 'onDeleteUserFieldValues');
		$eventManager->registerEventHandler('main', 'OnAfterUserTypeAdd', 'main', '\Bitrix\Main\ORM\Entity', 'onUserTypeChange');
		$eventManager->registerEventHandler('main', 'OnAfterUserTypeUpdate', 'main', '\Bitrix\Main\ORM\Entity', 'onUserTypeChange');
		$eventManager->registerEventHandler('main', 'OnAfterUserTypeDelete', 'main', '\Bitrix\Main\ORM\Entity', 'onUserTypeChange');

		if (LANGUAGE_ID == "ru")
		{
			COption::SetOptionString("main", "~new_license18_0_sign", "Y");
			COption::SetOptionString("main", "vendor", "1c_bitrix");
			COption::SetOptionString("main", "update_site", "www.1c-bitrix.ru");
		}
		else
		{
			COption::SetOptionString("main", "~new_license17_5_sign", "Y");
			COption::SetOptionString("main", "vendor", "bitrix");
			COption::SetOptionString("main", "update_site", "www.bitrixsoft.com");
		}

		COption::SetOptionString("main", "PARAM_MAX_SITES", "2");
		COption::SetOptionString("main", "PARAM_MAX_USERS", "0");
		COption::SetOptionString("main", "GROUP_DEFAULT_TASK", "1");
		COption::SetOptionString("main", "admin_lid", LANGUAGE_ID);
		COption::SetOptionString("main", "update_site_ns", "Y");
		COption::SetOptionString("main", "update_use_https", "Y");
		COption::SetOptionString("main", "optimize_css_files", "Y");
		COption::SetOptionString("main", "optimize_js_files", "Y");
		COption::SetOptionString("main", "control_file_duplicates", "Y");
		COption::SetOptionString("main", "secure_logout", "Y");

		$nextDay = time() + 86400;
		CAgent::AddAgent("CCaptchaAgent::DeleteOldCaptcha(3600);", "main", "N", 3600);
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bitrix24'))
		{
			CAgent::AddAgent("CSiteCheckerTest::CommonTest();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 03:00:00', $nextDay)), 'FULL'));
		}
		CAgent::AddAgent("CEvent::CleanUpAgent();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 03:10:00', $nextDay)), 'FULL'));
		CAgent::AddAgent("CUser::CleanUpHitAuthAgent();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 03:15:00', $nextDay)), 'FULL'));
		CAgent::AddAgent("CUndo::CleanUpOld();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 03:20:00', $nextDay)), 'FULL'));
		CAgent::AddAgent('CUserCounter::DeleteOld();', "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 03:25:00', $nextDay)), 'FULL'));
		CAgent::AddAgent('\\Bitrix\\Main\\UI\\Viewer\\FilePreviewTable::deleteOldAgent();', 'main', "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 03:30:00', $nextDay)), 'FULL'));
		CAgent::AddAgent("CUser::AuthActionsCleanUpAgent();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 04:15:00', $nextDay)), 'FULL'));
		CAgent::AddAgent("CUser::CleanUpAgent();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 04:20:00', $nextDay)), 'FULL'));
		CAgent::AddAgent("CUser::DeactivateAgent();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 04:25:00', $nextDay)), 'FULL'));
		CAgent::AddAgent("CEventLog::CleanUpAgent();", "main", "N", 86400, "", "Y", ConvertTimeStamp(strtotime(date('Y-m-d 04:30:00', $nextDay)), 'FULL'));

		self::InstallDesktop();

		CSmileGallery::installGallery();

		/* geolocation handlers */
		GeoIp\HandlerTable::add(['SORT' => 100, 'ACTIVE' => 'Y', 'CLASS_NAME' => '\\Bitrix\\Main\\Service\\GeoIp\\GeoIP2']);
		GeoIp\HandlerTable::add(['SORT' => 110, 'ACTIVE' => 'N', 'CLASS_NAME' => '\\Bitrix\\Main\\Service\\GeoIp\\MaxMind']);
		GeoIp\HandlerTable::add(['SORT' => 120, 'ACTIVE' => 'N', 'CLASS_NAME' => '\\Bitrix\\Main\\Service\\GeoIp\\SypexGeo']);

		return true;
	}

	protected function InstallGroups()
	{
		global $APPLICATION;

		$group = new CGroup;

		$arGroups = [
			[
				"~ID" => 1,
				"ACTIVE" => "Y",
				"C_SORT" => 1,
				"NAME" => GetMessage("MAIN_ADMIN_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_ADMIN_GROUP_DESC"),
			],
			[
				"~ID" => 2,
				"ACTIVE" => "Y",
				"C_SORT" => 2,
				"NAME" => GetMessage("MAIN_EVERYONE_GROUP_NAME"),
				"ANONYMOUS" => "Y",
				"DESCRIPTION" => GetMessage("MAIN_EVERYONE_GROUP_DESC"),
			],
			[
				"~ID" => 3,
				"ACTIVE" => "Y",
				"C_SORT" => 3,
				"NAME" => GetMessage("MAIN_VOTE_RATING_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_VOTE_RATING_GROUP_DESC"),
				"STRING_ID" => "RATING_VOTE",
			],
			[
				"~ID" => 4,
				"ACTIVE" => "Y",
				"C_SORT" => 4,
				"NAME" => GetMessage("MAIN_VOTE_AUTHORITY_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_VOTE_AUTHORITY_GROUP_DESC"),
				"STRING_ID" => "RATING_VOTE_AUTHORITY",
			],
		];

		foreach ($arGroups as $arGroup)
		{
			$rsGroup = CGroup::GetByID($arGroup["~ID"]);
			if ($rsGroup->Fetch())
			{
				continue;
			}

			$success = (bool)$group->Add($arGroup);
			if (!$success)
			{
				$APPLICATION->ThrowException($group->LAST_ERROR);
				return false;
			}
		}

		return true;
	}

	protected function InstallLanguages()
	{
		global $APPLICATION;

		$addResult = CultureTable::add([
			"NAME" => LANGUAGE_ID,
			"CODE" => LANGUAGE_ID,
			"FORMAT_DATE" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_DATE"),
			"FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_DATETIME"),
			"FORMAT_NAME" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_NAME"),
			"WEEK_START" => (LANGUAGE_ID == 'en' ? 0 : 1),
			"CHARSET" => "UTF-8",
			"SHORT_DATE_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_SHORT_DATE_FORMAT"),
			"MEDIUM_DATE_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_MEDIUM_DATE_FORMAT"),
			"LONG_DATE_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_LONG_DATE_FORMAT"),
			"FULL_DATE_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_FULL_DATE_FORMAT"),
			"DAY_MONTH_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_DAY_MONTH_FORMAT"),
			"DAY_SHORT_MONTH_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_DAY_SHORT_MONTH_FORMAT"),
			"DAY_OF_WEEK_MONTH_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_DAY_OF_WEEK_MONTH_FORMAT"),
			"SHORT_DAY_OF_WEEK_MONTH_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_SHORT_DAY_OF_WEEK_MONTH_FORMAT"),
			"SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT"),
			"SHORT_TIME_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_SHORT_TIME_FORMAT"),
			"LONG_TIME_FORMAT" => GetMessage("MAIN_DEFAULT_LANGUAGE_LONG_TIME_FORMAT"),
			"AM_VALUE" => GetMessage("MAIN_DEFAULT_LANGUAGE_AM_VALUE"),
			"PM_VALUE" => GetMessage("MAIN_DEFAULT_LANGUAGE_PM_VALUE"),
			"NUMBER_THOUSANDS_SEPARATOR" => GetMessage("MAIN_DEFAULT_LANGUAGE_NUMBER_THOUSANDS_SEPARATOR"),
			"NUMBER_DECIMAL_SEPARATOR" => GetMessage("MAIN_DEFAULT_LANGUAGE_NUMBER_DECIMAL_SEPARATOR"),
			"NUMBER_DECIMALS" => 2,
		]);
		$cultureId = $addResult->getId();

		$arLanguages = [
			[
				"LID" => LANGUAGE_ID,
				"CODE" => GetMessage("MAIN_DEFAULT_LANGUAGE_CODE"),
				"ACTIVE" => "Y",
				"SORT" => 1,
				"DEF" => "Y",
				"NAME" => GetMessage("MAIN_DEFAULT_LANGUAGE_NAME"),
				"CULTURE_ID" => $cultureId,
			],
		];

		if (LANGUAGE_ID <> "en" && file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lang/en/install/index.php'))
		{
			$addResult = CultureTable::add([
				"NAME" => "en",
				"CODE" => "en",
				"FORMAT_DATE" => "MM/DD/YYYY",
				"FORMAT_DATETIME" => "MM/DD/YYYY H:MI:SS T",
				"FORMAT_NAME" => "#NAME# #LAST_NAME#",
				"WEEK_START" => 0,
				"CHARSET" => "UTF-8",
				"SHORT_DATE_FORMAT" => "n/j/Y",
				"MEDIUM_DATE_FORMAT" => "M j, Y",
				"LONG_DATE_FORMAT" => "F j, Y",
				"FULL_DATE_FORMAT" => "l, F j, Y",
				"DAY_MONTH_FORMAT" => "F j",
				"DAY_SHORT_MONTH_FORMAT" => "M j",
				"DAY_OF_WEEK_MONTH_FORMAT" => "l, F j",
				"SHORT_DAY_OF_WEEK_MONTH_FORMAT" => "D, F j",
				"SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT" => "D, M j",
				"SHORT_TIME_FORMAT" => "g:i a",
				"LONG_TIME_FORMAT" => "g:i:s a",
				"AM_VALUE" => "am",
				"PM_VALUE" => "pm",
				"NUMBER_THOUSANDS_SEPARATOR" => ",",
				"NUMBER_DECIMAL_SEPARATOR" => ".",
				"NUMBER_DECIMALS" => "2",
			]);
			$cultureId = $addResult->getId();

			$arLanguages[] = [
				"LID" => "en",
				"CODE" => "en",
				"ACTIVE" => "Y",
				"SORT" => 2,
				"DEF" => "N",
				"NAME" => "English",
				"CULTURE_ID" => $cultureId,
			];
		}

		if (LANGUAGE_ID <> "de" && file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lang/de/install/index.php'))
		{
			$addResult = CultureTable::add([
				"NAME" => "de",
				"CODE" => "de",
				"FORMAT_DATE" => "DD.MM.YYYY",
				"FORMAT_DATETIME" => "DD.MM.YYYY HH:MI:SS",
				"FORMAT_NAME" => "#NAME# #LAST_NAME#",
				"WEEK_START" => 1,
				"CHARSET" => "UTF-8",
				"SHORT_DATE_FORMAT" => "d.m.Y",
				"MEDIUM_DATE_FORMAT" => "j. M Y",
				"LONG_DATE_FORMAT" => "j. F Y",
				"FULL_DATE_FORMAT" => "l, j. F  Y",
				"DAY_MONTH_FORMAT" => "j. F",
				"DAY_SHORT_MONTH_FORMAT" => "j. M",
				"DAY_OF_WEEK_MONTH_FORMAT" => "l, j. F",
				"SHORT_DAY_OF_WEEK_MONTH_FORMAT" => "D, j. F",
				"SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT" => "D, j. M",
				"SHORT_TIME_FORMAT" => "H:i",
				"LONG_TIME_FORMAT" => "H:i:s",
				"AM_VALUE" => "am",
				"PM_VALUE" => "pm",
				"NUMBER_THOUSANDS_SEPARATOR" => ".",
				"NUMBER_DECIMAL_SEPARATOR" => ",",
				"NUMBER_DECIMALS" => "2",
			]);
			$cultureId = $addResult->getId();

			$arLanguages[] = [
				"LID" => "de",
				"CODE" => "de",
				"ACTIVE" => "Y",
				"SORT" => 3,
				"DEF" => "N",
				"NAME" => "German",
				"CULTURE_ID" => $cultureId,
			];
		}

		if (LANGUAGE_ID <> "ru" && file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lang/ru/install/index.php'))
		{
			$addResult = CultureTable::add([
				"NAME" => "ru",
				"CODE" => "ru",
				"FORMAT_DATE" => "DD.MM.YYYY",
				"FORMAT_DATETIME" => "DD.MM.YYYY HH:MI:SS",
				"FORMAT_NAME" => "#NAME# #LAST_NAME#",
				"WEEK_START" => 1,
				"CHARSET" => "UTF-8",
				"SHORT_DATE_FORMAT" => "d.m.Y",
				"MEDIUM_DATE_FORMAT" => "j M Y",
				"LONG_DATE_FORMAT" => "j F Y",
				"FULL_DATE_FORMAT" => "l, j F Y",
				"DAY_MONTH_FORMAT" => "j F",
				"DAY_SHORT_MONTH_FORMAT" => "j M",
				"DAY_OF_WEEK_MONTH_FORMAT" => "l, j F",
				"SHORT_DAY_OF_WEEK_MONTH_FORMAT" => "D, j F",
				"SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT" => "D, j M",
				"SHORT_TIME_FORMAT" => "H:i",
				"LONG_TIME_FORMAT" => "H:i:s",
				"AM_VALUE" => "am",
				"PM_VALUE" => "pm",
				"NUMBER_THOUSANDS_SEPARATOR" => " ",
				"NUMBER_DECIMAL_SEPARATOR" => ",",
				"NUMBER_DECIMALS" => "2",
			]);
			$cultureId = $addResult->getId();

			$arLanguages[] = [
				"LID" => "ru",
				"CODE" => "ru",
				"ACTIVE" => "Y",
				"SORT" => 3,
				"DEF" => "N",
				"NAME" => "Russian",
				"CULTURE_ID" => $cultureId,
			];
		}

		$lang = new CLanguage;
		foreach ($arLanguages as $arLanguage)
		{
			$rsLang = CLanguage::GetByID($arLanguage["LID"]);
			if ($rsLang->Fetch())
			{
				continue;
			}

			$success = (bool)$lang->Add($arLanguage);
			if (!$success)
			{
				$APPLICATION->ThrowException($lang->LAST_ERROR);
				return false;
			}
		}

		return true;
	}

	protected function InstallSites()
	{
		global $APPLICATION;

		$culture = CultureTable::getRow(['filter' => [
			"=FORMAT_DATE" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATE"),
			"=FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATETIME"),
			"=FORMAT_NAME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_NAME"),
			"=CHARSET" => "UTF-8",
		]]);

		if ($culture)
		{
			$cultureId = $culture["ID"];
		}
		else
		{
			$addResult = CultureTable::add([
				"NAME" => "s1",
				"CODE" => "s1",
				"FORMAT_DATE" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATE"),
				"FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATETIME"),
				"FORMAT_NAME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_NAME"),
				"CHARSET" => "UTF-8",
			]);
			$cultureId = $addResult->getId();
		}

		$arSite = [
			"LID" => "s1",
			"ACTIVE" => "Y",
			"SORT" => 1,
			"DEF" => "Y",
			"NAME" => GetMessage("MAIN_DEFAULT_SITE_NAME"),
			"DIR" => "/",
			"LANGUAGE_ID" => LANGUAGE_ID,
			"CULTURE_ID" => $cultureId,
		];

		$rsSites = CSite::GetByID($arSite["LID"]);
		if (!$rsSites->Fetch())
		{
			$site = new CSite;
			$success = (bool)$site->Add($arSite);
			if (!$success)
			{
				$APPLICATION->ThrowException($site->LAST_ERROR);
				return false;
			}
		}

		return true;
	}

	function GetModuleTasks()
	{
		return [
			'' => [
				"OPERATIONS" => [
					"edit_php",
				],
			],
			'main_denied' => [
				"LETTER" => "D",
				"BINDING" => "module",
				"OPERATIONS" => [
				],
			],
			'main_change_profile' => [
				"LETTER" => "P",
				"BINDING" => "module",
				"OPERATIONS" => [
					"view_own_profile",
					"edit_own_profile",
				],
			],
			'main_view_all_settings' => [
				"LETTER" => "R",
				"BINDING" => "module",
				"OPERATIONS" => [
					"view_own_profile",
					"view_all_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
				],
			],
			'main_view_all_settings_change_profile' => [
				"LETTER" => "T",
				"BINDING" => "module",
				"OPERATIONS" => [
					"view_own_profile",
					"view_all_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
					"edit_own_profile",
				],
			],
			'main_edit_subordinate_users' => [
				"LETTER" => "V",
				"BINDING" => "module",
				"OPERATIONS" => [
					"view_own_profile",
					"view_subordinate_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
					"edit_own_profile",
					"edit_subordinate_users",
				],
			],
			'main_full_access' => [
				"LETTER" => "W",
				"BINDING" => "module",
				"OPERATIONS" => [
					"view_own_profile",
					"view_all_users",
					"view_groups",
					"view_tasks",
					"view_other_settings",
					"edit_own_profile",
					"edit_all_users",
					"edit_groups",
					"edit_tasks",
					"edit_other_settings",
					"cache_control",
					"lpa_template_edit",
					"view_event_log",
					"edit_ratings",
					"manage_short_uri",
				],
			],
			'fm_folder_access_denied' => [
				"LETTER" => "D",
				"BINDING" => "file",
				"OPERATIONS" => [
				],
			],
			'fm_folder_access_read' => [
				"LETTER" => "R",
				"BINDING" => "file",
				"OPERATIONS" => [
					"fm_view_permission",
					"fm_view_file",
					"fm_view_listing",
				],
			],
			'fm_folder_access_write' => [
				"LETTER" => "W",
				"BINDING" => "file",
				"OPERATIONS" => [
					"fm_view_permission",
					"fm_edit_existent_folder",
					"fm_create_new_file",
					"fm_edit_existent_file",
					"fm_create_new_folder",
					"fm_delete_file",
					"fm_delete_folder",
					"fm_view_file",
					"fm_view_listing",
					"fm_edit_in_workflow",
					"fm_rename_file",
					"fm_rename_folder",
					"fm_upload_file",
					"fm_add_to_menu",
					"fm_download_file",
					"fm_lpa",
				],
			],
			'fm_folder_access_full' => [
				"LETTER" => "X",
				"BINDING" => "file",
				"OPERATIONS" => [
					"fm_view_permission",
					"fm_edit_permission",
					"fm_edit_existent_folder",
					"fm_create_new_file",
					"fm_edit_existent_file",
					"fm_create_new_folder",
					"fm_delete_file",
					"fm_delete_folder",
					"fm_view_file",
					"fm_view_listing",
					"fm_edit_in_workflow",
					"fm_rename_file",
					"fm_rename_folder",
					"fm_upload_file",
					"fm_add_to_menu",
					"fm_download_file",
					"fm_lpa",
				],
			],
			'fm_folder_access_workflow' => [
				"LETTER" => "U",
				"BINDING" => "file",
				"OPERATIONS" => [
					"fm_view_permission",
					"fm_edit_existent_file",
					"fm_view_file",
					"fm_view_listing",
					"fm_edit_in_workflow",
				],
			],
		];
	}

	private static function InstallRatings()
	{
		global $DB;

		// add  ratings
		$arFields = [
			'ACTIVE' => 'N',
			'NAME' => GetMessage("MAIN_RATING_NAME"),
			'ENTITY_ID' => 'USER',
			'CALCULATION_METHOD' => 'SUM',
			'~CREATED' => $DB->GetNowFunction(),
			'CALCULATED' => 'N',
			'POSITION' => 'Y',
			'AUTHORITY' => 'N',
			'CONFIGS' => 'a:3:{s:4:"MAIN";a:2:{s:4:"VOTE";a:1:{s:4:"USER";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:5:"BONUS";a:2:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";}}}s:5:"FORUM";a:2:{s:4:"VOTE";a:2:{s:5:"TOPIC";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.5";s:5:"LIMIT";s:2:"30";}s:4:"POST";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:9:{s:6:"ACTIVE";s:1:"Y";s:16:"TODAY_TOPIC_COEF";s:3:"0.4";s:15:"WEEK_TOPIC_COEF";s:3:"0.2";s:16:"MONTH_TOPIC_COEF";s:3:"0.1";s:14:"ALL_TOPIC_COEF";s:1:"0";s:15:"TODAY_POST_COEF";s:3:"0.2";s:14:"WEEK_POST_COEF";s:3:"0.1";s:15:"MONTH_POST_COEF";s:4:"0.05";s:13:"ALL_POST_COEF";s:1:"0";}}}s:4:"BLOG";a:2:{s:4:"VOTE";a:2:{s:4:"POST";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.5";s:5:"LIMIT";s:2:"30";}s:7:"COMMENT";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:3:"0.1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:9:{s:6:"ACTIVE";s:1:"Y";s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";s:18:"TODAY_COMMENT_COEF";s:3:"0.2";s:17:"WEEK_COMMENT_COEF";s:3:"0.1";s:18:"MONTH_COMMENT_COEF";s:4:"0.05";s:16:"ALL_COMMENT_COEF";s:1:"0";}}}}',
		];
		$DB->Add("b_rating", $arFields, ["CONFIGS"]);

		// add  authority ratings
		$arFields = [
			'ACTIVE' => 'N',
			'NAME' => GetMessage("MAIN_RATING_AUTHORITY_NAME"),
			'ENTITY_ID' => 'USER',
			'CALCULATION_METHOD' => 'SUM',
			'~CREATED' => $DB->GetNowFunction(),
			'CALCULATED' => 'N',
			'POSITION' => 'Y',
			'AUTHORITY' => 'Y',
			'CONFIGS' => 'a:3:{s:4:"MAIN";a:2:{s:4:"VOTE";a:1:{s:4:"USER";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:1:"0";}}s:6:"RATING";a:1:{s:5:"BONUS";a:2:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";}}}s:5:"FORUM";a:2:{s:4:"VOTE";a:2:{s:5:"TOPIC";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:4:"POST";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:8:{s:16:"TODAY_TOPIC_COEF";s:2:"20";s:15:"WEEK_TOPIC_COEF";s:2:"10";s:16:"MONTH_TOPIC_COEF";s:1:"5";s:14:"ALL_TOPIC_COEF";s:1:"0";s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";}}}s:4:"BLOG";a:2:{s:4:"VOTE";a:2:{s:4:"POST";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:7:"COMMENT";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:8:{s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";s:18:"TODAY_COMMENT_COEF";s:3:"0.2";s:17:"WEEK_COMMENT_COEF";s:3:"0.1";s:18:"MONTH_COMMENT_COEF";s:4:"0.05";s:16:"ALL_COMMENT_COEF";s:1:"0";}}}}',
		];
		$ratingId = $DB->Add("b_rating", $arFields, ["CONFIGS"]);
		COption::SetOptionString("main", "rating_authority_rating", $ratingId);

		// set default rating vote group config
		$rsGroup = $DB->Query("SELECT * FROM b_group WHERE STRING_ID='RATING_VOTE'", true);
		if ($arGroup = $rsGroup->Fetch())
		{
			$arVoteGroup[] = [
				'GROUP_ID' => 1,
				'TYPE' => "'R'",
			];
			$arVoteGroup[] = [
				'GROUP_ID' => $arGroup['ID'],
				'TYPE' => "'R'",
			];
			foreach ($arVoteGroup as $arField)
			{
				$DB->Insert("b_rating_vote_group", $arField);
			}

			$arFields = [
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_ADD_GROUP_RATING_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => [
					'AUTHORITY' => [
						'RATING_CONDITION' => 1,
						'RATING_VALUE' => 1,
					],
				],
				'ACTION_NAME' => 'ADD_TO_GROUP',
				'ACTION_CONFIG' => [
					'ADD_TO_GROUP' => [
						'GROUP_ID' => $arGroup['ID'],
					],
				],
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'addToGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'addToGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			];
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, ["ACTION_CONFIG", "CONDITION_CONFIG"]);

			$arFields = [
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_REM_GROUP_RATING_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => [
					'AUTHORITY' => [
						'RATING_CONDITION' => 2,
						'RATING_VALUE' => 1,
					],
				],
				'ACTION_NAME' => 'REMOVE_FROM_GROUP',
				'ACTION_CONFIG' => [
					'REMOVE_FROM_GROUP' => [
						'GROUP_ID' => $arGroup['ID'],
					],
				],
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'removeFromGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'removeFromGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			];
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, ["ACTION_CONFIG", "CONDITION_CONFIG"]);

			COption::SetOptionString("main", "rating_assign_rating_group_add", 1);
			COption::SetOptionString("main", "rating_assign_rating_group_delete", 1);
			COption::SetOptionString("main", "rating_assign_rating_group", $arGroup['ID']);
		}
		$rsGroup = $DB->Query("SELECT * FROM b_group WHERE STRING_ID='RATING_VOTE_AUTHORITY'", true);
		if ($arGroup = $rsGroup->Fetch())
		{
			$arVoteGroup[] = [
				'GROUP_ID' => 1,
				'TYPE' => "'A'",
			];
			$arVoteGroup[] = [
				'GROUP_ID' => $arGroup['ID'],
				'TYPE' => "'A'",
			];
			foreach ($arVoteGroup as $arField)
			{
				$DB->Insert("b_rating_vote_group", $arField);
			}

			$arFields = [
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_ADD_GROUP_AUTHORITY_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => [
					'AUTHORITY' => [
						'RATING_CONDITION' => 1,
						'RATING_VALUE' => 2,
					],
				],
				'ACTION_NAME' => 'ADD_TO_GROUP',
				'ACTION_CONFIG' => [
					'ADD_TO_GROUP' => [
						'GROUP_ID' => $arGroup['ID'],
					],
				],
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'addToGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'addToGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			];
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, ["ACTION_CONFIG", "CONDITION_CONFIG"]);

			$arFields = [
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_REM_GROUP_AUTHORITY_NAME"),
				'ENTITY_TYPE_ID' => 'USER',
				'CONDITION_NAME' => 'AUTHORITY',
				'CONDITION_CLASS' => 'CRatingRulesMain',
				'CONDITION_METHOD' => 'ratingCheck',
				'CONDITION_CONFIG' => [
					'AUTHORITY' => [
						'RATING_CONDITION' => 2,
						'RATING_VALUE' => 2,
					],
				],
				'ACTION_NAME' => 'REMOVE_FROM_GROUP',
				'ACTION_CONFIG' => [
					'REMOVE_FROM_GROUP' => [
						'GROUP_ID' => $arGroup['ID'],
					],
				],
				'ACTIVATE' => 'N',
				'ACTIVATE_CLASS' => 'CRatingRulesMain',
				'ACTIVATE_METHOD' => 'removeFromGroup',
				'DEACTIVATE' => 'N',
				'DEACTIVATE_CLASS' => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'removeFromGroup',
				"~CREATED" => $DB->GetNowFunction(),
				"~LAST_MODIFIED" => $DB->GetNowFunction(),
			];
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, ["ACTION_CONFIG", "CONDITION_CONFIG"]);

			COption::SetOptionString("main", "rating_assign_authority_group_add", 2);
			COption::SetOptionString("main", "rating_assign_authority_group_delete", 2);
			COption::SetOptionString("main", "rating_assign_authority_group", $arGroup['ID']);
		}

		// auto authority vote
		$arFields = [
			'ACTIVE' => 'Y',
			'NAME' => GetMessage("MAIN_RULE_AUTO_AUTHORITY_VOTE_NAME"),
			'ENTITY_TYPE_ID' => 'USER',
			'CONDITION_NAME' => 'VOTE',
			'CONDITION_CLASS' => 'CRatingRulesMain',
			'CONDITION_METHOD' => 'voteCheck',
			'CONDITION_CONFIG' => [
				'VOTE' => [
					'VOTE_LIMIT' => 90,
					'VOTE_RESULT' => 10,
					'VOTE_FORUM_TOPIC' => 0.5,
					'VOTE_FORUM_POST' => 0.1,
					'VOTE_BLOG_POST' => 0.5,
					'VOTE_BLOG_COMMENT' => 0.1,
				],
			],
			'ACTION_NAME' => 'empty',
			'ACTION_CONFIG' => [
			],
			'ACTIVATE' => 'N',
			'ACTIVATE_CLASS' => 'empty',
			'ACTIVATE_METHOD' => 'empty',
			'DEACTIVATE' => 'N',
			'DEACTIVATE_CLASS' => 'empty ',
			'DEACTIVATE_METHOD' => 'empty',
			"~CREATED" => $DB->GetNowFunction(),
			"~LAST_MODIFIED" => $DB->GetNowFunction(),
		];
		$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
		$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
		$DB->Add("b_rating_rule", $arFields, ["ACTION_CONFIG", "CONDITION_CONFIG"]);

		// rating default config
		COption::SetOptionString("main", "rating_community_size", 1);
		COption::SetOptionString("main", "rating_community_authority", round(3 * 10, 4));
		COption::SetOptionString("main", "rating_vote_weight", 10);
		COption::SetOptionString("main", "rating_normalization_type", "auto");
		COption::SetOptionString("main", "rating_normalization", 10);
		COption::SetOptionString("main", "rating_count_vote", 10);
		COption::SetOptionString("main", "rating_authority_weight_formula", 'Y');
		COption::SetOptionString("main", "rating_community_last_visit", 90);
		COption::SetOptionString("main", "rating_text_like_y", GetMessage("MAIN_RATING_TEXT_LIKE_Y"));
		COption::SetOptionString("main", "rating_text_like_n", GetMessage("MAIN_RATING_TEXT_LIKE_N"));
		COption::SetOptionString("main", "rating_text_like_d", GetMessage("MAIN_RATING_TEXT_LIKE_D"));
		COption::SetOptionString("main", "rating_assign_type", 'auto');
		COption::SetOptionString("main", "rating_vote_type", 'like');
		COption::SetOptionString("main", "rating_self_vote", 'Y');
		COption::SetOptionString("main", "rating_vote_show", 'Y');
		COption::SetOptionString("main", "rating_vote_template", 'like');
		COption::SetOptionString("main", "rating_start_authority", 3);
	}

	private static function InstallDesktop()
	{
		$info_table = '<table class="bx-gadgets-info-site-table" cellspacing="0">';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">' . GetMessage("MAIN_DESKTOP_CREATEDBY_KEY") . ':</td>';
		$info_table .= '	<td>' . GetMessage("MAIN_DESKTOP_CREATEDBY_VALUE") . '</td>';
		$info_table .= '	<td class="bx-gadgets-info-site-logo" rowspan="5"><img src="' . '/bitrix/components/bitrix/desktop/templates/admin/images/site_logo.png' . '" alt=""></td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">' . GetMessage("MAIN_DESKTOP_URL_KEY") . ':</td>';
		$info_table .= '	<td>' . GetMessage("MAIN_DESKTOP_URL_VALUE") . '</td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">' . GetMessage("MAIN_DESKTOP_PRODUCTION_KEY") . ':</td>';
		$info_table .= '	<td>' . GetMessage("MAIN_DESKTOP_PRODUCTION_VALUE") . '</td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">' . GetMessage("MAIN_DESKTOP_RESPONSIBLE_KEY") . ':</td>';
		$info_table .= '	<td>' . GetMessage("MAIN_DESKTOP_RESPONSIBLE_VALUE") . '</td>';
		$info_table .= '</tr>';
		$info_table .= '<tr>';
		$info_table .= '	<td class="bx-gadget-gray">' . GetMessage("MAIN_DESKTOP_EMAIL_KEY") . ':</td>';
		$info_table .= '	<td>' . GetMessage("MAIN_DESKTOP_EMAIL_VALUE") . '</td>';
		$info_table .= '</tr>';
		$info_table .= '</table>';

		switch (LANGUAGE_ID)
		{
			case "ru":
				$rss_url = "https://www.1c-bitrix.ru/about/life/news/rss/";
				break;
			case "de":
				$rss_url = "https://www.bitrix.de/company/news/rss/";
				break;
			default:
				$rss_url = "https://www.bitrixsoft.com/company/news/rss/";
		}

		if ( // 111
			file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/statistic/install/index.php")
			&& !file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/intranet/install/index.php")
		)
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/install/index.php"))
			{
				$arOptions = [
					[
						"GADGETS" => [
							"ADMIN_ORDERS_GRAPH@111111111" => [
								"COLUMN" => 0,
								"ROW" => 0,
								"HIDE" => "N",
							],
							"ADMIN_ORDERS@111111111" => [
								"COLUMN" => 0,
								"ROW" => 1,
								"HIDE" => "N",
							],
							"ADMIN_STAT@222222222" => [
								"COLUMN" => 0,
								"ROW" => 3,
								"HIDE" => "N",
							],
							"HTML_AREA@444444444" => [
								"COLUMN" => 1,
								"ROW" => 0,
								"HIDE" => "N",
								"USERDATA" => [
									"content" => $info_table,
								],
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE"),
								],
							],
							"ADMIN_SECURITY@555555555" => [
								"COLUMN" => 1,
								"ROW" => 1,
								"HIDE" => "N",
							],
							"ADMIN_SITESPEED@666666777" => [
								"COLUMN" => 1,
								"ROW" => 2,
								"HIDE" => "N",
							],
							"ADMIN_PERFMON@666666666" => [
								"COLUMN" => 1,
								"ROW" => 3,
								"HIDE" => "N",
							],
							"ADMIN_PRODUCTS@111111111" => [
								"COLUMN" => 1,
								"ROW" => 65,
								"HIDE" => "N",
							],
							"ADMIN_INFO@333333333" => [
								"COLUMN" => 1,
								"ROW" => 7,
								"HIDE" => "N",
							],
							"ADMIN_CHECKLIST@777888999" => [
								"COLUMN" => 1,
								"ROW" => 8,
								"HIDE" => "N",
							],
							"RSSREADER@777777777" => [
								"COLUMN" => 1,
								"ROW" => 9,
								"HIDE" => "N",
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
									"CNT" => 10,
									"RSS_URL" => $rss_url,
								],
							],
						],
					],
				];
				if (LANGUAGE_ID == "ru")
				{
					$arOptions[0]["GADGETS"]["ADMIN_MARKETPALCE@22549"] = [
						"COLUMN" => 1,
						"ROW" => 4,
						"HIDE" => "N",
					];
					$arOptions[0]["GADGETS"]["ADMIN_MOBILESHOP@13391"] = [
						"COLUMN" => 1,
						"ROW" => 5,
						"HIDE" => "N",
					];
				}
			}
			else
			{
				$arOptions = [
					[
						"GADGETS" => [
							"ADMIN_STAT@222222222" => [
								"COLUMN" => 0,
								"ROW" => 0,
								"HIDE" => "N",
							],
							"ADMIN_INFO@333333333" => [
								"COLUMN" => 0,
								"ROW" => 1,
								"HIDE" => "N",
							],
							"ADMIN_CHECKLIST@777888999" => [
								"COLUMN" => 0,
								"ROW" => 2,
								"HIDE" => "N",
							],
							"RSSREADER@777777777" => [
								"COLUMN" => 0,
								"ROW" => 3,
								"HIDE" => "N",
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
									"CNT" => 10,
									"RSS_URL" => $rss_url,
								],
							],
							"HTML_AREA@444444444" => [
								"COLUMN" => 1,
								"ROW" => 0,
								"HIDE" => "N",
								"USERDATA" => [
									"content" => $info_table,
								],
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE"),
								],
							],
							"ADMIN_SECURITY@555555555" => [
								"COLUMN" => 1,
								"ROW" => 1,
								"HIDE" => "N",
							],
							"ADMIN_SITESPEED@666666777" => [
								"COLUMN" => 1,
								"ROW" => 2,
								"HIDE" => "N",
							],
							"ADMIN_PERFMON@666666666" => [
								"COLUMN" => 1,
								"ROW" => 3,
								"HIDE" => "N",
							],
						],
					],
				];
				if (LANGUAGE_ID == "ru")
				{
					$arOptions[0]["GADGETS"]["ADMIN_MARKETPALCE@22549"] = [
						"COLUMN" => 1,
						"ROW" => 4,
						"HIDE" => "N",
					];
				}
			}
		}
		elseif (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/intranet/install/index.php")) // 222
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/install/index.php"))
			{
				$arOptions = [
					[
						"GADGETS" => [
							"ADMIN_ORDERS_GRAPH@111111111" => [
								"COLUMN" => 0,
								"ROW" => 0,
								"HIDE" => "N",
							],
							"ADMIN_ORDERS@111111111" => [
								"COLUMN" => 0,
								"ROW" => 1,
								"HIDE" => "N",
							],
							"HTML_AREA@444444444" => [
								"COLUMN" => 1,
								"ROW" => 0,
								"HIDE" => "N",
								"USERDATA" => [
									"content" => $info_table,
								],
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE"),
								],
							],
							"ADMIN_SECURITY@555555555" => [
								"COLUMN" => 1,
								"ROW" => 1,
								"HIDE" => "N",
							],
							"ADMIN_SITESPEED@666666777" => [
								"COLUMN" => 1,
								"ROW" => 2,
								"HIDE" => "N",
							],
							"ADMIN_PERFMON@666666666" => [
								"COLUMN" => 1,
								"ROW" => 3,
								"HIDE" => "N",
							],
							"ADMIN_PRODUCTS@111111111" => [
								"COLUMN" => 1,
								"ROW" => 6,
								"HIDE" => "N",
							],
							"ADMIN_INFO@333333333" => [
								"COLUMN" => 1,
								"ROW" => 7,
								"HIDE" => "N",
							],
							"ADMIN_CHECKLIST@777888999" => [
								"COLUMN" => 1,
								"ROW" => 8,
								"HIDE" => "N",
							],
							"RSSREADER@777777777" => [
								"COLUMN" => 1,
								"ROW" => 9,
								"HIDE" => "N",
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
									"CNT" => 10,
									"RSS_URL" => $rss_url,
								],
							],
						],
					],
				];
				if (LANGUAGE_ID == "ru")
				{
					$arOptions[0]["GADGETS"]["ADMIN_MARKETPALCE@22549"] = [
						"COLUMN" => 1,
						"ROW" => 4,
						"HIDE" => "N",
					];
					$arOptions[0]["GADGETS"]["ADMIN_MOBILESHOP@13391"] = [
						"COLUMN" => 1,
						"ROW" => 5,
						"HIDE" => "N",
					];
				}
			}
			else
			{
				$arOptions = [
					[
						"GADGETS" => [
							"ADMIN_INFO@333333333" => [
								"COLUMN" => 0,
								"ROW" => 0,
								"HIDE" => "N",
							],
							"HTML_AREA@444444444" => [
								"COLUMN" => 0,
								"ROW" => 1,
								"HIDE" => "N",
								"USERDATA" => [
									"content" => $info_table,
								],
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE"),
								],
							],
							"ADMIN_CHECKLIST@777888999" => [
								"COLUMN" => 0,
								"ROW" => 2,
								"HIDE" => "N",
							],
							"RSSREADER@777777777" => [
								"COLUMN" => 1,
								"ROW" => 3,
								"HIDE" => "N",
								"SETTINGS" => [
									"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
									"CNT" => 10,
									"RSS_URL" => $rss_url,
								],
							],
							"ADMIN_SECURITY@555555555" => [
								"COLUMN" => 1,
								"ROW" => 0,
								"HIDE" => "N",
							],
							"ADMIN_SITESPEED@666666777" => [
								"COLUMN" => 1,
								"ROW" => 1,
								"HIDE" => "N",
							],
							"ADMIN_PERFMON@666666666" => [
								"COLUMN" => 1,
								"ROW" => 2,
								"HIDE" => "N",
							],
						],
					],
				];
				if (LANGUAGE_ID == "ru")
				{
					$arOptions[0]["GADGETS"]["ADMIN_MARKETPALCE@22549"] = [
						"COLUMN" => 1,
						"ROW" => 3,
						"HIDE" => "N",
					];
				}
			}
		}
		elseif (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/statistic/install/index.php")) // 333
		{
			$arOptions = [
				[
					"GADGETS" => [
						"ADMIN_SECURITY@555555555" => [
							"COLUMN" => 0,
							"ROW" => 0,
							"HIDE" => "N",
						],
						"ADMIN_PERFMON@666666666" => [
							"COLUMN" => 0,
							"ROW" => 1,
							"HIDE" => "N",
						],
						"ADMIN_STAT@222222222" => [
							"COLUMN" => 0,
							"ROW" => 2,
							"HIDE" => "N",
						],
						"HTML_AREA@444444444" => [
							"COLUMN" => 1,
							"ROW" => 0,
							"HIDE" => "N",
							"USERDATA" => [
								"content" => $info_table,
							],
							"SETTINGS" => [
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE"),
							],
						],
						"ADMIN_INFO@333333333" => [
							"COLUMN" => 1,
							"ROW" => 1,
							"HIDE" => "N",
						],
						"RSSREADER@777777777" => [
							"COLUMN" => 1,
							"ROW" => 3,
							"HIDE" => "N",
							"SETTINGS" => [
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
								"CNT" => 5,
								"RSS_URL" => $rss_url,
							],
						],
						"ADMIN_CHECKLIST@777888999" => [
							"COLUMN" => 1,
							"ROW" => 2,
							"HIDE" => "N",
						],
					],
				],
			];
		}
		else // 444
		{
			$arOptions = [
				[
					"GADGETS" => [
						"ADMIN_INFO@333333333" => [
							"COLUMN" => 0,
							"ROW" => 0,
							"HIDE" => "N",
						],
						"ADMIN_SECURITY@555555555" => [
							"COLUMN" => 0,
							"ROW" => 1,
							"HIDE" => "N",
						],
						"ADMIN_PERFMON@666666666" => [
							"COLUMN" => 0,
							"ROW" => 2,
							"HIDE" => "N",
						],
						"HTML_AREA@444444444" => [
							"COLUMN" => 1,
							"ROW" => 0,
							"HIDE" => "N",
							"USERDATA" => [
								"content" => $info_table,
							],
							"SETTINGS" => [
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_INFO_TITLE"),
							],
						],
						"RSSREADER@777777777" => [
							"COLUMN" => 1,
							"ROW" => 2,
							"HIDE" => "N",
							"SETTINGS" => [
								"TITLE_STD" => GetMessage("MAIN_DESKTOP_RSS_TITLE"),
								"CNT" => 5,
								"RSS_URL" => $rss_url,
							],
						],
						"ADMIN_CHECKLIST@777888999" => [
							"COLUMN" => 1,
							"ROW" => 1,
							"HIDE" => "N",
						],
					],
				],
			];
		}

		CUserOptions::SetOption('intranet', "~gadgets_admin_index", $arOptions, true);
	}

	function UnInstallDB()
	{
		return true;
	}

	function InstallEvents()
	{
		$languages = [];
		$langs = CLanguage::GetList();
		while ($language = $langs->Fetch())
		{
			$languages[] = $language;
		}

		$arEventTypes = [];
		foreach ($languages as $language)
		{
			$lid = $language["LID"];

			IncludeModuleLangFile(__FILE__, $lid);

			$arEventTypes[] = [
				"LID" => $lid,
				"EVENT_NAME" => "NEW_USER",
				"NAME" => GetMessage("MAIN_NEW_USER_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_NEW_USER_TYPE_DESC"),
				"SORT" => 1,
			];
			$arEventTypes[] = [
				"LID" => $lid,
				"EVENT_NAME" => "USER_INFO",
				"NAME" => GetMessage("MAIN_USER_INFO_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 2,
			];
			$arEventTypes[] = [
				"LID" => $lid,
				"EVENT_NAME" => "NEW_USER_CONFIRM",
				"NAME" => GetMessage("MAIN_NEW_USER_CONFIRM_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_NEW_USER_CONFIRM_TYPE_DESC"),
				"SORT" => 3,
			];
			$arEventTypes[] = [
				"LID" => $lid,
				"EVENT_NAME" => "USER_PASS_REQUEST",
				"NAME" => GetMessage("MAIN_USER_PASS_REQUEST_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 4,
			];
			$arEventTypes[] = [
				"LID" => $lid,
				"EVENT_NAME" => "USER_PASS_CHANGED",
				"NAME" => GetMessage("MAIN_USER_PASS_CHANGED_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 5,
			];
			$arEventTypes[] = [
				"LID" => $lid,
				"EVENT_NAME" => "USER_INVITE",
				"NAME" => GetMessage("MAIN_USER_INVITE_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INVITE_TYPE_DESC"),
				"SORT" => 6,
			];
			$arEventTypes[] = [
				"LID" => $lid,
				"EVENT_NAME" => "FEEDBACK_FORM",
				"NAME" => GetMessage("MF_EVENT_NAME"),
				"DESCRIPTION" => GetMessage("MF_EVENT_DESCRIPTION"),
				"SORT" => 7,
			];
			$arEventTypes[] = [
				'LID' => $lid,
				'EVENT_NAME' => 'MAIN_MAIL_CONFIRM_CODE',
				'NAME' => getMessage('MAIN_MAIL_CONFIRM_EVENT_TYPE_NAME'),
				'DESCRIPTION' => getMessage('MAIN_MAIL_CONFIRM_EVENT_TYPE_DESC'),
				'SORT' => 8,
			];
			$arEventTypes[] = [
				'LID' => $lid,
				'EVENT_NAME' => 'EVENT_LOG_NOTIFICATION',
				'NAME' => getMessage('MAIN_INSTALL_EVENT_TYPE_NOTIFICATION'),
				'DESCRIPTION' => getMessage('MAIN_INSTALL_EVENT_TYPE_NOTIFICATION_DESC'),
				'SORT' => 9,
			];
			$arEventTypes[] = [
				'LID' => $lid,
				'EVENT_NAME' => 'USER_CODE_REQUEST',
				'NAME' => GetMessage("MAIN_INSTALL_EVENT_TYPE_USER_CODE_REQUEST"),
				'DESCRIPTION' => GetMessage("MAIN_INSTALL_EVENT_TYPE_USER_CODE_REQUEST_DESC"),
				'SORT' => 10,
			];
			$arEventTypes[] = [
				'LID' => $lid,
				'EVENT_NAME' => 'NEW_DEVICE_LOGIN',
				'NAME' => GetMessage('MAIN_INSTALL_EVENT_TYPE_NEW_DEVICE_LOGIN'),
				'DESCRIPTION' => GetMessage('MAIN_INSTALL_EVENT_TYPE_NEW_DEVICE_LOGIN_DESC'),
				'SORT' => 11,
			];

			//sms types
			$arEventTypes[] = [
				'LID' => $lid,
				'EVENT_NAME' => 'SMS_USER_CONFIRM_NUMBER',
				'EVENT_TYPE' => \Bitrix\Main\Mail\Internal\EventTypeTable::TYPE_SMS,
				'NAME' => GetMessage("main_install_sms_event_confirm_name"),
				'DESCRIPTION' => GetMessage("main_install_sms_event_confirm_descr"),
			];
			$arEventTypes[] = [
				'LID' => $lid,
				'EVENT_NAME' => 'SMS_USER_RESTORE_PASSWORD',
				'EVENT_TYPE' => \Bitrix\Main\Mail\Internal\EventTypeTable::TYPE_SMS,
				'NAME' => GetMessage("main_install_sms_event_restore_name"),
				'DESCRIPTION' => GetMessage("main_install_sms_event_restore_descr"),
			];
			$arEventTypes[] = [
				'LID' => $lid,
				'EVENT_NAME' => 'SMS_EVENT_LOG_NOTIFICATION',
				'EVENT_TYPE' => \Bitrix\Main\Mail\Internal\EventTypeTable::TYPE_SMS,
				'NAME' => getMessage('MAIN_INSTALL_EVENT_TYPE_NOTIFICATION'),
				'DESCRIPTION' => getMessage('MAIN_INSTALL_EVENT_TYPE_NOTIFICATION_DESC_SMS'),
			];
		}

		$type = new CEventType;
		foreach ($arEventTypes as $arEventType)
		{
			$type->Add($arEventType);
		}

		foreach ($languages as $language)
		{
			$this->InstallEventMessages($language['LID'], ['s1']);
		}

		return true;
	}

	public function InstallEventMessages(string $languageId, array $siteId): void
	{
		if (!file_exists(__DIR__ . '/../lang/' . $languageId . '/install/index.php'))
		{
			return;
		}

		IncludeModuleLangFile(__FILE__, $languageId);

		$arMessages = [];
		$arMessages[] = [
			"EVENT_NAME" => "NEW_USER",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
			"SUBJECT" => GetMessage("MAIN_NEW_USER_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_NEW_USER_EVENT_DESC"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "USER_INFO",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_INFO_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_INFO_EVENT_DESC"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "USER_PASS_REQUEST",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_PASS_REQUEST_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_PASS_REQUEST_EVENT_DESC"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "USER_PASS_CHANGED",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_PASS_CHANGED_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_PASS_CHANGED_EVENT_DESC"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "NEW_USER_CONFIRM",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_NEW_USER_CONFIRM_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_NEW_USER_CONFIRM_EVENT_DESC"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "USER_INVITE",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_INVITE_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_INVITE_EVENT_DESC"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "FEEDBACK_FORM",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("MF_EVENT_SUBJECT"),
			"MESSAGE" => GetMessage("MF_EVENT_MESSAGE"),
		];
		$arMessages[] = [
			'EVENT_NAME' => 'MAIN_MAIL_CONFIRM_CODE',
			'LID' => $siteId,
			'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
			'EMAIL_TO' => '#EMAIL_TO#',
			'SUBJECT' => '#MESSAGE_SUBJECT#',
			'MESSAGE' => "<? EventMessageThemeCompiler::includeComponent('bitrix:main.mail.confirm', '', \$arParams); ?>",
			'BODY_TYPE' => 'html',
			'SITE_TEMPLATE_ID' => 'mail_join',
		];
		$arMessages[] = [
			"EVENT_NAME" => "EVENT_LOG_NOTIFICATION",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_EVENT_MESS_NOTIFICATION"),
			"MESSAGE" => GetMessage("MAIN_EVENT_MESS_NOTIFICATION_TEXT"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "USER_CODE_REQUEST",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_INSTALL_EVENT_MESS_USER_CODE_REQUEST"),
			"MESSAGE" => GetMessage("MAIN_INSTALL_EVENT_MESS_USER_CODE_REQUEST_MESS"),
		];
		$arMessages[] = [
			"EVENT_NAME" => "NEW_DEVICE_LOGIN",
			"LID" => $siteId,
			"LANGUAGE_ID" => $languageId,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage('MAIN_INSTALL_EVENT_MESSAGE_NEW_DEVICE_LOGIN_SUBJECT'),
			"MESSAGE" => GetMessage('MAIN_INSTALL_EVENT_MESSAGE_NEW_DEVICE_LOGIN'),
		];

		$message = new CEventMessage;
		foreach ($arMessages as $arMessage)
		{
			$message->Add($arMessage);
		}

		//sms templates
		$smsTemplates = [
			[
				"EVENT_NAME" => "SMS_USER_CONFIRM_NUMBER",
				"ACTIVE" => true,
				"SENDER" => "#DEFAULT_SENDER#",
				"RECEIVER" => "#USER_PHONE#",
				"MESSAGE" => GetMessage("main_install_sms_template_confirm_mess"),
				"LANGUAGE_ID" => $languageId,
			],
			[
				"EVENT_NAME" => "SMS_USER_RESTORE_PASSWORD",
				"ACTIVE" => true,
				"SENDER" => "#DEFAULT_SENDER#",
				"RECEIVER" => "#USER_PHONE#",
				"MESSAGE" => GetMessage("main_install_sms_template_restore_mess"),
				"LANGUAGE_ID" => $languageId,
			],
			[
				"EVENT_NAME" => "SMS_EVENT_LOG_NOTIFICATION",
				"ACTIVE" => true,
				"SENDER" => "#DEFAULT_SENDER#",
				"RECEIVER" => "#PHONE_NUMBER#",
				"MESSAGE" => GetMessage("main_install_sms_template_notification_mess"),
				"LANGUAGE_ID" => $languageId,
			],
		];

		$entity = \Bitrix\Main\Sms\TemplateTable::getEntity();
		$site = \Bitrix\Main\SiteTable::getEntity()->wakeUpObject("s1");

		foreach ($smsTemplates as $smsTemplate)
		{
			$template = $entity->createObject();
			foreach ($smsTemplate as $field => $value)
			{
				$template->set($field, $value);
			}
			$template->addToSites($site);
			$template->save();
		}
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/bitrix", $_SERVER["DOCUMENT_ROOT"] . "/bitrix", true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/install/admin", $_SERVER['DOCUMENT_ROOT'] . "/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/tools", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/services", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/services", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/images", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/sounds", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/sounds", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/components/bitrix", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/gadgets/bitrix", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/gadgets/bitrix", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/panel", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/panel", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/fonts", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/fonts", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/install/css", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css", true, true);

		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
	}

	function DoUninstall()
	{
	}
}
