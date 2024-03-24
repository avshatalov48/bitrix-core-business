<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @var CDatabase $DB */
/** @var CUser $USER */
/** @var CMain $APPLICATION */

if (!IsModuleInstalled('subscribe'))
{
	ShowError(GetMessage('SUBSCR_MODULE_NOT_INSTALLED'));
	return;
}

if (!isset($arParams['CACHE_TIME']))
{
	$arParams['CACHE_TIME'] = 3600;
}
if ($arParams['CACHE_TYPE'] == 'N' || ($arParams['CACHE_TYPE'] == 'A' && COption::GetOptionString('main', 'component_cache_on', 'Y') == 'N'))
{
	$arParams['CACHE_TIME'] = 0;
}

if (!isset($arParams['PAGE']) || $arParams['PAGE'] == '')
{
	$arParams['PAGE'] = COption::GetOptionString('subscribe', 'subscribe_section') . 'subscr_edit.php';
}
$arParams['SHOW_HIDDEN'] = $arParams['SHOW_HIDDEN'] == 'Y';
$arParams['USE_PERSONALIZATION'] = $arParams['USE_PERSONALIZATION'] != 'N';

if ($arParams['USE_PERSONALIZATION'])
{
	if (!CModule::IncludeModule('subscribe'))
	{
		ShowError(GetMessage('SUBSCR_MODULE_NOT_INSTALLED'));
		return;
	}
	//get current user subscription from cookies
	$arSubscription = CSubscription::GetUserSubscription();
	//get user's newsletter categories
	$arSubscriptionRubrics = CSubscription::GetRubricArray(intval($arSubscription['ID']));
}
else
{
	$arSubscription = ['ID' => 0, 'EMAIL' => ''];
	$arSubscriptionRubrics = [];
}

//get site's newsletter categories
$obCache = new CPHPCache;
$strCacheID = LANGUAGE_ID . $arParams['SHOW_HIDDEN'];
if ($obCache->StartDataCache($arParams['CACHE_TIME'], $strCacheID, '/' . SITE_ID . $this->getRelativePath()))
{
	if (!CModule::IncludeModule('subscribe'))
	{
		$obCache->AbortDataCache();
		ShowError(GetMessage('SUBSCR_MODULE_NOT_INSTALLED'));
		return;
	}

	$arFilter = ['ACTIVE' => 'Y', 'LID' => SITE_ID];
	if (!$arParams['SHOW_HIDDEN'])
	{
		$arFilter['VISIBLE'] = 'Y';
	}
	$rsRubric = CRubric::GetList(['SORT' => 'ASC', 'NAME' => 'ASC'], $arFilter);
	$arRubrics = [];
	while ($arRubric = $rsRubric->GetNext())
	{
		$arRubrics[] = $arRubric;
	}
	$obCache->EndDataCache($arRubrics);
}
else
{
	$arRubrics = $obCache->GetVars();
}

if (!$arRubrics)
{
	ShowError(GetMessage('SUBSCR_NO_RUBRIC_FOUND'));
	return;
}

$arResult['FORM_ACTION'] = htmlspecialcharsbx(str_replace('#SITE_DIR#', LANG_DIR, $arParams['PAGE']));

if ($_REQUEST['sf_EMAIL'] <> '')
{
	$arResult['EMAIL'] = htmlspecialcharsbx($_REQUEST['sf_EMAIL']);
}
elseif ($arSubscription['EMAIL'] <> '')
{
	$arResult['EMAIL'] = htmlspecialcharsbx($arSubscription['EMAIL']);
}
else
{
	$arResult['EMAIL'] = '';
}

$arResult['RUBRICS'] = [];
foreach ($arRubrics as $arRubric)
{
	$bChecked = (
		// user is already subscribed
		!is_array($_REQUEST['sf_RUB_ID']) && in_array($arRubric['ID'], $arSubscriptionRubrics) ||
		// or there is no information about user subscription
		!is_array($_REQUEST['sf_RUB_ID']) && intval($arSubscription['ID']) == 0 ||
		// or user has checked the category and posted the form
		is_array($_REQUEST['sf_RUB_ID']) && in_array($arRubric['ID'], $_REQUEST['sf_RUB_ID'])
	);

	$arResult['RUBRICS'][] = [
		'ID' => $arRubric['ID'],
		'NAME' => $arRubric['NAME'],
		'CHECKED' => $bChecked,
	];
}

$this->includeComponentTemplate();
