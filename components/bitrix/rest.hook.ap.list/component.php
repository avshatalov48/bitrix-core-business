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

$arResult["GRID_ID"] = "rest_hook_ap";
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
		$ap = \Bitrix\Rest\APAuth\PasswordTable::getByPrimary($request->getPost("id"))->fetch();
		if ($ap && $ap['USER_ID'] == $USER->GetID())
		{
			$result = \Bitrix\Rest\APAuth\PasswordTable::delete($ap['ID']);
		}
		else
		{
			$result = (new \Bitrix\Main\Result())->addError(new \Bitrix\Main\Error("Could not find application."));
		}
		if (!$result->isSuccess())
		{
			$arResult["MESSAGES"] = $result->getErrorMessages();
		}
	}
}

$arParams['PAGE_SIZE'] = intval($arParams['PAGE_SIZE']) > 0 ? intval($arParams['PAGE_SIZE']) : 20;
$arParams['EDIT_URL_TPL'] = isset($arParams['EDIT_URL_TPL']) ? trim($arParams['EDIT_URL_TPL']) : SITE_DIR.'marketplace/hook/ap/#id#/';

InitBVar($arParams['SET_TITLE']);

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
	$arResult["ELEMENTS_ROWS"][$ap["ID"]] = [
		"ID" => $ap['ID'],
		"TITLE" => $c->encode($ap['TITLE']),
		"COMMENT" => $c->encode($ap["COMMENT"]),
		"DATE_CREATE" => $c->encode($ap['DATE_CREATE']),
		"DATE_LOGIN" => $c->encode($ap['DATE_LOGIN']),
		"LAST_IP" => $c->encode($ap['LAST_IP']),
	];
}

if($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage('REST_AP_LIST_TITLE'));
}

$this->IncludeComponentTemplate();
