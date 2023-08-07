<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Polls");
?><?$APPLICATION->IncludeComponent(
	"bitrix:voting.form",
	"with_description",
	Array(
		"VOTE_ID" => $_REQUEST["VOTE_ID"], 
		"VOTE_RESULT_TEMPLATE" => "vote_result.php?VOTE_ID=#VOTE_ID#" 
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>