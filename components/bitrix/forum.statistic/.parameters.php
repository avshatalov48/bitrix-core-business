<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	"PARAMETERS" => array(
		"FID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"TID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_TID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["TID"]}'),
		"TITLE_SEO" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_TITLE_SEO"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["TITLE_SEO"]}'),
		"PERIOD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_PERIOD"),
			"TYPE" => "STRING",
			"DEFAULT" => "600"),
		"SHOW" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SHOW"),
			"TYPE" => "LIST",
			"REFRESH" => "Y",
			"MULTIPLE" => "Y",
			"VALUES" => array(
				"STATISTIC" => GetMessage("F_SHOW_STATISTIC"),
				"BIRTHDAY" => GetMessage("F_SHOW_BIRTHDAY"),
				"USERS_ONLINE" => GetMessage("F_SHOW_USERS_ONLINE")),
			"DEFAULT" => array("USERS_ONLINE")),

		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		"CACHE_TIME" => Array(),
		"CACHE_TIME_USER_STAT" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("F_CACHE_TIME_USER_STAT"),
			"TYPE" => "STRING",
			"DEFAULT" => '600'),
		"WORD_LENGTH" => CForumParameters::GetWordLength(),
	)
);

if(!empty($arCurrentValues["SHOW"]))
{
	$arComponentParameters["PARAMETERS"]["SHOW_FORUM_ANOTHER_SITE"] = array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SHOW_FORUM_ANOTHER_SITE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
	$arComponentParameters["PARAMETERS"]["FORUM_ID"] = CForumParameters::GetForumsMultiSelect(GetMessage("F_FORUM_ID"), "BASE");
}
?>