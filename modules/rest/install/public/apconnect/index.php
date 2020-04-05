<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"bitrix:rest.apconnect", 
	".default", 
	array(
		'CLIENT_ID' => $_REQUEST["client_id"],
		'CLIENT_STATE' => $_REQUEST["state"],
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>