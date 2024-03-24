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

$this->setFrameMode(false);

if (!CModule::IncludeModule('subscribe'))
{
	ShowError(GetMessage('SUBSCR_MODULE_NOT_INSTALLED'));
	return;
}

if (!isset($arParams['CACHE_TIME']))
{
	$arParams['CACHE_TIME'] = 3600;
}

if (!isset($arParams['PAGE']) || $arParams['PAGE'] == '')
{
	$arParams['PAGE'] = COption::GetOptionString('subscribe', 'subscribe_section') . 'subscr_edit.php';
}
$arParams['SHOW_HIDDEN'] = $arParams['SHOW_HIDDEN'] == 'Y';
$arParams['SHOW_COUNT'] = $arParams['SHOW_COUNT'] == 'Y';
$arParams['SET_TITLE'] = $arParams['SET_TITLE'] != 'N';

//get current user subscription from cookies
$arSubscription = CSubscription::GetUserSubscription();

//get user's newsletter categories
$arSubscriptionRubrics = CSubscription::GetRubricArray(intval($arSubscription['ID']));

//get site's newsletter categories
$obCache = new CPHPCache;
$strCacheID = LANGUAGE_ID . $arParams['SHOW_HIDDEN'] . $this->getRelativePath();
if ($obCache->StartDataCache($arParams['CACHE_TIME'], $strCacheID, '/' . SITE_ID . $this->getRelativePath()))
{
	$arFilter = ['ACTIVE' => 'Y', 'LID' => SITE_ID];
	if (!$arParams['SHOW_HIDDEN'])
	{
		$arFilter['VISIBLE'] = 'Y';
	}
	$rsRubric = CRubric::GetList(['SORT' => 'ASC', 'NAME' => 'ASC'], $arFilter);
	$arRubrics = [];
	while ($arRubric = $rsRubric->GetNext())
	{
		$arRubric['SUBSCRIBER_COUNT'] = $arParams['SHOW_COUNT'] ? CRubric::GetSubscriptionCount($arRubric['ID']) : 0;
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
$arResult['SHOW_COUNT'] = $arParams['SHOW_COUNT'];

if ($arSubscription['EMAIL'] <> '')
{
	$arResult['EMAIL'] = htmlspecialcharsbx($arSubscription['EMAIL']);
}
else
{
	$arResult['EMAIL'] = htmlspecialcharsbx($USER->GetParam('EMAIL'));
}

//check whether already authorized
$arResult['SHOW_PASS'] = true;
if ($arSubscription['ID'] > 0)
{
	//try to authorize user account's subscription
	if ($arSubscription['USER_ID'] > 0 && !CSubscription::IsAuthorized($arSubscription['ID']))
	{
		CSubscription::Authorize($arSubscription['ID'], '');
	}
	//check authorization
	if (CSubscription::IsAuthorized($arSubscription['ID']))
	{
		$arResult['SHOW_PASS'] = false;
	}
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
		'DESCRIPTION' => $arRubric['DESCRIPTION'],
		'CHECKED' => $bChecked,
		'SUBSCRIBER_COUNT' => $arRubric['SUBSCRIBER_COUNT'],
	];
}

if ($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(GetMessage('SUBSCR_PAGE_TITLE'), ['COMPONENT_NAME' => $this->getName()]);
}

$this->includeComponentTemplate();
