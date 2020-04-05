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
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
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
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_MESSAGE_MOVE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_MOVE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_move.php?FID=#FID#&TID=#TID#&MID_ARRAY=#MID_ARRAY#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		"URL_TEMPLATES_TOPIC_NEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_TOPIC_NEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_new.php?FID=#FID#"),
		"URL_TEMPLATES_SUBSCR_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_SUBSCR_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "subscr_list.php?FID=#FID#"),
		"URL_TEMPLATES_TOPIC_MOVE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_TOPIC_MOVE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_move.php?FID=#FID#&TID=#TID#"),	
		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"URL_TEMPLATES_PM_EDIT" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_EDIT_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_edit.php"),
		"URL_TEMPLATES_MESSAGE_SEND" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_SEND_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_send.php?UID=#UID#"),
		"URL_TEMPLATES_RSS" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_RSS_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "rss.php?TYPE=#TYPE#&MODE=#MODE#&IID=#IID#"),
		"URL_TEMPLATES_USER_POST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_USER_POST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "user_post.php?UID=#UID#&mode=#mode#"),
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")),
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
		"PAGE_NAVIGATION_SHOW_ALL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_SHOW_ALL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"), 
		"WORD_LENGTH" => CForumParameters::GetWordLength(),
		"IMAGE_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_IMAGE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => 500),
		"SHOW_FIRST_POST" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SHOW_FIRST_POST"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"AJAX_TYPE" => CForumParameters::GetAjaxType(),
		"SET_PAGE_PROPERTY" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_PAGE_PROPERTY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("F_DISPLAY_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"),
		
		"SET_TITLE" => Array(),
		"CACHE_TIME" => Array(),
	)
);

// rating
$arComponentParameters["GROUPS"]["RATING_SETTINGS"] = array("NAME" => GetMessage("F_RATING_SETTINGS"));
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
	"PARENT" => "RATING_SETTINGS",
);
if ($arCurrentValues["SHOW_RATING"] != "N")
{
	$arRatingsList = array();
	$db_res = CRatings::GetList($aSort = array("ID" => "ASC"), array("ACTIVE" => "Y", "ENTITY_ID" => "USER"));
	while ($res = $db_res->Fetch())
		$arRatingsList[$res["ID"]] = "[ ".$res["ID"]." ] ".$res["NAME"];
	
	$arComponentParameters["PARAMETERS"]["RATING_ID"] = array(
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("F_RATING_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arRatingsList,
		"DEFAULT" => "",
		"REFRESH" => "Y"
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
		"PARENT" => "RATING_SETTINGS",
	);
}


?>