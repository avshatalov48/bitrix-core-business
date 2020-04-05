#!#PHP_PATH# -q
<?php
/* replace #PHP_PATH# to real path of php binary
For example:
/user/bin/php
/usr/bin/perl
/usr/bin/env python
*/
$_SERVER["DOCUMENT_ROOT"] = "#DOCUMENT_ROOT#"; // replace #DOCUMENT_ROOT# to real document root path
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

$siteID = '#SITE_ID#'; // replace #SITE_ID# to your real site ID - need for language ID

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define("BX_CAT_CRON", true);
define('NO_AGENT_CHECK', true);
if (preg_match('/^[a-z0-9_]{2}$/i', $siteID) === 1)
{
	define('SITE_ID', $siteID);
}
else
{
	die('No defined site - $siteID');
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $DB;

if (!defined('LANGUAGE_ID') || preg_match('/^[a-z]{2}$/i', LANGUAGE_ID) !== 1)
	die('Language id is absent - defined site is bad');

set_time_limit(0);

if (!\Bitrix\Main\Loader::includeModule('catalog'))
	die('Can\'t include module');

$profile_id = 0;
if (isset($argv[1]))
	$profile_id = (int)$argv[1];
if ($profile_id <= 0)
	die('No profile id');

$ar_profile = CCatalogExport::GetByID($profile_id);
if (!$ar_profile)
	die('No profile');

$strFile = CATALOG_PATH2EXPORTS.$ar_profile["FILE_NAME"]."_run.php";
if (!file_exists($_SERVER["DOCUMENT_ROOT"].$strFile))
{
	$strFile = CATALOG_PATH2EXPORTS_DEF.$ar_profile["FILE_NAME"]."_run.php";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"].$strFile))
		die('No export script');
}

$arSetupVars = array();
$intSetupVarsCount = 0;
if ($ar_profile["DEFAULT_PROFILE"] != 'Y')
{
	parse_str($ar_profile["SETUP_VARS"], $arSetupVars);
	if (!empty($arSetupVars) && is_array($arSetupVars))
		$intSetupVarsCount = extract($arSetupVars, EXTR_SKIP);
}

$firstStep = true;

global $arCatalogAvailProdFields;
$arCatalogAvailProdFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_ELEMENT);
global $arCatalogAvailPriceFields;
$arCatalogAvailPriceFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_CATALOG);
global $arCatalogAvailValueFields;
$arCatalogAvailValueFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE);
global $arCatalogAvailQuantityFields;
$arCatalogAvailQuantityFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE_EXT);
global $arCatalogAvailGroupFields;
$arCatalogAvailGroupFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_SECTION);

global $defCatalogAvailProdFields;
$defCatalogAvailProdFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_ELEMENT);
global $defCatalogAvailPriceFields;
$defCatalogAvailPriceFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CATALOG);
global $defCatalogAvailValueFields;
$defCatalogAvailValueFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE);
global $defCatalogAvailQuantityFields;
$defCatalogAvailQuantityFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE_EXT);
global $defCatalogAvailGroupFields;
$defCatalogAvailGroupFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_SECTION);
global $defCatalogAvailCurrencies;
$defCatalogAvailCurrencies = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CURRENCY);

CCatalogDiscountSave::Disable();
include($_SERVER["DOCUMENT_ROOT"].$strFile);
CCatalogDiscountSave::Enable();

CCatalogExport::Update(
	$profile_id,
	array(
		"=LAST_USE" => $DB->GetNowFunction()
	)
);