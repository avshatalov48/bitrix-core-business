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

use Bitrix\Main\Localization\Loc;

if(
	!\Bitrix\Main\Loader::includeModule("rest")
	|| !\CRestUtil::isAdmin()
)
{
	return;
}

$arParams['PAGE_SIZE'] = intval($arParams['PAGE_SIZE']) > 0 ? intval($arParams['PAGE_SIZE']) : 20;
$arParams['EDIT_URL_TPL'] = isset($arParams['EDIT_URL_TPL']) ? trim($arParams['EDIT_URL_TPL']) : SITE_DIR.'marketplace/local/edit/0/';
$arParams['APPLICATION_URL'] = isset($arParams['APPLICATION_URL']) ? trim($arParams['APPLICATION_URL']) : SITE_DIR.'marketplace/app/#id#/';

$arResult["GRID_ID"] = "rest_local_app";
$arResult["ELEMENTS_ROWS"] = array();

\CJSCore::Init(array('marketplace'));

$filter = array('=STATUS' => \Bitrix\Rest\AppTable::STATUS_LOCAL);

$nav = new \Bitrix\Main\UI\PageNavigation("nav-app");
$nav->allowAllRecords(false)
		->setPageSize($arParams['PAGE_SIZE'])
		->initFromUri();

$dbApp = \Bitrix\Rest\AppTable::getList(array(
	'filter' => $filter,
	'select' => array(
		'ID', 'APP_NAME', 'CLIENT_ID', 'CLIENT_SECRET',
		'URL_INSTALL',
		'MENU_NAME' => 'LANG.MENU_NAME',
		'MENU_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
		'MENU_NAME_LICENSE' => 'LANG_LICENSE.MENU_NAME',
	),
	"count_total" => true,
	"offset" => $nav->getOffset(),
	"limit" => $nav->getLimit(),
));

$arResult['ROWS_COUNT'] = $dbApp->getCount();
$nav->setRecordCount($arResult['ROWS_COUNT']);

$arResult["NAV_OBJECT"] = $nav;

while($app = $dbApp->fetch())
{
	$data = array(
		"ID" => $app["ID"],
		"NAME" => \Bitrix\Main\Text\Converter::getHtmlConverter()->encode($app["APP_NAME"]),
		"CLIENT_ID" => $app["CLIENT_ID"],
		"SECRET_ID" => $app["CLIENT_SECRET"],
	);

	$onlyApi = empty($app["MENU_NAME"]) && empty($app["MENU_NAME_DEFAULT"]) && empty($app["MENU_NAME_LICENSE"]);

	$cols['ONLY_API'] = $onlyApi ? Loc::getMessage("APP_YES") : Loc::getMessage("APP_NO");

	$actions = array();
	$actions[] = array(
		'ICONCLASS' => 'view',
		'TITLE' => Loc::getMessage('APP_EDIT'),
		'TEXT' => Loc::getMessage('APP_EDIT'),
		'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape(str_replace("#id#", $app['ID'], $arParams['EDIT_URL_TPL']))."');",
		'DEFAULT' => true
	);

	if(!$onlyApi)
	{
		$actions[] = array(
			'ICONCLASS' => 'view',
			'TITLE' => Loc::getMessage('APP_OPEN'),
			'TEXT' => Loc::getMessage('APP_OPEN'),
			'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape(str_replace("#id#", $app['ID'], $arParams['APPLICATION_URL']))."');",
		);
	}

	$actions[] = array(
		'ICONCLASS' => 'view',
		'TITLE' => Loc::getMessage('APP_DELETE'),
		'TEXT' => Loc::getMessage('APP_DELETE'),
		'ONCLICK' => "BX.Marketplace.LocalappList.delete('".$app["ID"]."');",
		'DEFAULT' => false
	);
	$actions[] = array(
		'ICONCLASS' => 'view',
		'TITLE' => Loc::getMessage('APP_RIGHTS'),
		'TEXT' => Loc::getMessage('APP_RIGHTS'),
		'ONCLICK' => "BX.rest.Marketplace.setRights('".$app["ID"]."');",
		'DEFAULT' => false
	);

	if(strlen($app['URL_INSTALL']) > 0)
	{
		$actions[] = array(
			'ICONCLASS' => 'view',
			'TITLE' => Loc::getMessage('APP_REINSTALL'),
			'TEXT' => Loc::getMessage('APP_REINSTALL'),
			'ONCLICK' => "BX.rest.Marketplace.reinstall('".$app["ID"]."')",
			'DEFAULT' => false
		);
	}


	$arResult["ELEMENTS_ROWS"][$app["ID"]] = array("data" => $data, "columns" => $cols, 'actions' => $actions);
}

$arResult["HEADERS"] = array(
	array("id"=>"ID", "name"=> "ID", "default"=>true, "editable"=>false),
	array("id"=>"NAME", "name"=> Loc::getMessage("APP_HEADER_NAME"), "default"=>true, "editable"=>false),
	array("id"=>"ONLY_API", "name"=> Loc::getMessage("APP_HEADER_ONLY_API"), "default"=>true, "editable"=>false),
	array("id"=>"CLIENT_ID", "name"=> Loc::getMessage("APP_HEADER_CLIENT_ID"), "default"=>true, "editable"=>false),
	array("id"=>"SECRET_ID", "name"=> Loc::getMessage("APP_HEADER_SECRET_ID"), "default"=>true, "editable"=>false)
);

$APPLICATION->SetTitle(Loc::getMessage('APP_LIST_TITLE'));

$this->IncludeComponentTemplate();
