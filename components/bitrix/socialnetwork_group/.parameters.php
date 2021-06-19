<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("socialnetwork"))
	return false;

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$userProp[$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("SONET_GROUP", 0, LANGUAGE_ID);
$groupProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$groupProp[$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => Array(
			"user_id" => Array(
				"NAME" => GetMessage("SONET_USER_VAR"),
				"DEFAULT" => "user_id",
			),
			"page" => Array(
				"NAME" => GetMessage("SONET_PAGE_VAR"),
				"DEFAULT" => "page",
			),
			"group_id" => Array(
				"NAME" => GetMessage("SONET_GROUP_VAR"),
				"DEFAULT" => "group_id",
			),
			"message_id" => Array(
				"NAME" => GetMessage("SONET_MESSAGE_VAR"),
				"DEFAULT" => "message_id",
			),
		),
		"SEF_MODE" => Array(
			"index" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_INDEX"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array(),
			),

			"search" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_SEARCH"),
				"DEFAULT" => "search.php",
				"VARIABLES" => array(),
			),
			"group_reindex" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_REINDEX"),
				"DEFAULT" => "group_reindex.php",
				"VARIABLES" => array(),
			),
			"group_content_search" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_CONTENT_SEARCH"),
				"DEFAULT" => "group/#group_id#/search/",
				"VARIABLES" => array(),
			),
			"group_subscribe" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_SUBSCRIBE"),
				"DEFAULT" => "group/#group_id#/subscribe/",
				"VARIABLES" => array(),
			),

			"group" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP"),
				"DEFAULT" => "group/#group_id#/",
				"VARIABLES" => array("group_id"),
			),
			"group_search" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_SEARCH"),
				"DEFAULT" => "group/search/",
				"VARIABLES" => array(),
			),
			"group_search_subject" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_SEARCH_SUBJECT"),
				"DEFAULT" => "group/search/#subject_id#/",
				"VARIABLES" => array("subject_id"),
			),
			"group_edit" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_EDIT"),
				"DEFAULT" => "group/#group_id#/edit/",
				"VARIABLES" => array("group_id"),
			),
			"group_delete" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_DELETE"),
				"DEFAULT" => "group/#group_id#/delete/",
				"VARIABLES" => array("group_id"),
			),
			"group_request_search" => array(
				"NAME" => GetMessage("SONET_GROUP_REQUEST_SEARCH"),
				"DEFAULT" => "group/#group_id#/user_search/",
				"VARIABLES" => array("group_id"),
			),
			"group_request_user" => array(
				"NAME" => GetMessage("SONET_GROUP_REQUEST_USER"),
				"DEFAULT" => "group/#group_id#/user/#user_id#/request/",
				"VARIABLES" => array("user_id", "group_id"),
			),
			"user_request_group" => array(
				"NAME" => GetMessage("SONET_USER_REQUEST_GROUP"),
				"DEFAULT" => "group/#group_id#/user_request/",
				"VARIABLES" => array("group_id"),
			),
			"group_requests" => array(
				"NAME" => GetMessage("SONET_GROUP_REQUESTS"),
				"DEFAULT" => "group/#group_id#/requests/",
				"VARIABLES" => array("group_id"),
			),
			"group_requests_out" => array(
				"NAME" => GetMessage("SONET_GROUP_REQUESTS_OUT"),
				"DEFAULT" => "group/#group_id#/requests_out/",
				"VARIABLES" => array("group_id"),
			),
			"group_mods" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_MODS"),
				"DEFAULT" => "group/#group_id#/moderators/",
				"VARIABLES" => array("group_id"),
			),
			"group_users" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_USERS"),
				"DEFAULT" => "group/#group_id#/users/",
				"VARIABLES" => array("group_id"),
			),
			"group_ban" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_BAN"),
				"DEFAULT" => "group/#group_id#/ban/",
				"VARIABLES" => array("group_id"),
			),
			"user_leave_group" => array(
				"NAME" => GetMessage("SONET_USER_LEAVE_GROUP"),
				"DEFAULT" => "group/#group_id#/user_leave/",
				"VARIABLES" => array("group_id"),
			),
			"group_features" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_FEATURES"),
				"DEFAULT" => "group/#group_id#/features/",
				"VARIABLES" => array("group_id"),
			),
			"group_log" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_LOG"),
				"DEFAULT" => "group/#group_id#/log/",
				"VARIABLES" => array("group_id"),
			),
			"group_photo" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_PHOTO"),
				"DEFAULT" => "group/#group_id#/photo/",
				"VARIABLES" => array("group_id"),
			),
			"group_calendar" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_CALENDAR"),
				"DEFAULT" => "group/#group_id#/calendar/",
				"VARIABLES" => array("group_id"),
			),
			"group_files" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_FILES"),
				"DEFAULT" => "group/#group_id#/files/#path#",
				"VARIABLES" => array("group_id"),
			),

			"group_blog" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_BLOG"),
				"DEFAULT" => "group/#group_id#/blog/",
				"VARIABLES" => array("group_id"),
			),
			"group_blog_post_edit" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_BLOG_POST_EDIT"),
				"DEFAULT" => "group/#group_id#/blog/edit/#post_id#/",
				"VARIABLES" => array("group_id", "post_id"),
			),
			"group_blog_rss" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_BLOG_RSS"),
				"DEFAULT" => "group/#group_id#/blog/rss/#type#/",
				"VARIABLES" => array("group_id", "type"),
			),
			"group_blog_draft" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_BLOG_DRAFT"),
				"DEFAULT" => "group/#group_id#/blog/draft/",
				"VARIABLES" => array("group_id"),
			),
			"group_blog_post" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_BLOG_POST"),
				"DEFAULT" => "group/#group_id#/blog/#post_id#/",
				"VARIABLES" => array("group_id", "post_id"),
			),
			"group_blog_moderation" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_BLOG_MODERATION"),
				"DEFAULT" => "group/#group_id#/blog/moderation/",
				"VARIABLES" => array("group_id"),
			),

			"group_forum" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_FORUM"),
				"DEFAULT" => "group/#group_id#/forum/",
				"VARIABLES" => array("group_id"),
			),
			"group_forum_topic_edit" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_FORUM_TOPIC_EDIT"),
				"DEFAULT" => "group/#group_id#/forum/edit/#topic_id#/",
				"VARIABLES" => array("group_id", "topic_id"),
			),
			"group_forum_topic" => array(
				"NAME" => GetMessage("SONET_SEF_GROUP_FORUM_TOPIC"),
				"DEFAULT" => "group/#group_id#/forum/#topic_id#/",
				"VARIABLES" => array("group_id", "topic_id"),
			),

		),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/socialnetwork/smile/",
		),
		"PATH_TO_BLOG_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_BLOG_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/blog/smile/",
		),
		"CACHE_TIME"  =>  Array("DEFAULT" => 3600),
		"SET_TITLE" => Array(),
		"CACHE_TIME_LONG" => array(
			"NAME" => GetMessage("SONET_CACHE_TIME_LONG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "604800",
			"COLS" => 25,
			"PARENT" => "CACHE_SETTINGS",
		),
		"SET_NAV_CHAIN" => Array(
			"NAME" => GetMessage("SONET_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"LOG_SUBSCRIBE_ONLY" => Array(
			"NAME" => GetMessage("SONET_GROUP_LOG_SUBSCRIBE_ONLY"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"LOG_RSS_TTL" => Array(
			"NAME" => GetMessage("SONET_GROUP_LOG_RSS_TTL"),
			"TYPE" => "STRING",
			"DEFAULT" =>"60",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"ITEM_DETAIL_COUNT" => Array(
			"NAME" => GetMessage("SONET_ITEM_DETAIL_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 32,
			"PARENT" => "VISUAL",
		),
		"ITEM_MAIN_COUNT" => Array(
			"NAME" => GetMessage("SONET_ITEM_MAIN_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "6",
			"PARENT" => "VISUAL",
		),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("SONET_DATE_TIME_FORMAT"), "VISUAL"),
		"SHOW_YEAR" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SHOW_YEAR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"Y" => GetMessage("SONET_SHOW_YEAR_VALUE_Y"),
				"M" => GetMessage("SONET_SHOW_YEAR_VALUE_M"),
				"N" => GetMessage("SONET_SHOW_YEAR_VALUE_N")
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "Y"
		),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("SONET_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "VISUAL",
		),
		"SHOW_LOGIN" => Array(
			"NAME" => GetMessage("SONET_SHOW_LOGIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "VISUAL",
		),
		"CAN_OWNER_EDIT_DESKTOP" => Array(
			"NAME" => GetMessage("SONET_CAN_OWNER_EDIT_DESKTOP"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>(IsModuleInstalled("intranet") ? "N" : "Y"),
			"PARENT" => "VISUAL",
		),
		"USER_PROPERTY_MAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTY_MAIN"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_PROPERTY_CONTACT" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTY_CONTACT"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_PROPERTY_PERSONAL" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTY_PERSONAL"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"GROUP_PROPERTY" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_GROUP_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $groupProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"GROUP_USE_BAN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_GROUP_USE_BAN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" => "Y",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SUBSCRIBE" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_SUBSCRIBE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_GROUP_CREATE" => array(
			"NAME" => GetMessage("SONET_SEF_PATH_GROUP_CREATE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SEARCH_EXTERNAL" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_SEARCH_EXTERNAL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_MESSAGES_CHAT" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_CHAT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/messages/chat/#user_id#/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER_CALENDAR" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_USER_CALENDAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/user/#user_id#/calendar/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_MESSAGE_FORM_MESS" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_REPLY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/messages/form/#user_id#/#message_id#/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER_LOG" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_USER_LOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/log/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER_LOG_ENTRY" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_USER_LOG_ENTRY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/log/#log_id#/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER_TASKS_TEMPLATES" => array(
			"NAME" => GetMessage("SONET_SEF_USER_TASKS_TEMPLATES"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/user/#user_id#/tasks/templates/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER_TEMPLATES_TEMPLATE" => array(
			"NAME" => GetMessage("SONET_SEF_USER_TEMPLATES_TEMPLATE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/user/#user_id#/tasks/templates/template/#action#/#template_id#/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER_BLOG_POST_IMPORTANT" => array(
			"NAME" => GetMessage("SONET_SEF_PATH_TO_USER_BLOG_POST_IMPORTANT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "user/#user_id#/blog/important/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
	),
);

if (IsModuleInstalled("video"))
{
	$arComponentParameters["PARAMETERS"]["PATH_TO_VIDEO_CALL"] = array(
			"NAME" => GetMessage("SONET_PATH_TO_VIDEO_CALL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/video/#user_id#/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		);
}

if (IsModuleInstalled("bizproc"))
{
	$arComponentParameters["PARAMETERS"]["PATH_TO_BIZPROC_TASK_LIST"] = array(
			"NAME" => GetMessage("SONET_PATH_TO_BIZPROC_TASK_LIST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/user/#user_id#/bizproc/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		);
	$arComponentParameters["PARAMETERS"]["PATH_TO_BIZPROC_TASK"] = array(
			"NAME" => GetMessage("SONET_PATH_TO_BIZPROC_TASK"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/company/personal/user/#user_id#/bizproc/#id#/",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		);
}
if (CModule::IncludeModule("intranet"))
{
	/* *** EVENT CALENDAR *** */
	$arComponentParameters["GROUPS"]["EVENT_CALENDAR_SETTINGS"] = array("NAME" => GetMessage("SONET_EVENT_CALENDAR_SETTINGS"));

	$calendar2 = (
		(
			!IsModuleInstalled("intranet")
			|| COption::GetOptionString("intranet", "calendar_2", "N") == "Y"
		)
		&& CModule::IncludeModule("calendar")
	);
	if ($calendar2)
	{
		$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_SUPERPOSE"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_ALLOW_SUPERPOSE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_RES_MEETING"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_ALLOW_RES_MEETING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		);
	}
	else
	{
		$arIBlockType = array();
		$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
		while ($arr=$rsIBlockType->Fetch())
		{
			if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
				$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
		}

		$arIBlock=array();
		$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["CALENDAR_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
		while($arr=$rsIBlock->Fetch())
			$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

		$arComponentParameters["PARAMETERS"]["CALENDAR_IBLOCK_TYPE"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_GROUP_IBLOCK_ID"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_GROUP_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_WEEK_HOLIDAYS"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_WEEK_HOLIDAYS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => array(GetMessage('EC_P_MON_F'),GetMessage('EC_P_TUE_F'),GetMessage('EC_P_WEN_F'),GetMessage('EC_P_THU_F'),GetMessage('EC_P_FRI_F'),GetMessage('EC_P_SAT_F'),GetMessage('EC_P_SAN_F')),
			"DEFAULT" => array(5,6),
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_YEAR_HOLIDAYS"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_YEAR_HOLIDAYS"),
			"TYPE" => 'STRING',
			"ROWS" => 3,
			"DEFAULT" => '1.01,7.01,23.02,8.03',
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_WORK_TIME_START"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_WORK_TIME_START"),
			"DEFAULT" => "9"
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_WORK_TIME_END"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_WORK_TIME_END"),
			"DEFAULT" => "19"
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_USER_IBLOCK_ID"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_USER_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock
		);


		// SUPERPOSE
		$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_SUPERPOSE"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_ALLOW_SUPERPOSE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		);

		if ($arCurrentValues["CALENDAR_ALLOW_SUPERPOSE"] == 'Y')
		{
			$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_CAL_IDS"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_SP_CAL_IDS"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arIBlock
			);

			// Cur user
			$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_CUR_USER_CALS"] = Array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_SP_CUR_USER_CALS"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"REFRESH" => "Y",
			);
			// Users
			$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_USERS_CALS"] = Array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_SP_USERS_CALS"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"REFRESH" => "Y",
			);
			// Groups
			$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_GROUPS_CALS"] = Array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_SP_GROUPS_CALS"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"REFRESH" => "Y",
			);
		}

		/* Reserve Meeting Rooms*/
		$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_RES_MEETING"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_ALLOW_RES_MEETING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		);

		if ($arCurrentValues["CALENDAR_ALLOW_RES_MEETING"] != 'N')
		{
			$arComponentParameters["PARAMETERS"]["CALENDAR_RES_MEETING_IBLOCK_ID"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_RES_MEETING_IBLOCK"),
				"TYPE" => "LIST",
				"VALUES" => $arIBlock,
				"REFRESH" => "Y",
			);

			$arComponentParameters["PARAMETERS"]["CALENDAR_PATH_TO_RES_MEETING"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_PATH_TO_RES_MEETING"),
				"DEFAULT" => "/services/res.php?page=meeting&meeting_id=#id#",
			);

			/* Access to Reserve Meeting */
			$arUserGroups = array();
			$dbGroups = CGroup::GetList("NAME", "ASC", array("ACTIVE" => "Y"));
			while ($arGroup = $dbGroups->GetNext())
				$arUserGroups[$arGroup["ID"]] = "[".$arGroup["ID"]."] ".$arGroup["NAME"];

			$arComponentParameters["PARAMETERS"]["CALENDAR_RES_MEETING_USERGROUPS"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_RES_MEETING_USERGROUPS"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arUserGroups,
				"DEFAULT" => Array(1)
			);
		}
		/* Reserve Video-Meeting Rooms*/
		$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_VIDEO_MEETING"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_ALLOW_VIDEO_MEETING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		);

		if ($arCurrentValues["CALENDAR_ALLOW_VIDEO_MEETING"] != 'N')
		{
			$arComponentParameters["PARAMETERS"]["CALENDAR_VIDEO_MEETING_IBLOCK_ID"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_VIDEO_MEETING_IBLOCK"),
				"TYPE" => "LIST",
				"VALUES" => $arIBlock,
				"REFRESH" => "Y"
			);

			$arComponentParameters["PARAMETERS"]["CALENDAR_PATH_TO_VIDEO_MEETING"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_PATH_TO_VIDEO_MEETING"),
				"DEFAULT" => "/services/video/",
			);
			$arComponentParameters["PARAMETERS"]["CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL"),
				"DEFAULT" => "/services/video/detail.php?ID=#conf_id#",
			);


			/* Access to Reserve Video-Meeting */
			$arUserGroups = array();
			$dbGroups = CGroup::GetList("NAME", "ASC", array("ACTIVE" => "Y"));
			while ($arGroup = $dbGroups->GetNext())
				$arUserGroups[$arGroup["ID"]] = "[".$arGroup["ID"]."] ".$arGroup["NAME"];

			$arComponentParameters["PARAMETERS"]["CALENDAR_VIDEO_MEETING_USERGROUPS"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_VIDEO_MEETING_USERGROUPS"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arUserGroups,
				"DEFAULT" => Array(1)
			);
		}

		$arComponentParameters["PARAMETERS"]["CALENDAR_REINVITE_PARAMS_LIST"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_REINVITE_PARAMS_LIST"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => array(
				'name' => GetMessage('SONET_CALENDAR_EV_NAME'),
				'desc' => GetMessage('SONET_CALENDAR_EV_DESC'),
				'from' => GetMessage('SONET_CALENDAR_EV_FROM'),
				'to' => GetMessage('SONET_CALENDAR_EV_TO'),
				'location' => GetMessage('SONET_CALENDAR_LOCATION'),
				'guest_list' => GetMessage('SONET_CALENDAR_GUEST_LIST'),
				'repeating' => GetMessage('SONET_CALENDAR_REPEATING'),
				'meet_text' => GetMessage('SONET_CALENDAR_MEET_TEXT'),
				'importance' => GetMessage('SONET_CALENDAR_IMPORTANCE')
			),
			"DEFAULT" => Array("from", "to", "location")
		);
	}
	/* *** END **** EVENT CALENDAR *** */

	// Tasks
	$arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["task_id"] = array(
		"NAME" => GetMessage("SONET_TASK_VAR"),
		"DEFAULT" => "task_id",
	);
	$arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["view_id"] = array(
		"NAME" => GetMessage("SONET_VIEW_VAR"),
		"DEFAULT" => "view_id",
	);
	$arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["action"] = array(
		"NAME" => GetMessage("SONET_ACTION_VAR"),
		"DEFAULT" => "action",
	);

	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["group_tasks"] = array(
		"NAME" => GetMessage("SONET_SEF_GROUP_TASKS"),
		"DEFAULT" => "group/#group_id#/tasks/",
		"VARIABLES" => array("group_id"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["group_tasks_task"] = array(
		"NAME" => GetMessage("SONET_SEF_GROUP_TASKS_TASK"),
		"DEFAULT" => "group/#group_id#/tasks/task/#action#/#task_id#/",
		"VARIABLES" => array("group_id", "action", "task_id"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["group_tasks_view"] = array(
		"NAME" => GetMessage("SONET_SEF_GROUP_TASKS_VIEW"),
		"DEFAULT" => "group/#group_id#/tasks/view/#action#/#view_id#/",
		"VARIABLES" => array("group_id", "action", "view_id"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["group_tasks_report"] = array(
		"NAME" => GetMessage("SONET_SEF_GROUP_TASKS_REPORT"),
		"DEFAULT" => "group/#group_id#/tasks/report/",
		"VARIABLES" => array("group_id"),
	);

	$arComponentParameters["GROUPS"]["TASKS"] = array("NAME" => GetMessage("INT_TASKS_GROUP"));

	if (CModule::IncludeModule("forum"))
	{
		$arForumTask = array();
		$db_resTask = CForumNew::GetListEx();
		if ($db_resTask && $resTask = $db_resTask->GetNext())
		{
			do
			{
				$arForumTask[$resTask["ID"]] = "[".$resTask["ID"]."] ".$resTask["NAME"];
			} while($resTask = $db_resTask->GetNext());
		}

		$arComponentParameters["PARAMETERS"]["TASK_FORUM_ID"] = array(
			"PARENT" => "TASKS",
			"NAME" => GetMessage("INTL_TASK_FORUM_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arForumTask,
			"REFRESH" => "N",
			"MULTIPLE" => "N");
	}
}

if(CModule::IncludeModule("iblock"))
{
	$arIBlockType = array();
	$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while ($arr=$rsIBlockType->Fetch())
	{
		if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
		{
			$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
		}
	}

	$arIBlockGroup=array();
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["FILES_GROUP_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
	while($arr=$rsIBlock->Fetch())
	{
		$arIBlockGroup[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
	}

	$arUGroupsEx = Array();
	$dbUGroups = CGroup::GetList();
	while($arUGroups = $dbUGroups -> Fetch())
	{
		$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
	}

	if (
		IsModuleInstalled("webdav")
		&& (
			!IsModuleInstalled("disk")
			|| !\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false)
		)
	)
	{
		$arComponentParameters["GROUPS"]["WEBDAV_SETTINGS"] = array(
			"NAME" => GetMessage("SONET_WEBDAV_SETTINGS"));
		$arComponentParameters["PARAMETERS"]["FILES_GROUP_IBLOCK_TYPE"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y");
		$arComponentParameters["PARAMETERS"]["FILES_GROUP_IBLOCK_ID"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockGroup);
		$arComponentParameters["PARAMETERS"]["FILES_AUTO_PUBLISH"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_FILES_AUTO_PUBLISH"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N");
		$arComponentParameters["PARAMETERS"]["FILES_USE_AUTH"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_FILES_USE_AUTH"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
		$arComponentParameters["PARAMETERS"]["NAME_FILE_PROPERTY"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_NAME_FILE_PROPERTY"),
			"TYPE" => "STRING",
			"DEFAULT" => "FILE");
		$arComponentParameters["PARAMETERS"]["FILES_UPLOAD_MAX_FILESIZE"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => str_replace("#upload_max_filesize#", ini_get('upload_max_filesize'), GetMessage("SONET_UPLOAD_MAX_FILESIZE")),
			"TYPE" => "STRING",
			"DEFAULT" => intval(ini_get('upload_max_filesize')));
		$arComponentParameters["PARAMETERS"]["FILES_UPLOAD_MAX_FILE"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_UPLOAD_MAX_FILE"),
			"TYPE" => "STRING",
			"DEFAULT" => 2);
		if (IsModuleInstalled("forum"))
		{
			$arComponentParameters["PARAMETERS"]["FILES_USE_COMMENTS"] = array(
					"PARENT" => "WEBDAV_SETTINGS",
					"NAME" => GetMessage("SONET_USE_COMMENTS"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"REFRESH" => "Y");

			if ($arCurrentValues["FILES_USE_COMMENTS"]=="Y")
			{
				$arForum = array();
				$fid = 0;
				if (CModule::IncludeModule("forum"))
				{
					$db_res = CForumNew::GetList(array(), array());
					if ($db_res && ($res = $db_res->GetNext()))
					{
						do
						{
							$arForum[intval($res["ID"])] = $res["NAME"];
							$fid = intval($res["ID"]);
						}while ($res = $db_res->GetNext());
					}
				}
				$arComponentParameters["PARAMETERS"]["FILES_FORUM_ID"] = Array(
					"PARENT" => "WEBDAV_SETTINGS",
					"NAME" => GetMessage("SONET_FORUM_ID"),
					"TYPE" => "LIST",
					"VALUES" => $arForum,
					"DEFAULT" => $fid);
				$arComponentParameters["PARAMETERS"]["FILES_USE_CAPTCHA"] = Array(
					"PARENT" => "WEBDAV_SETTINGS",
					"NAME" => GetMessage("SONET_USE_CAPTCHA"),
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "Y");
			}
		}
	}
}
if(CModule::IncludeModule("blog"))
{
	$arBlogGroup = array();
	$dbBlogGr = CBlogGroup::GetList();
	while($arBlogGr = $dbBlogGr->Fetch())
	{
		$arBlogGroup[$arBlogGr["ID"]] = "(".$arBlogGr["SITE_ID"].") ".$arBlogGr["NAME"];
	}
	$arComponentParameters["GROUPS"]["BLOG_SETTINGS"] = array(
		"NAME" => GetMessage("SONET_BLOG_SETTINGS"));

	if(!empty($arBlogGroup))
	{
		$arComponentParameters["PARAMETERS"]["BLOG_GROUP_ID"] = array(
			"PARENT" => "BLOG_SETTINGS",
			"NAME" => GetMessage("SONET_BLOG_GROUP_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arBlogGroup,
			"REFRESH" => "N",
			"MULTIPLE" => "N",
			);
	}

	$arComponentParameters["PARAMETERS"]["ALLOW_POST_MOVE"] = Array(
				"NAME" => GetMessage("BPE_ALLOW_POST_MOVE"),
				"TYPE" => "CHECKBOX",
				"MULTIPLE" => "N",
				"VALUE" => "Y",
				"DEFAULT" =>"N",
				"PARENT" => "BLOG_SETTINGS",
				"REFRESH" => "Y",
			);
	$arComponentParameters["PARAMETERS"]["PATH_TO_USER_BLOG_POST"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_USER_POST"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "BLOG_SETTINGS",
	);
	$arComponentParameters["PARAMETERS"]["PATH_TO_USER_BLOG_POST_EDIT"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_USER_POST_EDIT"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "BLOG_SETTINGS",
	);

	if ($arCurrentValues["ALLOW_POST_MOVE"] == "Y")
	{
		$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_POST"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_BLOG_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
		$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_POST_EDIT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_BLOG_POST_EDIT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
		$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_DRAFT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_BLOG_DRAFT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
		$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_BLOG"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_BLOG_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_POST"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_POST_EDIT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_POST_EDIT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_DRAFT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_DRAFT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_BLOG"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BLOG_SETTINGS",
		);
	}

	$arComponentParameters["PARAMETERS"]["PATH_TO_USER_BLOG_POST"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_USER_BLOG_POST"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "BLOG_SETTINGS",
	);

	$arComponentParameters["PARAMETERS"]["BLOG_IMAGE_MAX_WIDTH"] = Array(
				"NAME" => GetMessage("BPC_IMAGE_MAX_WIDTH"),
				"TYPE" => "STRING",
				"DEFAULT" => COption::GetOptionString('blog', 'image_max_width'),
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_IMAGE_MAX_HEIGHT"] = Array(
				"NAME" => GetMessage("BPC_IMAGE_MAX_HEIGHT"),
				"TYPE" => "STRING",
				"DEFAULT" => COption::GetOptionString('blog', 'image_max_height'),
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_COMMENT_AJAX_POST"] = Array(
				"NAME" => GetMessage("BPC_COMMENT_AJAX_POST"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_COMMENT_ALLOW_VIDEO"] = Array(
				"NAME" => GetMessage("BPC_COMMENT_ALLOW_VIDEO"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"] = Array(
				"NAME" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD"),
				"TYPE" => "LIST",
				"VALUES" => Array(
						"A" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD_A"),
						"R" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD_R"),
						"N" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD_N"),
					),
				"MULTIPLE" => "N",
				"DEFAULT" => "A",
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_SHOW_SPAM"] = Array(
				"NAME" => GetMessage("BPC_SHOW_SPAM"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_NO_URL_IN_COMMENTS"] = Array(
				"NAME" => GetMessage("BPC_NO_URL_IN_COMMENTS"),
				"TYPE" => "LIST",
				"VALUES" => Array(
						"" => GetMessage("BPC_NO_URL_IN_COMMENTS_N"),
						"A" => GetMessage("BPC_NO_URL_IN_COMMENTS_A"),
						"L" => GetMessage("BPC_NO_URL_IN_COMMENTS_L"),
					),
				"MULTIPLE" => "N",
				"DEFAULT" => "",
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"] = Array(
				"NAME" => GetMessage("BPC_NO_URL_IN_COMMENTS_AUTHORITY"),
				"TYPE" => "STRING",
				"DEFAULT" => "",
				"PARENT" => "BLOG_SETTINGS",
			);
	$arComponentParameters["PARAMETERS"]["BLOG_ALLOW_POST_CODE"] = Array(
				"NAME" => GetMessage("BPC_ALLOW_POST_CODE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "BLOG_SETTINGS",
				"REFRESH" => "Y",
			);
	if ($arCurrentValues["BLOG_ALLOW_POST_CODE"] != "N")
	{
		$arComponentParameters["PARAMETERS"]["BLOG_USE_GOOGLE_CODE"] = array(
			"NAME" => GetMessage("BPE_USE_GOOGLE_CODE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BLOG_SETTINGS",
		);
	}
	$arComponentParameters["PARAMETERS"]["BLOG_USE_CUT"] = Array(
				"NAME" => GetMessage("BPC_USE_CUT"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"PARENT" => "BLOG_SETTINGS",
			);
}

if (CModule::IncludeModule("forum"))
{
	$arForum = array();
	$db_res = CForumNew::GetListEx();
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			$arForum[$res["ID"]] = "[".$res["ID"]."] ".$res["NAME"];
		} while($res = $db_res->Fetch());
	}

	if (!empty($arForum))
	{
	$arComponentParameters["GROUPS"]["FORUM_SETTINGS"] = array(
		"NAME" => GetMessage("SONET_FORUM_SETTINGS"));
	$arComponentParameters["PARAMETERS"]["FORUM_ID"] = array(
		"PARENT" => "FORUM_SETTINGS",
		"NAME" => GetMessage("SONET_FORUM_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arForum,
		"REFRESH" => "N",
		"MULTIPLE" => "N");
	$arThemesMessages = array(
		"beige" => GetMessage("F_THEME_BEIGE"),
		"blue" => GetMessage("F_THEME_BLUE"),
		"fluxbb" => GetMessage("F_THEME_FLUXBB"),
		"gray" => GetMessage("F_THEME_GRAY"),
		"green" => GetMessage("F_THEME_GREEN"),
		"orange" => GetMessage("F_THEME_ORANGE"),
		"red" => GetMessage("F_THEME_RED"),
		"white" => GetMessage("F_THEME_WHITE"));

	$arThemes = array();
	$dir = trim(preg_replace("'[\\\\/]+'", "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/forum/templates/.default/themes/"));
	if (is_dir($dir) && $directory = opendir($dir)):

		while (($file = readdir($directory)) !== false)
		{
			if ($file != "." && $file != ".." && is_dir($dir.$file))
				$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : mb_strtoupper(mb_substr($file, 0, 1)).mb_strtolower(mb_substr($file, 1)));
		}
		closedir($directory);
	endif;
	$arComponentParameters["PARAMETERS"]["FORUM_THEME"] = array(
		"PARENT" => "FORUM_SETTINGS",
		"NAME" => GetMessage("F_THEMES"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue",
		"ADDITIONAL_VALUES" => "Y");
	$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", 0, LANGUAGE_ID);
	$F_USER_FIELDS = array();
	if (!empty($arRes))
		foreach ($arRes as $key => $val)
			$F_USER_FIELDS[$val["FIELD_NAME"]] = (empty($val["EDIT_FORM_LABEL"]) ? $val["FIELD_NAME"] : $val["EDIT_FORM_LABEL"]);

	$arComponentParameters["PARAMETERS"]["USER_FIELDS_FORUM"] = array(
		"PARENT" => "FORUM_SETTINGS",
		"NAME" => GetMessage("SONET_USER_FIELDS_FORUM"),
		"TYPE" => "LIST",
		"VALUES" => $F_USER_FIELDS,
		"MULTIPLE" => "Y",
		"DEFAULT" => array("UF_FORUM_MESSAGE_DOC"));
	}

	if (IsModuleInstalled("vote"))
	{
		$arComponentParameters["PARAMETERS"]["SHOW_VOTE"] = array(
			"PARENT" => "FORUM_SETTINGS",
			"NAME" => GetMessage("F_SHOW_VOTE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y");

		if ($arCurrentValues["SHOW_VOTE"] == "Y" && CModule::IncludeModule("vote"))
		{
			$rVoteChannels = CAllVoteChannel::GetList('', '', array('ACTIVE' => 'Y'));
			if ($rVoteChannels)
			{
				\Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/components/bitrix/voting.current/.parameters.php");

				$defaultVoteChannel = -1;
				while ($arVoteChannel = $rVoteChannels->Fetch())
				{
					$arVoteChannels[$arVoteChannel['ID']] = "[".$arVoteChannel["ID"]."]".htmlspecialcharsbx($arVoteChannel['TITLE']);
					if ($arVoteChannel['SYMBOLIC_NAME'] == 'SOCIALNETWORK')
						$defaultVoteChannel = $arVoteChannel['ID'];
				}
				$arComponentParameters["PARAMETERS"]["VOTE_CHANNEL_ID"] = array(
					"PARENT" => "FORUM_SETTINGS",
					"NAME" => GetMessage("F_VOTE_CHANNEL_ID"),
					"TYPE" => "LIST",
					"VALUES" => $arVoteChannels,
					"MULTIPLE" => "N",
					"DEFAULT" => $defaultVoteChannel);

				$arComponentParameters["PARAMETERS"]["VOTE_TEMPLATE"] = array(
					"PARENT" => "FORUM_SETTINGS",
					"NAME" => GetMessage("F_VOTE_TEMPLATE"),
					"TYPE" => "LIST",
					"VALUES" => array(
						".default" => GetMessage("F_VOTE_TEMPLATE_DEFAULT"),
						"light" => GetMessage("F_VOTE_TEMPLATE_LIGHT"),
						"main_page" => GetMessage("F_VOTE_TEMPLATE_MAIN_PAGE")),
					"DEFAULT" => "light",
					"MULTIPLE" => "N",
					"ADDITIONAL_VALUES" => "Y");
				$arComponentParameters["PARAMETERS"]["VOTE_UNIQUE"] = array(
					"VALUES" => array(
						"1" => GetMessage("F_VOTE_UNIQUE_SESSION"),
						"2" => GetMessage("F_VOTE_UNIQUE_COOKIE_ONLY"),
						"4" => GetMessage("F_VOTE_UNIQUE_IP_ONLY"),
						"8" => GetMessage("F_VOTE_UNIQUE_USER_ID_ONLY")
					),
					"PARENT" => "FORUM_SETTINGS",
					"NAME" => GetMessage("F_VOTE_UNIQUE"),
					"TYPE" => "LIST",
					"MULTIPLE" => "Y",
					"ADDITIONAL_VALUES" => "N",
					"DEFAULT" => array(1,2,4,8),
				);
				$arComponentParameters["PARAMETERS"]["VOTE_UNIQUE_IP_DELAY"] = array(
					"DEFAULT" => "10 D",
					"PARENT" => "FORUM_SETTINGS",
					"NAME" => GetMessage("F_VOTE_UNIQUE_IP_DELAY"),
					"TYPE" => "CUSTOM",
					"JS_FILE" => "/bitrix/js/vote/comp_props.js",
					"JS_EVENT" => "ComponentPropsVoteIPDelay",
					"JS_LANG" => array(
						"SECONDS" => GetMessage("F_VOTE_SECONDS"),
						"MINUTES" => GetMessage("F_VOTE_MINUTES"),
						"HOURS" => GetMessage("F_VOTE_HOURS"),
						"DAYS" => GetMessage("F_VOTE_DAYS"),
						)
				);
			}
		}
	}
	$arComponentParameters["PARAMETERS"]["FORUM_AJAX_POST"] = array(
		"PARENT" => "FORUM_SETTINGS",
		"NAME" => GetMessage("F_AJAX_POST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N");
}

if (CModule::IncludeModule("iblock"))
{
	if (empty($arIBlockType))
	{
		$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
		while ($arr=$rsIBlockType->Fetch())
		{
			if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
			{
				$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
			}
		}
	}

	$arIBlockGroup=array();
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["PHOTO_GROUP_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
	while($arr=$rsIBlock->Fetch())
	{
		$arIBlockGroup[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
	}

	if (IsModuleInstalled("photogallery"))
	{
		$arComponentParameters["GROUPS"]["PHOTO_SETTINGS"] = array(
			"NAME" => GetMessage("SONET_PHOTO_SETTINGS"));
		$arComponentParameters["PARAMETERS"]["PHOTO_GROUP_IBLOCK_TYPE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_TYPE").GetMessage("SONET_GROUP"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y");
		$arComponentParameters["PARAMETERS"]["PHOTO_GROUP_IBLOCK_ID"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_ID").GetMessage("SONET_GROUP"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockGroup);
		$arComponentParameters["PARAMETERS"]["PHOTO_MODERATION"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_GLOBAL_MODERATE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N");

		$arComponentParameters["PARAMETERS"]["PHOTO_SECTION_PAGE_ELEMENTS"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "15");
		$arComponentParameters["PARAMETERS"]["PHOTO_ELEMENTS_PAGE_ELEMENTS"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENTS_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => '50');
		// $arComponentParameters["PARAMETERS"]["PHOTO_SLIDER_COUNT_CELL"] = array(
			// "PARENT" => "PHOTO_SETTINGS",
			// "NAME" => GetMessage("P_SLIDER_COUNT_CELL"),
			// "TYPE" => "STRING",
			// "DEFAULT" => "3");
		// $arComponentParameters["PARAMETERS"]["CELL_COUNT"] = array(
			// "PARENT" => "PHOTO_SETTINGS",
			// "NAME" => GetMessage("P_TEMPLATE_CELL_COUNT"),
			// "TYPE" => "STRING",
			// "DEFAULT" => "0");
		$arComponentParameters["PARAMETERS"]["PHOTO_ALBUM_PHOTO_THUMBS_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_ALBUM_PHOTO_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "120");
		// $arComponentParameters["PARAMETERS"]["PHOTO_ALBUM_PHOTO_SIZE"] = array(
			// "PARENT" => "PHOTO_SETTINGS",
			// "NAME" => GetMessage("SONET_ALBUM_PHOTO_SIZE"),
			// "TYPE" => "STRING",
			// "DEFAULT" => "150");

		$arComponentParameters["PARAMETERS"]["PHOTO_THUMBNAIL_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "100");
		// $arComponentParameters["PARAMETERS"]["PHOTO_PREVIEW_SIZE"] = array(
			// "PARENT" => "PHOTO_SETTINGS",
			// "NAME" => GetMessage("SONET_PREVIEW_SIZE"),
			// "TYPE" => "STRING",
			// "DEFAULT" => "700");
		$arComponentParameters["PARAMETERS"]["PHOTO_ORIGINAL_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ORIGINAL_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "1280");

		if ($arCurrentValues["PHOTO_UPLOADER_TYPE"])
		{
			$arComponentParameters["PARAMETERS"]["PHOTO_UPLOADER_TYPE"] = array(
				"PARENT" => "PHOTO_SETTINGS",
				"NAME" => GetMessage("P_UPLOADER_TYPE"),
				"TYPE" => "LIST",
				"VALUES" => array(
					"form" => GetMessage("P_UPLOADER_TYPE_FORM_SIMPLE"),
					"applet" => GetMessage("P_UPLOADER_TYPE_APPLET"),
					"flash" => GetMessage("P_UPLOADER_TYPE_FLASH")
				),
				"DEFAULT" => "form",
				"HIDDEN" => $arCurrentValues["PHOTO_UPLOADER_TYPE"] == "form" ? "Y" : "N",
				"REFRESH" => "Y"
			);
		}

		if ($arCurrentValues["PHOTO_UPLOADER_TYPE"] == "applet")
		{
			$arComponentParameters["PARAMETERS"]["PHOTO_APPLET_LAYOUT"] = array(
					"PARENT" => "UPLOADER",
					"NAME" => GetMessage("P_APPLET_LAYOUT"),
					"TYPE" => "LIST",
					"VALUES" => array(
						"extended" => GetMessage("P_APPLET_LAYOUT_EXTENDED"),
						"simple" => GetMessage("P_APPLET_LAYOUT_SIMPLE"),
					),
					"DEFAULT" => "extended");
		}

		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_MIN_PICTURE_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_WATERMARK_MIN_PICTURE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "400");

		$arFiles = array(
			"" => "...");
		$path = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".BX_ROOT."/modules/photogallery/fonts/");
		CheckDirPath($path);
		$handle = opendir($path);
		$file_exist = false;
		if ($handle)
		{
			while($file = readdir($handle))
			{
				if ($file == "." || $file == ".." || !is_file($path.$file))
					continue;
				$file_exist = true;
				$arFiles[$file] = $file;
			}
		}
		if (!$file_exist)
		{
			$arFiles = array(
				"" => GetMessage("SONET_FONTS_NONE"));
		}

		$arComponentParameters["PARAMETERS"]["PHOTO_PATH_TO_FONT"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_FONT"),
			"TYPE" => "LIST",
			"VALUES" => $arFiles,
			"DEFAULT" => array(""),
			"MULTIPLE" => "N");
		$arComponentParameters["PARAMETERS"]["PHOTO_SHOW_WATERMARK"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_SHOW_WATERMARK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_RULES"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_RULES"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"USER" => GetMessage("P_WATERMARK_RULES_USER"),
				"ALL" => GetMessage("P_WATERMARK_RULES_ALL")),
			"DEFAULT" => "USER",
			"REFRESH" => "Y");
		if ($arCurrentValues["PHOTO_WATERMARK_RULES"] == "ALL")
		{
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_TYPE"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"TEXT" => GetMessage("P_WATERMARK_TYPE_TEXT"),
			"PICTURE" => GetMessage("P_WATERMARK_TYPE_PICTURE")),
		"DEFAULT" => "PICTURE",
		"REFRESH" => "Y");
		if ($arCurrentValues["PHOTO_WATERMARK_TYPE"] == "TEXT")
		{
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_TEXT"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_TEXT"),
			"TYPE" => "STRING",
			"VALUES" => "");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_COLOR"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_COLOR"),
			"TYPE" => "STRING",
			"VALUES" => "FF00EE");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_SIZE"),
			"TYPE" => "STRING",
			"VALUES" => "10");
		}
		else
		{
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_FILE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_FILE"),
			"TYPE" => "STRING",
			"VALUES" => "");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_FILE_ORDER"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_FILE_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"usual" => GetMessage("P_WATERMARK_FILE_ORDER_USUAL"),
				"resize" => GetMessage("P_WATERMARK_FILE_ORDER_RESIZE"),
				"repeat" => GetMessage("P_WATERMARK_FILE_ORDER_REPEAT")),
			"DEFAULT" => "usual");

		}

		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_POSITION"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_POSITION"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"tl" => GetMessage("P_WATERMARK_POSITION_TL"),
			"tc" => GetMessage("P_WATERMARK_POSITION_TC"),
			"tr" => GetMessage("P_WATERMARK_POSITION_TR"),
			"ml" => GetMessage("P_WATERMARK_POSITION_ML"),
			"mc" => GetMessage("P_WATERMARK_POSITION_MC"),
			"mr" => GetMessage("P_WATERMARK_POSITION_MR"),
			"bl" => GetMessage("P_WATERMARK_POSITION_BL"),
			"bc" => GetMessage("P_WATERMARK_POSITION_BC"),
			"br" => GetMessage("P_WATERMARK_POSITION_BR")),
		"DEFAULT" => "mc");

			if ($arCurrentValues["PHOTO_WATERMARK_TYPE"] != "TEXT")
				$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_TRANSPARENCY"] = array(
					"PARENT" => "PHOTO_SETTINGS",
					"NAME" => GetMessage("P_WATERMARK_TRANSPARENCY"),
					"TYPE" => "STRING",
					"DEFAULT" => "20"
				);
		}
		$arComponentParameters["PARAMETERS"]["PHOTO_UPLOAD_MAX_FILESIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => str_replace("#upload_max_filesize#", ini_get('upload_max_filesize'), GetMessage("SONET_UPLOAD_MAX_FILESIZE")),
			"TYPE" => "STRING",
			"DEFAULT" => intval(ini_get('upload_max_filesize')));

		$arComponentParameters["PARAMETERS"]["PHOTO_USE_RATING"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_USE_RATING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y"
		);

		if($arCurrentValues["PHOTO_USE_RATING"]=="Y")
		{
			$arComponentParameters["PARAMETERS"]["PHOTO_MAX_VOTE"] = array(
				"PARENT" => "PHOTO_SETTINGS",
				"NAME" => GetMessage("IBLOCK_MAX_VOTE"),
				"TYPE" => "STRING",
				"DEFAULT" => "5");
			$arComponentParameters["PARAMETERS"]["PHOTO_VOTE_NAMES"] = array(
				"PARENT" => "PHOTO_SETTINGS",
				"NAME" => GetMessage("IBLOCK_VOTE_NAMES"),
				"TYPE" => "STRING",
				"VALUES" => array(),
				"MULTIPLE" => "Y",
				"DEFAULT" => array("1","2","3","4","5"),
				"ADDITIONAL_VALUES" => "Y");
			$arComponentParameters["PARAMETERS"]["PHOTO_DISPLAY_AS_RATING"] = array(
				"NAME" => GetMessage("TP_CBIV_DISPLAY_AS_RATING"),
				"PARENT" => "PHOTO_SETTINGS",
				"TYPE" => "LIST",
				"VALUES" => array(
					"rating" => GetMessage("TP_CBIV_RATING"),
					"vote_avg" => GetMessage("TP_CBIV_AVERAGE"),
				),
				"DEFAULT" => "rating");
		}

		if (IsModuleInstalled("blog") || IsModuleInstalled("forum"))
		{
			$arComponentParameters["PARAMETERS"]["PHOTO_USE_COMMENTS"] = array(
					"PARENT" => "PHOTO_SETTINGS",
					"NAME" => GetMessage("SONET_USE_COMMENTS"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"REFRESH" => "Y");

			if ($arCurrentValues["PHOTO_USE_COMMENTS"]=="Y")
			{
				$arr = array();
				if (IsModuleInstalled("blog"))
					$arr["blog"] = GetMessage("SONET_PHOTO_COMMENTS_TYPE_BLOG");
				if (IsModuleInstalled("forum"))
					$arr["forum"] = GetMessage("SONET_PHOTO_COMMENTS_TYPE_FORUM");

				$arComponentParameters["PARAMETERS"]["PHOTO_COMMENTS_TYPE"] = Array(
					"PARENT" => "PHOTO_SETTINGS",
					"NAME" => GetMessage("SONET_PHOTO_COMMENTS_TYPE"),
					"TYPE" => "LIST",
					"VALUES" => $arr,
					"DEFAULT" => "forum",
					"REFRESH" => "Y"
				);

				$arCurrentValues["PHOTO_COMMENTS_TYPE"] = ($arCurrentValues["PHOTO_COMMENTS_TYPE"] == "blog" ? "blog" : "forum");

				if (IsModuleInstalled("blog") && $arCurrentValues["PHOTO_COMMENTS_TYPE"]=="blog")
				{
					$arBlogs = array();
					if(CModule::IncludeModule("blog"))
					{
						$rsBlog = CBlog::GetList();
						while($arBlog=$rsBlog->Fetch())
						{
							$arBlogs[$arBlog["URL"]] = $arBlog["NAME"];
							$url = $arBlog["URL"];
						}
					}

					$arComponentParameters["PARAMETERS"]["PHOTO_BLOG_URL"] = Array(
						"PARENT" => "PHOTO_SETTINGS",
						"NAME" => GetMessage("SONET_PHOTO_BLOG_URL"),
						"TYPE" => "LIST",
						"VALUES" => $arBlogs,
						"DEFAULT" => $url
					);
					$arComponentParameters["PARAMETERS"]["PHOTO_COMMENTS_COUNT"] = Array(
						"PARENT" => "PHOTO_SETTINGS",
						"NAME" => GetMessage("SONET_PHOTO_COMMENTS_COUNT"),
						"TYPE" => "STRING",
						"DEFAULT" => 25
					);
					$arComponentParameters["PARAMETERS"]["PHOTO_PATH_TO_BLOG"] = Array(
						"PARENT" => "PHOTO_SETTINGS",
						"NAME" => GetMessage("SONET_PHOTO_PATH_TO_BLOG"),
						"TYPE" => "STRING",
						"DEFAULT" => ""
					);
				}
				elseif (IsModuleInstalled("forum") && $arCurrentValues["PHOTO_COMMENTS_TYPE"]=="forum")
				{
					$arForum = array();
					$fid = 0;
					if (CModule::IncludeModule("forum"))
					{
						$db_res = CForumNew::GetList(array(), array());
						if ($db_res && ($res = $db_res->GetNext()))
						{
							do
							{
								$arForum[intval($res["ID"])] = $res["NAME"];
								$fid = intval($res["ID"]);
							}while ($res = $db_res->GetNext());
						}
					}
					$arComponentParameters["PARAMETERS"]["PHOTO_FORUM_ID"] = Array(
						"PARENT" => "PHOTO_SETTINGS",
						"NAME" => GetMessage("SONET_FID"),
						"TYPE" => "LIST",
						"VALUES" => $arForum,
						"DEFAULT" => $fid
					);
					$arComponentParameters["PARAMETERS"]["PHOTO_USE_CAPTCHA"] = Array(
						"PARENT" => "PHOTO_SETTINGS",
						"NAME" => GetMessage("SONET_USE_CAPTCHA"),
						"TYPE" => "CHECKBOX",
						"MULTIPLE" => "N",
						"DEFAULT" => "Y"
					);
				}
			}

			$arComponentParameters["PARAMETERS"]["LOG_PHOTO_COUNT"] = Array(
				"NAME" => GetMessage("SONET_LOG_PHOTO_COUNT"),
				"TYPE" => "STRING",
				"MULTIPLE" => "N",
				"DEFAULT" => "6",
				"COLS" => 2,
				"PARENT" => "PHOTO_SETTINGS",
			);
			$arComponentParameters["PARAMETERS"]["LOG_PHOTO_THUMBNAIL_SIZE"] = Array(
				"NAME" => GetMessage("SONET_LOG_PHOTO_THUMBNAIL_SIZE"),
				"TYPE" => "STRING",
				"MULTIPLE" => "N",
				"DEFAULT" => "48",
				"COLS" => 4,
				"PARENT" => "PHOTO_SETTINGS",
			);
		}
	}
}

if (IsModuleInstalled("search"))
{
	$arComponentParameters["GROUPS"]["SEARCH_SETTINGS"] = array(
		"NAME" => GetMessage("SONET_SEARCH_SETTINGS"));
	$arComponentParameters["PARAMETERS"]["SEARCH_DEFAULT_SORT"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_DEFAULT_SORT"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"DEFAULT" => "rank",
		"VALUES" => array(
			"rank" => GetMessage("SONET_SEARCH_DEFAULT_SORT_RANK"),
			"date" => GetMessage("SONET_SEARCH_DEFAULT_SORT_DATE"),
		),
	);
	$arComponentParameters["PARAMETERS"]["SEARCH_PAGE_RESULT_COUNT"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_PAGE_RESULT_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => 10);
	$arComponentParameters["PARAMETERS"]["SHOW_SEARCH_TAGS_CLOUD"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SHOW_SEARCH_TAGS_CLOUD"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "N");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_PAGE_ELEMENTS"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_PAGE_ELEMENTS"),
		"TYPE" => "STRING",
		"DEFAULT" => 100);
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_PERIOD"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_PERIOD"),
		"TYPE" => "STRING",
		"DEFAULT" => "");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_FONT_MAX"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_FONT_MAX"),
		"TYPE" => "STRING",
		"DEFAULT" => "50");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_FONT_MIN"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_FONT_MIN"),
		"TYPE" => "STRING",
		"DEFAULT" => "10");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_COLOR_NEW"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_COLOR_NEW"),
		"TYPE" => "STRING",
		"DEFAULT" => "3E74E6");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_COLOR_OLD"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_COLOR_OLD"),
		"TYPE" => "STRING",
		"DEFAULT" => "C0C0C0");
	$arComponentParameters["PARAMETERS"]["SEARCH_FILTER_NAME"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_FILTER_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "sonet_search_filter");
	$arComponentParameters["PARAMETERS"]["SEARCH_FILTER_DATE_NAME"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_FILTER_DATE_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "sonet_search_filter_date");
	$arComponentParameters["PARAMETERS"]["SEARCH_RESTART"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_RESTART"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "N");
	$arComponentParameters["PARAMETERS"]["SEARCH_USE_LANGUAGE_GUESS"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_USE_LANGUAGE_GUESS"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y");
}

$arComponentParameters["PARAMETERS"]["GROUP_USE_KEYWORDS"] = Array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("SONET_GROUP_USE_KEYWORDS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
);

$arComponentParameters["PARAMETERS"]["GROUP_THUMBNAIL_SIZE"] = Array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("SONET_GROUP_THUMBNAIL_SIZE"),
	"TYPE" => "STRING",
	"MULTIPLE" => "N",
	"DEFAULT" => "",
	"COLS" => 3,
);

$arComponentParameters["PARAMETERS"]["LOG_THUMBNAIL_SIZE"] = Array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("SONET_LOG_THUMBNAIL_SIZE"),
	"TYPE" => "STRING",
	"MULTIPLE" => "N",
	"DEFAULT" => "",
	"COLS" => 3,
);

$arComponentParameters["PARAMETERS"]["LOG_COMMENT_THUMBNAIL_SIZE"] = Array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("SONET_LOG_COMMENT_THUMBNAIL_SIZE"),
	"TYPE" => "STRING",
	"MULTIPLE" => "N",
	"DEFAULT" => "",
	"COLS" => 3,
);

$arComponentParameters["PARAMETERS"]["LOG_NEW_TEMPLATE"] = Array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("SONET_LOG_NEW_TEMPLATE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
);

if (IsModuleInstalled('intranet'))
{
	$arComponentParameters["PARAMETERS"]["PATH_TO_CONPANY_DEPARTMENT"] = array(
		"NAME" => GetMessage("SONET_PATH_TO_CONPANY_DEPARTMENT"),
		"DEFAULT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
		"PARENT" => "URL_TEMPLATES",
	);
}

$arSMThemesMessages = array(
	"grey" => GetMessage("SONET_SM_THEME_GREY"),
	"red" => GetMessage("SONET_SM_THEME_RED"),
	"green" => GetMessage("SONET_SM_THEME_GREEN"),
	"blue" => GetMessage("SONET_SM_THEME_BLUE"),
	"lightblue" => GetMessage("SONET_SM_THEME_LIGHTBLUE"),
	"brown" => GetMessage("SONET_SM_THEME_BROWN"),
);

$arSMThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/socialnetwork.menu/templates/.default/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arSMThemes[$file] = (!empty($arSMThemesMessages[$file]) ? $arSMThemesMessages[$file] : mb_strtoupper(mb_substr($file, 0, 1)).mb_strtolower(mb_substr($file, 1)));
	}
	closedir($directory);
endif;

$tmp_site = ($_REQUEST["site"] <> '' ? $_REQUEST["site"] : ($_REQUEST["src_site"] <> '' ? $_REQUEST["src_site"] : false));

$dbSiteRes = CSite::GetTemplateList($tmp_site);
while($arSiteRes = $dbSiteRes->Fetch())
{
	if (empty($arSiteRes["CONDITION"]))
	{
		$site_template = $arSiteRes["TEMPLATE"];
		break;
	}
}

if (mb_strpos($site_template, "bright") === 0)
	$DefaultSMTheme = "grey";
else
{
	if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite($tmp_site))
		$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id_extranet");
	else
		$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id");

	if ($theme_id <> '')
		$DefaultSMTheme = $theme_id;
	else
		$DefaultSMTheme = "grey";
}

$arComponentParameters["PARAMETERS"]["SM_THEME"] = Array(
	"NAME" => GetMessage("SONET_SM_THEME"),
	"TYPE" => "LIST",
	"VALUES" => $arSMThemes,
	"MULTIPLE" => "N",
	"DEFAULT" => $DefaultSMTheme,
	"PARENT" => "VISUAL",
);

$arComponentParameters["PARAMETERS"]["USE_MAIN_MENU"] = Array(
	"NAME" => GetMessage("SONET_USE_MAIN_MENU"),
	"TYPE" => "CHECKBOX",
	"MULTIPLE" => "N",
	"VALUE" => "Y",
	"DEFAULT" =>"N",
	"REFRESH" => "Y",
	"PARENT" => "VISUAL",
);

if ($arCurrentValues["USE_MAIN_MENU"] == 'Y')
{
	$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
	$arMenu = GetMenuTypes($site);

	$arComponentParameters["PARAMETERS"]["MAIN_MENU_TYPE"] = Array(
		"NAME" => GetMessage("SONET_MAIN_MENU_TYPE"),
		"TYPE" => "LIST",
		"DEFAULT"=>'left',
		"VALUES" => $arMenu,
		"ADDITIONAL_VALUES"	=> "Y",
		"PARENT" => "VISUAL",
		"COLS" => 45
	);
}

$arComponentParameters["PARAMETERS"]["SHOW_RATING"] = array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("SHOW_RATING"),
	"TYPE" => "LIST",
	"VALUES" => Array(
		"" => GetMessage("SHOW_RATING_CONFIG"),
		"Y" => GetMessage("MAIN_YES"),
		"N" => GetMessage("MAIN_NO"),
	),
	"MULTIPLE" => "N",
	"DEFAULT" => "",
	"REFRESH" => "Y"
);

if ($arCurrentValues["SHOW_RATING"] != "N")
{
	$arRatingsList = array();
	$db_res = CRatings::GetList($aSort = array("ID" => "ASC"), array("ACTIVE" => "Y", "ENTITY_ID" => "USER"));
	while ($res = $db_res->Fetch())
		$arRatingsList[$res["ID"]] = "[ ".$res["ID"]." ] ".$res["NAME"];

	$arComponentParameters["PARAMETERS"]["RATING_ID"] = array(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("SONET_RATING_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arRatingsList,
		"DEFAULT" => "",
		"MULTIPLE" => "Y"
	);

	$arComponentParameters["PARAMETERS"]["RATING_TYPE"] = array(
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
		"PARENT" => "VISUAL",
	);
}
?>