<?
/*
 * Order deduction dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.order.deduction',
	'.default',
	array(
		"ORDER_ID" => $arResult["ORDER"]["ID"],
		"DEDUCTED" => $arResult["ORDER"]["DEDUCTED"],
		"REASON_UNDO_DEDUCTED" => $arResult["ORDER"]["REASON_UNDO_DEDUCTED"]
	),
	false
);

?>