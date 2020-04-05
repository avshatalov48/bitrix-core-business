<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
	
$arComponentParameters = array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
		"ADMIN_SETTINGS" => array(
			"NAME" => GetMessage("F_ADMIN_SETTINGS"),
		),
	),
	"PARAMETERS" => array(
		"USE_DESC_PAGE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USE_DESC_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"URL_TEMPLATES_FORUMS" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_FORUMS_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "forums.php?GID=#GID#"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "read.php?FID=#FID#&TID=#TID#"),
		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "read.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		"URL_TEMPLATES_MESSAGE_APPR" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_APPR_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_appr.php?FID=#FID#&TID=#TID#"),
		"URL_TEMPLATES_RSS" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_RSS_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "rss.php?TYPE=#TYPE#&MODE=#MODE#&IID=#IID#"),
		
		"GID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_GROUP_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["GID"]}'),
		"FORUMS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_FORUMS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10"))),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"PAGE_NAVIGATION_WINDOW" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_WINDOW"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"FID" => CForumParameters::GetForumsMultiSelect(GetMessage("F_DEFAULT_FID"), "ADDITIONAL_SETTINGS"),
		"DATE_FORMAT" => CComponentUtil::GetDateFormatField(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"WORD_LENGTH" => CForumParameters::GetWordLength(false, "ADDITIONAL_SETTINGS"),

		"SHOW_FORUMS_LIST" => Array(
			"PARENT" => "ADMIN_SETTINGS",
			"NAME" => GetMessage("F_SHOW_FORUMS_LIST"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"SHOW_FORUM_ANOTHER_SITE" => Array(
			"PARENT" => "ADMIN_SETTINGS",
			"NAME" => GetMessage("F_SHOW_FORUM_ANOTHER_SITE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
			
		"SET_NAVIGATION" => CForumParameters::GetSetNavigation(GetMessage("F_SET_NAVIGATION"), "ADDITIONAL_SETTINGS"),
		"SET_TITLE" => Array(),
		"CACHE_TIME"  =>  Array(),
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("F_DISPLAY_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"),
	)
);
?>
