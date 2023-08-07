<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Результаты опросы");
?><?$APPLICATION->IncludeComponent("bitrix:voting.result", "with_description", Array(
	"VOTE_ID"	=>	$_REQUEST["VOTE_ID"]
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>