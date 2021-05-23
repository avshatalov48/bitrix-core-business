<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("vote"))
	return;

$arrChannels = Array();
$arrVotes = Array();
$arrQuestions = Array();
$arDiagramType = Array("-" => GetMessage("VOTE_BY_DEFAULT")) + GetVoteDiagramArray();

$rs = CVoteChannel::GetList($v1, $v2, array(), $v3);
while ($arChannel=$rs->Fetch()) 
{
	$arrChannels[$arChannel["SID"]] = "[".$arChannel["SID"]."] ".$arChannel["TITLE"];	

	$rsVotes = CVote::GetList($v1, $v2, array("CHANNEL_ID" => $arChannel["ID"]), $v3);
	while ($arVote = $rsVotes->Fetch())
	{
		$arrVotes[$arVote["ID"]] = "[".$arVote["ID"]."] (".$arChannel["SID"].") ".TruncateText($arVote["TITLE"],40);
	}
}


if (intval($arCurrentValues["VOTE_ID"])>0)
{
	$rsQuestions = CVoteQuestion::GetList($arCurrentValues["VOTE_ID"], $vv1, $vv2, array(), $vv3);
	while ($arQuestion = $rsQuestions->Fetch())
	{
		$QUESTION = ($arQuestion["QUESTION_TYPE"]=="html") ? strip_tags($arQuestion["QUESTION"]) : $arQuestion["QUESTION"];
		$QUESTION = TruncateText($QUESTION, 30);
		$arrQuestions["QUESTION_DIAGRAM_".$arQuestion["ID"]] = array(
			"NAME" => str_replace("#QUESTION#",$QUESTION,GetMessage("VOTE_TEMPLATE_FOR_QUESTION")),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arDiagramType
		);
	}
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"VOTE_ID" => array(
			"NAME" => GetMessage("VOTE_POLL_ID"), 
			"TYPE" => "LIST",
			"VALUES" => $arrVotes,
			"DEFAULT" => "={\$_REQUEST[\"VOTE_ID\"]}",
			"REFRESH" => "Y",
			"PARENT" => "BASE",
			"MULTIPLE"=>"N",
			"ADDITIONAL_VALUES"=>"Y"),
		"VOTE_ALL_RESULTS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("VOTE_ALL_RESULTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"CACHE_TIME" => Array("DEFAULT" => 1200),
	)
);

$arComponentParameters["PARAMETERS"] = array_merge($arComponentParameters["PARAMETERS"], $arrQuestions);
?>
