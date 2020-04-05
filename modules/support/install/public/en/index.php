<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Ticket list");
?><?$APPLICATION->IncludeComponent(
	"bitrix:support.ticket",
	"",
	Array(
		"SEF_MODE" => "N", 
		"TICKETS_PER_PAGE" => "50", 
		"MESSAGES_PER_PAGE" => "20", 
		"SET_PAGE_TITLE" => "Y", 
		"VARIABLE_ALIASES" => Array(
			"ID" => "ID"
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>