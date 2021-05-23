<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

if(
	!\Bitrix\Main\Loader::includeModule("rest")
	|| !\CRestUtil::isAdmin()
)
{
	return;
}
$arResult["GRID_ID"] = "rest_local_app";
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if ($request->isPost() &&
	check_bitrix_sessid() &&
	\Bitrix\Main\Grid\Context::isInternalRequest() &&
	$request->get("grid_id") == $arResult["GRID_ID"])
{
	$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

	if ($request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_DELETE_ROW)
	{
		$app = \Bitrix\Rest\AppTable::getByClientId($request->getPost("id"));
		if($app["ID"])
		{
			$result = \Bitrix\Rest\AppTable::delete($app['ID']);
		}
	}
}

$arParams['PAGE_SIZE'] = intval($arParams['PAGE_SIZE']) > 0 ? intval($arParams['PAGE_SIZE']) : 20;
$arParams['EDIT_URL_TPL'] = isset($arParams['EDIT_URL_TPL']) ? trim($arParams['EDIT_URL_TPL']) : SITE_DIR.'marketplace/local/edit/0/';
$arParams['APPLICATION_URL'] = isset($arParams['APPLICATION_URL']) ? trim($arParams['APPLICATION_URL']) : SITE_DIR.'marketplace/app/#id#/';


$arResult["ELEMENTS_ROWS"] = array();

\CJSCore::Init(array('marketplace'));

$filter = array('=STATUS' => \Bitrix\Rest\AppTable::STATUS_LOCAL);

$nav = new \Bitrix\Main\UI\PageNavigation("nav-app");
$nav->allowAllRecords(false)
		->setPageSize($arParams['PAGE_SIZE'])
		->initFromUri();

$appList = \Bitrix\Rest\AppTable::getList(
	[
		'filter' => $filter,
		'select' => [
			'ID',
			'APP_NAME',
			'CLIENT_ID',
			'CLIENT_SECRET',
			'URL_INSTALL',
			'LANG',
			'LANG_DEFAULT',
			'LANG_LICENSE',
		],
		'count_total' => true,
		'offset' => $nav->getOffset(),
		'limit' => $nav->getLimit(),
	]
);

$arResult['ROWS_COUNT'] = $appList->getCount();
$nav->setRecordCount($arResult['ROWS_COUNT']);
$arResult['NAV_OBJECT'] = $nav;

foreach ($appList->fetchCollection() as $app)
{
	$id = $app->getId();
	$arResult['ELEMENTS_ROWS'][$id] = [
		'ID' => $id,
		'APP_NAME' => $app->getAppName(),
		'CLIENT_ID' => $app->getClientId(),
		'CLIENT_SECRET' => $app->getClientSecret(),
		'URL_INSTALL' => $app->getUrlInstall(),
		'MENU_NAME' => !is_null($app->getLang()) ? $app->getLang()->getMenuName() : '',
		'MENU_NAME_DEFAULT' => !is_null($app->getLangDefault()) ? $app->getLangDefault()->getMenuName() : '',
		'MENU_NAME_LICENSE' => !is_null($app->getLangLicense()) ? $app->getLangLicense()->getMenuName() : '',
		'MENU_NAME_ALL' => []
	];

	$app->fillLangAll();
	if (!is_null($app->getLangAll()))
	{
		foreach ($app->getLangAll() as $lang)
		{
			$arResult['ELEMENTS_ROWS'][$id]['MENU_NAME_ALL'][$lang->getLanguageId()] = $lang->getMenuName();
		}
	}
}


$APPLICATION->SetTitle(Loc::getMessage('APP_LIST_TITLE'));
$this->IncludeComponentTemplate();
