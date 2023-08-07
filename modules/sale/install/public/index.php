<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"bitrix:sale.personal.order",
	".default",
	Array(
		"SEF_MODE" => "N", 
		"ORDERS_PER_PAGE" => "20", 
		"SET_TITLE" => "Y" 
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
