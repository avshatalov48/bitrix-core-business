<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	
	"PARAMETERS" => Array(

		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"USE_DESC_PAGE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USE_DESC_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),

		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
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
			"DEFAULT" => "message.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		"URL_TEMPLATES_MESSAGE_APPR" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_APPR_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_appr.php?FID=#FID#"),
		"URL_TEMPLATES_TOPIC_NEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_TOPIC_NEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_new.php?FID=#FID#"),
		"URL_TEMPLATES_SUBSCR_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_SUBSCRIBE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "subscr_list.php?FID=#FID#"),
		"URL_TEMPLATES_TOPIC_MOVE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MOVE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_move.php?FID=#FID#&TID=#TID#"),
		"URL_TEMPLATES_RSS" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_RSS_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "rss.php?TYPE=#TYPE#&MODE=#MODE#&IID=#IID#"),

		"PAGEN" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGEN"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal($GLOBALS["NavNum"] + 1)),
		"TOPICS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TOPICS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"))),
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"))),
		"DATE_FORMAT" => CComponentUtil::GetDateFormatField(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"PAGE_NAVIGATION_WINDOW" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_WINDOW"),
			"TYPE" => "STRING",
			"DEFAULT" => "11"),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"WORD_LENGTH" => CForumParameters::GetWordLength(),
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("F_DISPLAY_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"),
		
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),
	)
);
?>
