<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("vote"))
	return;

/*$arrChannels = Array("-" => GetMessage("VOTE_ALL_CHANNELS"));*/
$arrChannels = array();
$rs = CVoteChannel::GetList($v1, $v2, array(), $v3);
while ($arChannel=$rs->GetNext()) 
{
	$arrChannels[$arChannel["SID"]] = "[".$arChannel["SID"]."] ".html_entity_decode($arChannel["TITLE"]);
}


$arComponentParameters = array(
	"PARAMETERS" => array(
		"CHANNEL_SID" => array(
			"NAME" => GetMessage("VOTE_CHANNEL_SID"), 
			"TYPE" => "LIST",
			"PARENT" => "BASE",
			"VALUES" => $arrChannels,
			"DEFAULT" => "", 
			"MULTIPLE" => "Y",
		),
		"VOTE_FORM_TEMPLATE" => array(
			"NAME" => GetMessage("VOTE_EMPTY_FORM_PAGE"), 
			"TYPE" => "STRING",
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45,
			"DEFAULT" => "vote_new.php?VOTE_ID=#VOTE_ID#"
		),
		"VOTE_RESULT_TEMPLATE" => array(
			"NAME" => GetMessage("VOTE_RESULT_PAGE"), 
			"TYPE" => "STRING",
			"COLS" => 45,
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT" => "vote_result.php?VOTE_ID=#VOTE_ID#"
		)
	)
);
?>
