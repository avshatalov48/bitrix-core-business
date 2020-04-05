<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
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
	|| !$USER->IsAuthorized()
)
{
	return;
}

$arParams['PAGE_SIZE'] = intval($arParams['PAGE_SIZE']) > 0 ? intval($arParams['PAGE_SIZE']) : 20;
$arParams['EDIT_URL_TPL'] = isset($arParams['EDIT_URL_TPL']) ? trim($arParams['EDIT_URL_TPL']) : SITE_DIR.'marketplace/hook/ap/#id#/';

InitBVar($arParams['SET_TITLE']);

$arResult["GRID_ID"] = "rest_hook_ap";
$arResult["ELEMENTS_ROWS"] = array();

$filter = array(
	'=USER_ID' => $USER->GetID(),
);

$nav = new \Bitrix\Main\UI\PageNavigation("nav-rest-ap");
$nav->allowAllRecords(false)
	->setPageSize($arParams['PAGE_SIZE'])
	->initFromUri();

$dbRes = \Bitrix\Rest\APAuth\PasswordTable::getList(array(
	'order' => array('ID' => 'DESC'),
	'filter' => $filter,
	'select' => array(
		'ID', 'DATE_CREATE', 'DATE_LOGIN', 'LAST_IP',
		'TITLE', 'COMMENT',
	),
	"count_total" => true,
	"offset" => $nav->getOffset(),
	"limit" => $nav->getLimit(),
));

$arResult['ROWS_COUNT'] = $dbRes->getCount();
$nav->setRecordCount($arResult['ROWS_COUNT']);

$arResult["NAV_OBJECT"] = $nav;

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();
while($ap = $dbRes->fetch())
{
	$cols = array();

	$data = array(
		"ID" => $ap['ID'],
		"TITLE" => $c->encode($ap['TITLE']),
		"COMMENT" => $c->encode($ap["COMMENT"]),
		"DATE_CREATE" => $c->encode($ap['DATE_CREATE']),
		"DATE_LOGIN" => $c->encode($ap['DATE_LOGIN']),
		"LAST_IP" => $c->encode($ap['LAST_IP']),
	);

	$actions = array();
	$actions[] = array(
		'ICONCLASS' => 'edit',
		'TITLE' => Loc::getMessage('REST_HOOK_EDIT'),
		'TEXT' => Loc::getMessage('REST_HOOK_EDIT'),
		'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape(str_replace("#id#", $ap['ID'], $arParams['EDIT_URL_TPL']))."');",
		'DEFAULT' => true
	);

	$actions[] = array(
		'ICONCLASS' => 'delete',
		'TITLE' => Loc::getMessage('REST_HOOK_DELETE'),
		'TEXT' => Loc::getMessage('REST_HOOK_DELETE'),
		'ONCLICK' => "BX.Marketplace.Hook.Ap.delete('".$ap["ID"]."');",
		'DEFAULT' => false
	);

	$arResult["ELEMENTS_ROWS"][$ap["ID"]] = array("data" => $data, "columns" => $cols, 'actions' => $actions);
}

$arResult["HEADERS"] = array(
	array("id" => "ID", "name" => "ID", "default" => false, "editable" => false),
	array("id" => "TITLE", "name" => Loc::getMessage("REST_HOOK_HEADER_TITLE"), "default" => true, "editable" => false),
	array("id" => "DATE_CREATE", "name" => Loc::getMessage("REST_HOOK_HEADER_DATE_CREATE"), "default" => true, "editable" => false),
	array("id" => "DATE_LOGIN", "name" => Loc::getMessage("REST_HOOK_HEADER_DATE_LOGIN"), "default" => true, "editable" => false),
	array("id" => "LAST_IP", "name" => Loc::getMessage("REST_HOOK_HEADER_LAST_IP"), "default" => true, "editable" => false),
	array("id" => "COMMENT", "name" => Loc::getMessage("REST_HOOK_HEADER_COMMENT"), "default" => true, "editable" => false),
);

if($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage('REST_AP_LIST_TITLE'));
}

$this->IncludeComponentTemplate();
