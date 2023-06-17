<?php

IncludeModuleLangFile(__FILE__);

ClearVars();
$errorMessage = '';
$errAdmMessage = null;
$fCriticalError = false;

// Using report module
if (!CModule::IncludeModule('report'))
{
	$errorMessage .= GetMessage("REPORT_MODULE_NOT_INSTALLED").'<br>';
	$fCriticalError = true;
}

// Using currency module
if (!CModule::IncludeModule('currency'))
{
	$errorMessage .= GetMessage("CURRENCY_MODULE_NOT_INSTALLED").'<br>';
	$fCriticalError = true;
}

// Using catalog module
if (!CModule::IncludeModule('catalog'))
{
	$errorMessage .= GetMessage("CATALOG_MODULE_NOT_INSTALLED").'<br>';
	$fCriticalError = true;
}

// Using iblock module
if (!CModule::IncludeModule('iblock'))
{
	$errorMessage .= GetMessage("IBLOCK_MODULE_NOT_INSTALLED").'<br>';
	$fCriticalError = true;
}

// If exists $ID parameter and it more than 0, then it is identifier of report that will be created.
$ID = (int)$_REQUEST['ID'];
if ($ID == 0)
{
	$errorMessage .= GetMessage("REPORT_VIEW_REP_ID_NOT_SET").'<br>';
	$fCriticalError = true;
}

if (!$fCriticalError)
{
	CBaseSaleReportHelper::init();

	//<editor-fold defaultstate='collapsed' desc="Forming parameters of component report.view">
	$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
	$arParams = array(
		'PATH_TO_REPORT_LIST' => $selfFolderUrl . 'sale_report.php?lang='.LANG,
		'PATH_TO_REPORT_CONSTRUCT' => $selfFolderUrl . 'sale_report_construct.php?lang='.LANG,
		'PATH_TO_REPORT_VIEW' => $selfFolderUrl . 'sale_report_view.php',
		'REPORT_ID' => $ID,
		'ROWS_PER_PAGE' => 50,
		'NAV_TEMPLATE' => 'arrows_adm',
		'USE_CHART' => true,
		'SHOW_EDIT_BUTTON' => !isset($_REQUEST['publicSidePanel']) || $_REQUEST['publicSidePanel'] != 'Y',
	);
	//</editor-fold>

	// <editor-fold defaultstate="collapsed" desc="POST action">
	if ($_REQUEST['cancel'] ?? false)
	{
		LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
	}

	$siteList = CBaseSaleReportHelper::getSiteList();
	if (isset($_REQUEST['F_SALE_SITE']))
	{
		$siteId = mb_substr($_REQUEST['F_SALE_SITE'], 0, 2);
		if (array_key_exists($siteId, $siteList))
		{
			$siteCookieId = CBaseSaleReportHelper::getSiteCookieId();
			setcookie($siteCookieId, $siteId, time()+365*24*3600);
		}
		$arParams['F_SALE_SITE'] = $siteId;
		CBaseSaleReportHelper::setDefaultSiteId($siteId);
		unset($siteId);
	}
	else
	{
		$siteCookieId = CBaseSaleReportHelper::getSiteCookieId();
		if (isset($_COOKIE[$siteCookieId]))
		{
			$siteId = mb_substr($_COOKIE[$siteCookieId], 0, 2);
			if (array_key_exists($siteId, $siteList)) $arParams['F_SALE_SITE'] = $siteId;
			CBaseSaleReportHelper::setDefaultSiteId($siteId);
			unset($siteId);
		}
	}
	$arParams['USER_NAME_FORMAT'] = CSite::getNameFormat(null, CBaseSaleReportHelper::getDefaultSiteId());

	// Product custom "quantity" filter
	if (isset($_REQUEST['F_SALE_PRODUCT']))
	{
		if (in_array($_REQUEST['F_SALE_PRODUCT'], array('all', 'avail', 'not_avail')))
			$arParams['F_SALE_PRODUCT'] = $_REQUEST['F_SALE_PRODUCT'];
	}

	// Product custom "types of prices" filter
	$arSelectedPriceTypes = array();
	if (isset($_REQUEST['F_SALE_UCSPT']) && is_array($_REQUEST['F_SALE_UCSPT']))
	{
		$i = 0;
		foreach ($_REQUEST['F_SALE_UCSPT'] as $k => $v)
		{
			if ($i++ === $k && is_numeric($v)) $arSelectedPriceTypes[] = intval($v);
		}
	}
	CBaseSaleReportHelper::setSelectedPriceTypes($arSelectedPriceTypes);

	if (($_REQUEST['REPORT_AJAX'] ?? '') === 'Y')
	{
		$arResponse = array();
		if (is_array($_REQUEST['filterTypes']))
		{
			$result = CBaseSaleReportHelper::getAjaxResponse($_REQUEST['filterTypes']);
			if (is_array($result)) $arResponse = $result;
		}
		header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);
		echo CUtil::PhpToJSObject($arResponse);
		exit;
	}
	// </editor-fold>


	if (!isset($arParams['F_SALE_SITE']))
	{
		$arParams['F_SALE_SITE'] = CBaseSaleReportHelper::getDefaultSiteId();
	}

	// Select report currency
	$siteId = CBaseSaleReportHelper::getDefaultSiteId();
	$siteCurrencyId = '';
	if ($siteId !== null)
	{
		$arCurr = \CSaleLang::GetByID($siteId);
		if (!empty($arCurr['CURRENCY']))
		{
			$siteCurrencyId = $arCurr['CURRENCY'];
		}
	}
	if (empty($siteCurrencyId))
	{
		$siteCurrencyId = \COption::GetOptionString(
			'sale', 'default_currency', null, ($siteId !== null) ? $siteId : false
		);
	}
	CBaseSaleReportHelper::setSiteCurrencyId($siteCurrencyId);
	$reportCurrencyId = $siteCurrencyId;
	if (isset($_REQUEST['F_SALE_CURRENCY']))
	{
		$currenciesIds = array_keys(CBaseSaleReportHelper::getCurrencies());
		if (in_array($_REQUEST['F_SALE_CURRENCY'], $currenciesIds, true))
		{
			$reportCurrencyId = $_REQUEST['F_SALE_CURRENCY'];
		}
	}
	CBaseSaleReportHelper::setSelectedCurrency($reportCurrencyId);

	$reportCurrency = CCurrencyLang::GetById($reportCurrencyId, LANGUAGE_ID);
	$reportWeightUnits = CBaseSaleReportHelper::getDefaultSiteWeightUnits();
	$arParams['REPORT_CURRENCY_LABEL_TEXT'] = GetMessage('SALE_REPORT_VIEW_CURRENCY_LABEL_TITLE').': '.$reportCurrency['FULL_NAME'];
	$arParams['REPORT_WEIGHT_UNITS_LABEL_TEXT'] = GetMessage('SALE_REPORT_VIEW_WEIGHT_UNITS_LABEL_TITLE').': '.$reportWeightUnits;

	// Beforehand we get report parameters.
	$arRepParams = array();
	if (!($arRepParams = Bitrix\Report\ReportTable::getById($ID)->fetch()))
	{
		$errorMessage .= GetMessage("SALE_REPORT_VIEW_ERROR_GET_REP_PARAMS").'<br>';
		$fCriticalError = true;
	}
}

if (!$fCriticalError)
{
	// get helper name
	$arParams['OWNER_ID'] = $arRepParams['OWNER_ID'];
	$arParams['REPORT_HELPER_CLASS'] = CBaseSaleReportHelper::getHelperByOwner($arRepParams['OWNER_ID']);

	// fill report title
	$arParams['TITLE'] = $arRepParams['TITLE'];
}

if (!$fCriticalError)
{
	// helper specific filters
	if ($arParams['OWNER_ID'] === 'sale_SaleProduct')
	{
		// Product custom filter (set value to helper)
		if (!empty($arParams['F_SALE_PRODUCT']))
		{
			call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'setCustomProductFilter'), $arParams['F_SALE_PRODUCT']);
		}

		// Product custom "types of prices" filter (set report setting to helper)
		$arRepSetting = unserialize($arRepParams['SETTINGS'], ['allowed_classes' => false]);
		if (($arRepSetting['helper_spec']['ucspt'] ?? false) === true)
		{
			call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'enablePriceTypesColumns'), true);
		}
	}
}
