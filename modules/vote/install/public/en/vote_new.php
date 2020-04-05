<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Vote");
$APPLICATION->AddChainItem("Votes", "vote_list.php");
?>
<?
$VOTE_ID = $_REQUEST["VOTE_ID"]; 
?>
<?$APPLICATION->IncludeComponent("bitrix:voting.form", ".default", Array(
	"VOTE_ID"	=>	$_REQUEST["VOTE_ID"],
	"VOTE_RESULT_TEMPLATE"	=>	"vote_result.php?VOTE_ID=#VOTE_ID#"
	)
);?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
