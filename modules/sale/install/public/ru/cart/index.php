<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?><?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", ".default", Array(
	"COLUMNS_LIST"	=>	array(
		0	=>	"NAME",
		1	=>	"PRICE",
		2	=>	"QUANTITY",
		3	=>	"DELETE",
		4	=>	"DISCOUNT",
	),
	"PATH_TO_ORDER"	=>	"/personal/order/make/",
	"HIDE_COUPON"	=>	"N",
	"SET_TITLE"	=>	"Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>