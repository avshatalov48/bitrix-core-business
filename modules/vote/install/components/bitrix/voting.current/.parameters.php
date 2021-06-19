<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("vote"))
	return;

$arrChannels = Array("-" =>GetMessage("VOTE_SELECT_DEFAULT"));
$rs = CVoteChannel::GetList();
while ($arChannel=$rs->GetNext()) 
{
	$arrChannels[$arChannel["SID"]] = "[".$arChannel["SID"]."] ".html_entity_decode($arChannel["TITLE"]);
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"CHANNEL_SID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("VOTE_CHANNEL_SID"), 
			"TYPE" => "LIST",
			"VALUES" => $arrChannels,
			"DEFAULT" => ""
		),
		"VOTE_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("VOTE_VOTE_ID"), 
			"TYPE" => "STRING", 
			"DEFAULT" => ""
		),
		"VOTE_ALL_RESULTS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("VOTE_ALL_RESULTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CACHE_TIME" => Array("DEFAULT" => 3600),
		"AJAX_MODE" => array(),
	)
);
/* GetMessage("F_VOTE_UNIQUE_SESSION");
GetMessage("F_VOTE_UNIQUE_COOKIE_ONLY");
GetMessage("F_VOTE_UNIQUE_IP_ONLY");
GetMessage("F_VOTE_UNIQUE_USER_ID_ONLY");
GetMessage("F_VOTE_UNIQUE_IP_DELAY");
GetMessage("F_VOTE_UNIQUE");
GetMessage("F_VOTE_SECONDS");
GetMessage("F_VOTE_MINUTES");
GetMessage("F_VOTE_HOURS");
GetMessage("F_VOTE_DAYS"); */
?>