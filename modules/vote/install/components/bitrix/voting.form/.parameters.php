<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("vote"))
	return;

$arrChannels = array();
$arrVotes = Array();

$rs = CVoteChannel::GetList();
while ($arChannel=$rs->Fetch()) 
{
	$arrChannels[$arChannel["SID"]] = "[".$arChannel["SID"]."] ".$arChannel["TITLE"];	

	$rsVotes = CVote::GetList('', '', array("CHANNEL_ID" => $arChannel["ID"]));
	while ($arVote = $rsVotes->Fetch())
	{
		$arrVotes[$arVote["ID"]] = "[".$arVote["ID"]."] (".$arChannel["SID"].") ".TruncateText($arVote["TITLE"],40);
	}
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"VOTE_ID" => array(
			"NAME" => GetMessage("VOTE_POLL_ID"), 
			"TYPE" => "LIST",
			"PARENT" => "BASE",
			"VALUES" => $arrVotes,
			"DEFAULT"=>'={$_REQUEST["VOTE_ID"]}',
			"MULTIPLE"=>"N",
			"ADDITIONAL_VALUES"=>"Y",
		),

		"VOTE_RESULT_TEMPLATE" => array(
			"NAME" => GetMessage("VOTE_RESULT_PAGE"), 
			"TYPE" => "STRING",
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45,
			"DEFAULT" => "vote_result.php?VOTE_ID=#VOTE_ID#"
		),

		"CACHE_TIME" => Array("DEFAULT" => 3600),
	)
);
?>
