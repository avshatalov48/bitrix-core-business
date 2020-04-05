<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

$arComponentParameters = array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	"PARAMETERS" => array(
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "LIST",
			"VALUES" => $arForum,
			"DEFAULT" => '0'),
		"TID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_TID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["TID"]}'),
		"MID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MID"]}'),
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
			"DEFAULT" => "topic_edit.php?TID=#TID#&MID=#MID#"),
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
			"DEFAULT" => intVal($GLOBALS["NavNum"] + 1)),
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
		"PAGE_NAVIGATION_SHOW_ALL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_SHOW_ALL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
			
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DEFAULT_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"DEFAULT" => "/bitrix/images/forum/smile/"),

		"WORD_LENGTH" => CForumParameters::GetWordLength(),
		"IMAGE_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_IMAGE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => 500),

		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")),

		"DATE_FORMAT" => CForumParameters::GetDateFormat(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CForumParameters::GetDateTimeFormat(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		
		"AJAX_TYPE" => CForumParameters::GetAjaxType(),
		"SET_TITLE" => Array(),
		"CACHE_TIME" => Array(),
	)
);
$arComponentParameters["PARAMETERS"]["SHOW_RATING"] = array(
	"NAME" => GetMessage("SHOW_RATING"),
	"TYPE" => "LIST",
	"VALUES" => Array(
		"" => GetMessage("SHOW_RATING_CONFIG"),
		"Y" => GetMessage("MAIN_YES"),
		"N" => GetMessage("MAIN_NO"),
	),
	"MULTIPLE" => "N",
	"DEFAULT" => "",
	"PARENT" => "ADDITIONAL_SETTINGS",
	"REFRESH" => "Y"
);
if ($arCurrentValues["SHOW_RATING"] != "N")
{
	$arRatingsList = array();
	$db_res = CRatings::GetList($aSort = array("ID" => "ASC"), array("ACTIVE" => "Y", "ENTITY_ID" => "USER"));
	while ($res = $db_res->Fetch())
		$arRatingsList[$res["ID"]] = "[ ".$res["ID"]." ] ".$res["NAME"];
	
	$arComponentParameters["PARAMETERS"]["RATING_ID"] = array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("F_RATING_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arRatingsList,
		"DEFAULT" => "",
		"REFRESH" => "N"
	);
	$arComponentParameters["PARAMETERS"]["RATING_TYPE"] = Array(
		"NAME" => GetMessage("RATING_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => Array(
			"" => GetMessage("RATING_TYPE_CONFIG"),
			"like" => GetMessage("RATING_TYPE_LIKE_TEXT"),
			"like_graphic" => GetMessage("RATING_TYPE_LIKE_GRAPHIC"),
			"standart_text" => GetMessage("RATING_TYPE_STANDART_TEXT"),
			"standart" => GetMessage("RATING_TYPE_STANDART_GRAPHIC"),
		),
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
}
?>