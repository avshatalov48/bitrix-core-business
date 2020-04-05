<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Orders");
?><?$APPLICATION->IncludeComponent("bitrix:sale.order.full", ".default", Array(
	"ALLOW_PAY_FROM_ACCOUNT"	=>	"Y",
	"SHOW_MENU"	=>	"Y",
	"PATH_TO_BASKET"	=>	"/personal/cart/",
	"PATH_TO_PERSONAL"	=>	"/personal/order/",
	"PATH_TO_AUTH"	=>	"/auth/",
	"PATH_TO_PAYMENT"	=>	"/personal/order/payment/",
	"SET_TITLE"	=>	"Y",
	"DELIVERY_NO_SESSION"	=>	"Y",
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>