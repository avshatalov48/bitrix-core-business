<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;

$arForum = array();
$db_res = CForumNew::GetList(array(), array());
if ($db_res && ($res = $db_res->GetNext()))
{
	do
	{
		$arForum[intVal($res["ID"])] = $res["NAME"];
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
		"MID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MID"]}'),
		"MESSAGE_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_MESSAGE_TYPE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MESSAGE_TYPE"]}'),
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
		
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DEFAULT_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"DEFAULT" => "/bitrix/images/forum/smile/"),
		"PATH_TO_ICON" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DEFAULT_PATH_TO_ICON"),
			"TYPE" => "STRING",
			"DEFAULT" => "/bitrix/images/forum/icons/"),
		"DATE_TIME_FORMAT" => CForumParameters::GetDateTimeFormat(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		
		"AJAX_TYPE" => CForumParameters::GetAjaxType(),
		
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),
	)
);
?>
