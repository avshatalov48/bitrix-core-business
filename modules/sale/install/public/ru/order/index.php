<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent("bitrix:sale.personal.order", ".default", Array(
	"SEF_MODE"	=>	"N",
	"ORDERS_PER_PAGE"	=>	"20",
	"PATH_TO_PAYMENT"	=>	"/personal/order/payment/",
	"PATH_TO_BASKET"	=>	"/personal/cart/",
	"SET_TITLE"	=>	"Y",
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>