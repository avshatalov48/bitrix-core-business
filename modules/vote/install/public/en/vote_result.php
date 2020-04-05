<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Vote results");
$APPLICATION->AddChainItem("Votes", "vote_list.php");
?>
<?$APPLICATION->IncludeComponent("bitrix:voting.result", ".default", Array(
	"VOTE_ID"	=> $_REQUEST["VOTE_ID"],
	)
);?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
