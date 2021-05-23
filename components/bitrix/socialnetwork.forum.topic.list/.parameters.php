<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;

$arForum = array();
$db_res = CForumNew::GetList(array(), array());
if ($db_res && ($res = $db_res->GetNext()))
{
	do
	{
		$arForum[intval($res["ID"])] = $res["NAME"];
	}while ($res = $db_res->GetNext());
}

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
			"TYPE" => "LIST",
			"VALUES" => $arForum,
			"DEFAULT" => '0'),
		"USE_DESC_PAGE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USE_DESC_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"SOCNET_GROUP_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SOCNET_GROUP_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SOCNET_GROUP_ID"]}'),
		"USER_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["USER_ID"]}'),
			
		"URL_TEMPLATES_TOPIC_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_list.php"),
		"URL_TEMPLATES_TOPIC" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic.php?TID=#TID#"),
		"URL_TEMPLATES_TOPIC_EDIT" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_TOPIC_NEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_edit.php?MID=#MID#&"),
		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),

		"PAGEN" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGEN"),
			"TYPE" => "STRING",
			"DEFAULT" => intval($GLOBALS["NavNum"] + 1)),
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
			
		"TOPICS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TOPICS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intval(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"))),
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intval(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"))),
		"DATE_FORMAT" => CForumParameters::GetDateFormat(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CForumParameters::GetDateTimeFormat(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"WORD_LENGTH" => CForumParameters::GetWordLength(),
		
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),
	)
);
?>
