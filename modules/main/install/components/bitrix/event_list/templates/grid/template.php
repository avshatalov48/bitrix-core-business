<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */

use Bitrix\Main\Localization\Loc;

$arResult["HEADERS"] = array(
	array("id"=>"ID", "name"=> "ID", "default"=>true, "editable"=>false, "sort" => "ID"),
	array("id"=>"EVENT_NAME", "name"=> Loc::getMessage("EVENTLIST_HEADER_EVENT_NAME"), "default"=>true, "editable"=>false),
	array("id"=>"USER_NAME", "name"=> Loc::getMessage("EVENTLIST_HEADER_NAME"), "default"=>true, "editable"=>false),
	array("id"=>"IP", "name"=> Loc::getMessage("EVENTLIST_HEADER_IP"), "default"=>true, "editable"=>false),
	array("id"=>"DATE_TIME", "name"=> Loc::getMessage("EVENTLIST_HEADER_TIME"), "default"=>true, "editable"=>false)
);

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID" => $arResult["GRID_ID"],
		"HEADERS" => $arResult["HEADERS"],
		"ROWS" => $arResult["ELEMENTS_ROWS"],
		"NAV_OBJECT" => $arResult["NAV"],
		"FILTER" => $arResult["FILTER"],
		"FILTER_TEMPLATE_NAME" => 'tabbed',
		'SORT' => $arResult['SORT'],
		"AJAX_MODE" => "N",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>