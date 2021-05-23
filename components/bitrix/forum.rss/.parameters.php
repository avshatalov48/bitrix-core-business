<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = Array(
	"PARAMETERS" => array(
		"TYPE_RANGE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_RSS_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"RSS1" => "RSS 0.92",
				"RSS2" => "RSS 2.0",
				"ATOM" => "Atom 0.3",
			),
			"MULTIPLE" => "Y",
			"DEFAULT" => array("RSS1", "RSS2", "ATOM")),
		"FID_RANGE" => CForumParameters::GetForumsMultiSelect(GetMessage("F_FID_RANGE"), "BASE"), 
		"IID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_IID"),
			"TYPE" => "STRING",
			"DEFAULT"=>'={$_REQUEST["IID"]}'),
		"MODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_MODE_TEMPLATE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"link" => GetMessage("F_MODE_TEMPLATE_LINK"),
				"forum" => GetMessage("F_MODE_TEMPLATE_FORUM"), 
				"topic" => GetMessage("F_MODE_TEMPLATE_TOPIC")),
			"DEFAULT" => array("link"),
			"REFRESH" => "Y"),
		
		"URL_TEMPLATES_RSS" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_RSS_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "rss.php?TYPE=#TYPE#&MODE=#MODE#&IID=#IID#"),

		"CACHE_TIME" => array("DEFAULT"=>"86400"),
	)
);

if($arCurrentValues["MODE_TEMPLATE"] != "link")
{
	$arComponentParameters["PARAMETERS"]["TYPE"] = array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_TYPE"),
			"TYPE" => "STRING",
			"DEFAULT"=>'={$_REQUEST["TYPE"]}');
	$arComponentParameters["PARAMETERS"]["COUNT"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT"=>'30');
	$arComponentParameters["PARAMETERS"]["MAX_FILE_SIZE"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MAX_FILE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT"=>'5');
	$arComponentParameters["PARAMETERS"]["TEMPLATES_TITLE_FORUMS"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TEMPLATES_TITLE_FORUMS").GetMessage("F_TEMPLATES_HELP"),
			"TYPE" => "STRING",
			"DEFAULT" => '');
	$arComponentParameters["PARAMETERS"]["TEMPLATES_TITLE_FORUM"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TEMPLATES_TITLE_FORUM").GetMessage("F_TEMPLATES_HELP"),
			"TYPE" => "STRING",
			"DEFAULT" => '');
	$arComponentParameters["PARAMETERS"]["TEMPLATES_TITLE_TOPIC"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TEMPLATES_TITLE_TOPIC").GetMessage("F_TEMPLATES_HELP"),
			"TYPE" => "STRING",
			"DEFAULT" => '');
	
	$arComponentParameters["PARAMETERS"]["DATE_TIME_FORMAT"] = CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS");

	$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_LIST"] = Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#");
	$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_READ"] = Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "read.php?FID=#FID#&TID=#TID#&MID=#MID#");
	$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_READ"] = Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?FID=#FID#&TID=#TID#&MID=#MID#");
	$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_PROFILE_VIEW"] = Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#");
}
?>