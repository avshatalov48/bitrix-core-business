<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentProps = CComponentUtil::GetComponentProps("bitrix:socialnetwork.forum.topic.last", $arCurrentValues);
$arComponentProps["PARAMETERS"]["TOPICS_COUNT"]["DEFAULT"] = 3;

$arParameters = Array(
		"USER_PARAMETERS"=> Array(
			"TOPICS_COUNT"=>$arComponentProps["PARAMETERS"]["TOPICS_COUNT"],
		),
	);
	
?>
