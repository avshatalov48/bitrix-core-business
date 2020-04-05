<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.order.barcodes',
	'.default',
	array(
		"ORDER_ID" => $arParams["ORDER_ID"],
		"LID" => $arResult["LID"],
		"PRODUCT_DATA" => $arResult["BASKET"][$_REQUEST["product_id"]]
		),
	false
);
?>