<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arGroupList = Array();
$dbGroup = CBlogGroup::GetList(Array("SITE_ID" => "ASC", "NAME" => "ASC"));
while($arGroup = $dbGroup->Fetch())
{
	$arGroupList[$arGroup["ID"]] = "(".$arGroup["SITE_ID"].") [".$arGroup["ID"]."] ".$arGroup["NAME"];
}
	
$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"MESSAGE_COUNT" => Array(
				"NAME" => GetMessage("BMNP_MESSAGE_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 6,
				"PARENT" => "VISUAL",
			),
		"PERIOD_DAYS" => Array(
				"NAME" => GetMessage("BMNP_PERIOD_DAYS"),
				"TYPE" => "STRING",
				"DEFAULT" => 30,
				"PARENT" => "VISUAL",
			),
		"MESSAGE_LENGTH" => Array(
				"NAME" => GetMessage("BMNP_MESSAGE_LENGTH"),
				"TYPE" => "STRING",
				"DEFAULT" => 100,
				"PARENT" => "VISUAL",
			),
		"PREVIEW_WIDTH" => Array(
				"NAME" => GetMessage("BMNP_PREVIEW_WIDTH"),
				"TYPE" => "STRING",
				"DEFAULT" => 100,
				"PARENT" => "VISUAL",
			),		
		"PREVIEW_HEIGHT" => Array(
				"NAME" => GetMessage("BMNP_PREVIEW_HEIGHT"),
				"TYPE" => "STRING",
				"DEFAULT" => 100,
				"PARENT" => "VISUAL",
			),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("BC_DATE_TIME_FORMAT"), "VISUAL"),		
		"PATH_TO_BLOG" => Array(
			"NAME" => GetMessage("BMNP_PATH_TO_BLOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST" => Array(
			"NAME" => GetMessage("BMNP_PATH_TO_POST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("BMNP_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_GROUP_BLOG_POST" => Array(
			"NAME" => GetMessage("BMNP_PATH_TO_GROUP_BLOG_POST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SMILE" => Array(
			"NAME" => GetMessage("BMNP_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BMNP_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"POST_VAR" => Array(
			"NAME" => GetMessage("BMNP_POST_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("BMNP_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BMNP_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"CACHE_TIME"	=>	array("DEFAULT"=>"86400"),
		"GROUP_ID"=>array(
			"NAME" => GetMessage("BLG_GROUP_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arGroupList,
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
			"ADDITIONAL_VALUES" => "Y",
			"PARENT" => "DATA_SOURCE",
		),
		"BLOG_URL" => Array(
			"NAME" => GetMessage("BLG_BLOG_URL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),

	)
);
?>