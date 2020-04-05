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
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$arResult["MESSAGES"] = [];
if ($request->isPost() &&
	check_bitrix_sessid() &&
	\Bitrix\Main\Grid\Context::isInternalRequest() &&
	$request->get("grid_id") == $arResult["GRID_ID"])
{
	$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

	if ($request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_DELETE_ROW)
	{
		$event = \Bitrix\Rest\EventTable::getByPrimary($request->getPost("id"))->fetch();
		if ($event && $event['USER_ID'] == $USER->GetID() && intval($event['APP_ID']) <= 0)
		{
			$result = \Bitrix\Rest\EventTable::delete($event['ID']);
		}
		else
		{
			$result = (new \Bitrix\Main\Result())->addError(new \Bitrix\Main\Error("Could not find event."));
		}
		if (!$result->isSuccess())
		{
			$arResult["MESSAGES"] = $result->getErrorMessages();
		}
	}
}

$arResult["ELEMENTS_ROWS"] = [];

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
		"URL" => str_replace("#id#", $event['ID'], $arParams['EDIT_URL_TPL'])
	);

	$arResult["ELEMENTS_ROWS"][$event["ID"]] = $data;
}

if($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage('REST_EVENT_LIST_TITLE'));
}

$this->IncludeComponentTemplate();
