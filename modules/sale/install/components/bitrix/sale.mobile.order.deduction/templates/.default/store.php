<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.order.stores',
	'.default',
	array(
		"PRODUCT_DATA" => $arResult["BASKET"][$_REQUEST["product_id"]]
		),
	false
);

?>