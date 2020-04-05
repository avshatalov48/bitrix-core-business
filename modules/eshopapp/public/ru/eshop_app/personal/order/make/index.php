<?
require($_SERVER["DOCUMENT_ROOT"]."#SITE_DIR#eshop_app/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?>
<?$APPLICATION->IncludeComponent("bitrix:eshopapp.order.ajax", "mobile", array(
	"PAY_FROM_ACCOUNT" => "Y",
	"COUNT_DELIVERY_TAX" => "N",
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
	"ALLOW_AUTO_REGISTER" => "Y",
	"SEND_NEW_USER_NOTIFY" => "Y",
	"DELIVERY_NO_AJAX" => "N",
	"TEMPLATE_LOCATION" => "popup",
	"PROP_1" => array(
	),
	"PATH_TO_BASKET" => SITE_DIR."eshop_app/personal/cart/",
	"PATH_TO_PERSONAL" => SITE_DIR."eshop_app/personal/order/order_list.php",
	"PATH_TO_PAYMENT" => SITE_DIR."eshop_app/personal/order/payment/",
	"PATH_TO_ORDER" => SITE_DIR."eshop_app/personal/order/make/",
	"SET_TITLE" => "Y" ,
	"DELIVERY2PAY_SYSTEM" => Array(),
	"SHOW_ACCOUNT_NUMBER" => "Y",
	"DELIVERY_NO_SESSION " => "Y"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>