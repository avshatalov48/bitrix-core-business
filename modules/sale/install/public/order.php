<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"bitrix:sale.order.full",
	"",
	Array(
		"PATH_TO_BASKET" => "basket.php", 
		"PATH_TO_PERSONAL" => "index.php", 
		"PATH_TO_AUTH" => "/auth.php", 
		"ALLOW_PAY_FROM_ACCOUNT" => "Y", 
		"SHOW_MENU" => "Y", 
		"SET_TITLE" => "Y" 
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>