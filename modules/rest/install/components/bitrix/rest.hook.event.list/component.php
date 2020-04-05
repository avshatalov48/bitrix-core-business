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
$arParams['EDIT_URL_TPL'] = isset($arParams['EDIT_URL_TPL']) ? trim($arParams['EDIT_URL_TPL']) : SITE_DIR.'marketplace/hook/event/#id#/';

InitBVar($arParams['SET_TITLE']);

$arResult["GRID_ID"] = "rest_hook_event";
$arResult["ELEMENTS_ROWS"] = array();

$filter = array(
	'=USER_ID' => $USER->GetID(),
	'==APP_ID' => null,
);

$nav = new \Bitrix\Main\UI\PageNavigation("nav-rest-event");
$nav->allowAllRecords(false)
	->setPageSize($arParams['PAGE_SIZE'])
	->initFromUri();

$dbRes = \Bitrix\Rest\EventTable::getList(array(
	'order' => array('ID' => 'DESC'),
	'filter' => $filter,
	'select' => array(
		'ID', 'EVENT_NAME', 'EVENT_HANDLER', 'TITLE', 'COMMENT', 'DATE_CREATE'
	),
	"count_total" => true,
	"offset" => $nav->getOffset(),
	"limit" => $nav->getLimit(),
));

$arResult['ROWS_COUNT'] = $dbRes->getCount();
$nav->setRecordCount($arResult['ROWS_COUNT']);

$arResult["NAV_OBJECT"] = $nav;

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();

$eventDictionary = new \Bitrix\Rest\Dictionary\WebHook();
$eventDesc = array();

foreach($eventDictionary as $event)
{
	$eventDesc[ToUpper($event['code'])] = $event;
}

while($event = $dbRes->fetch())
{
	$cols = array();

	$eventName = $eventDesc[ToUpper($event['EVENT_NAME'])]['name'];
	if(strlen($eventName) <= 0)
	{
		$eventName = $event['EVENT_NAME'];
	}

	$data = array(
		"ID" => $event['ID'],
		"TITLE" => $c->encode($event['TITLE']),
		"COMMENT" => $c->encode($event['COMMENT']),
		"DATE_CREATE" => $c->encode($event['DATE_CREATE']),
		"EVENT_NAME" => $c->encode($eventName),
		"EVENT_HANDLER" => $c->encode($event["EVENT_HANDLER"]),
	);

	$actions = array();
	$actions[] = array(
		'ICONCLASS' => 'edit',
		'TITLE' => Loc::getMessage('REST_HOOK_EDIT'),
		'TEXT' => Loc::getMessage('REST_HOOK_EDIT'),
		'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape(str_replace("#id#", $event['ID'], $arParams['EDIT_URL_TPL']))."');",
		'DEFAULT' => true
	);

	$actions[] = array(
		'ICONCLASS' => 'delete',
		'TITLE' => Loc::getMessage('REST_HOOK_DELETE'),
		'TEXT' => Loc::getMessage('REST_HOOK_DELETE'),
		'ONCLICK' => "BX.Marketplace.Hook.Event.delete('".$event["ID"]."');",
		'DEFAULT' => false
	);

	$arResult["ELEMENTS_ROWS"][$event["ID"]] = array("data" => $data, "columns" => $cols, 'actions' => $actions);
}

$arResult["HEADERS"] = array(
	array("id" => "ID", "name" => "ID", "default" => false, "editable" => false),
	array("id" => "TITLE", "name" => Loc::getMessage("REST_HOOK_TITLE"), "default" => true, "editable" => false),
	array("id" => "COMMENT", "name" => Loc::getMessage("REST_HOOK_COMMENT"), "default" => true, "editable" => false),
	array("id" => "DATE_CREATE", "name" => Loc::getMessage("REST_HOOK_DATE_CREATE"), "default" => true, "editable" => false),
	array("id" => "EVENT_NAME", "name" => Loc::getMessage("REST_HOOK_EVENT_NAME"), "default" => true, "editable" => false),
	array("id" => "EVENT_HANDLER", "name" => Loc::getMessage("REST_HOOK_EVENT_HANDLER"), "default" => true, "editable" => false),
);

if($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage('REST_EVENT_LIST_TITLE'));
}

$this->IncludeComponentTemplate();
