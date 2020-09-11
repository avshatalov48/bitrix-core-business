<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$userProp[$val["FIELD_NAME"]] = (empty($val["EDIT_FORM_LABEL"]) ? $val["FIELD_NAME"] : $val["EDIT_FORM_LABEL"]);
}

$hidden = (!is_set($arCurrentValues, "USE_LIGHT_VIEW") || $arCurrentValues["USE_LIGHT_VIEW"] == "Y" ? "Y" : "N");

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", 0, LANGUAGE_ID);
$F_USER_FIELDS = array();
if (!empty($arRes))
	foreach ($arRes as $key => $val)
		$F_USER_FIELDS[$val["FIELD_NAME"]] = (empty($val["EDIT_FORM_LABEL"]) ? $val["FIELD_NAME"] : $val["EDIT_FORM_LABEL"]);

$arComponentParameters = array(
	"GROUPS" => array(
		"TEMPLATE_TEMPLATES_SETTINGS" => array(
			"NAME" => GetMessage("F_TEMPLATE_SETTINGS"),
			"SORT" => 1,
		),
		"ADMIN_SETTINGS" => array(
			"NAME" => GetMessage("F_ADMIN_SETTINGS"),
		),
		"RSS_SETTINGS" => array(
			"NAME" => GetMessage("F_RSS"),
		),
	),

	"PARAMETERS" => array(
		"USE_LIGHT_VIEW" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_USE_LIGHT_VIEW"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"),
		"VARIABLE_ALIASES" => Array(
			"FID" => Array(
				"NAME" => GetMessage("F_FORUM_ID"),
				"DEFAULT" => "FID",
			),
			"TID" => Array(
				"NAME" => GetMessage("F_TOPIC_ID"),
				"DEFAULT" => "TID",
			),
			"MID" => Array(
				"NAME" => GetMessage("F_MESSAGE_ID"),
				"DEFAULT" => "MID",
			),
			"UID" => Array(
				"NAME" => GetMessage("F_USER_ID"),
				"DEFAULT" => "UID",
			),
		),
		
		"SEF_MODE" => array_merge(
			array(
				"index" => array(
					"NAME" => GetMessage("F_INDEX"),
					"DEFAULT" => "index.php",
					"VARIABLES" => array(),
				),
				"list" => array(
					"NAME" => GetMessage("F_LIST"),
					"DEFAULT" => "forum#FID#/",
					"VARIABLES" => array("FID"),
				),
				"read" => array(
					"NAME" => GetMessage("F_READ"),
					"DEFAULT" => "forum#FID#/#TITLE_SEO#",
					"VARIABLES" => array("FID", "TID")
				),
				"message" => array(
					"NAME" => GetMessage("F_MESSAGE"),
					"DEFAULT" => "messages/forum#FID#/message#MID#/#TITLE_SEO#",
					"VARIABLES" => array("FID", "TID", "MID"),
				),
				"help" => array(
					"NAME" => GetMessage("F_HELP"),
					"DEFAULT" => "help/",
					"VARIABLES" => array(),
				),
				"rules" => array(
					"NAME" => GetMessage("F_RULES"),
					"DEFAULT" => "rules/",
					"VARIABLES" => array(),
				),
				"message_appr" => array(
					"NAME" => GetMessage("F_MESSAGE_APPR"),
					"DEFAULT" => "messages/approve/forum#FID#/topic#TID#/",
					"VARIABLES" => array("FID", "TID"),
				),
				"message_move" => array(
					"NAME" => GetMessage("F_MESSAGE_MOVE"),
					"DEFAULT" => "messages/move/forum#FID#/topic#TID#/message#MID#/",
					"VARIABLES" => array("FID", "TID", "MID"),
				),
				"rss" => array(
					"NAME" => GetMessage("F_RSS_PAGE"),
					"DEFAULT" => "rss/#TYPE#/#MODE#/#IID#/",
					"VARIABLES" => array(),
				),
				"search" => array(
					"NAME" => GetMessage("F_SEARCH"),
					"DEFAULT" => "search/",
					"VARIABLES" => array(),
				),
				"subscr_list" => array(
					"NAME" => GetMessage("F_SUBSCR_LIST"),
					"DEFAULT" => "subscribe/",
					"VARIABLES" => array(),
				),
				"active" => array(
					"NAME" => GetMessage("F_ACTIVE"),
					"DEFAULT" => "topic/new/",
					"VARIABLES" => array(),
				),
				"topic_move" => array(
					"NAME" => GetMessage("F_TOPIC_MOVE"),
					"DEFAULT" => "topic/move/forum#FID#/topic#TID#/",
					"VARIABLES" => array("FID", "TID"),
				),
				"topic_new" => array(
					"NAME" => GetMessage("F_TOPIC_NEW"),
					"DEFAULT" => "topic/add/forum#FID#/",
					"VARIABLES" => array("FID"),
				),
				"topic_search" => array(
					"NAME" => GetMessage("F_TOPIC_SEARCH"),
					"DEFAULT" => "topic/search/",
					"VARIABLES" => array(),
				),
				"user_list" => array(
					"NAME" => GetMessage("F_USER_LIST"),
					"DEFAULT" => "users/",
					"VARIABLES" => array(),
				),
				"profile" => array(
					"NAME" => GetMessage("F_PROFILE"),
					"DEFAULT" => "user/#UID#/edit/",
					"VARIABLES" => array("UID"),
				),
				"profile_view" => array(
					"NAME" => GetMessage("F_PROFILE_VIEW"),
					"DEFAULT" => "user/#UID#/",
					"VARIABLES" => array("UID"),
				),
				"user_post" => array(
					"NAME" => GetMessage("F_USER_POST"),
					"DEFAULT" => "user/#UID#/post/#mode#/",
					"VARIABLES" => array("UID", "mode"),
				),
				"message_send" => array(
					"NAME" => GetMessage("F_MESSAGE_SEND"),
					"DEFAULT" => "user/#UID#/send/#TYPE#/",
					"VARIABLES" => array("TYPE", "UID"),
				)
			),
			(
				intval(COption::GetOptionString("forum", "UsePMVersion", "2")) > 0 ?
					array(
						"pm_list" => array(
							"NAME" => GetMessage("F_PM_LIST"),
							"DEFAULT" => "pm/folder#FID#/",
							"VARIABLES" => array("FID"),
						),
						"pm_edit" => array(
							"NAME" => GetMessage("F_PM_EDIT"),
							"DEFAULT" => "pm/folder#FID#/message#MID#/user#UID#/#mode#/",
							"VARIABLES" => array("FID", "MID", "UID", "mode"),
						),
						"pm_read" => array(
							"NAME" => GetMessage("F_PM_READ"),
							"DEFAULT" => "pm/folder#FID#/message#MID#/",
							"VARIABLES" => array("FID", "MID"),
						),
						"pm_search" => array(
							"NAME" => GetMessage("F_PM_SEARCH"),
							"DEFAULT" => "pm/search/",
							"VARIABLES" => array(),
						),
						"pm_folder" => array(
							"NAME" => GetMessage("F_PM_FOLDER"),
							"DEFAULT" => "pm/folders/",
							"VARIABLES" => array(),
						)
					)
					:
					array()
			)
		),
		"CHECK_CORRECT_TEMPLATES" => Array(
			"PARENT" => "SEF_MODE",
			"NAME" => GetMessage("F_CHECK_CORRECT_PATH_TEMPLATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"HIDDEN" => $hidden),
		"FID" => CForumParameters::GetForumsMultiSelect(GetMessage("F_FID"), "BASE"),
		"USER_PROPERTY"=>array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("USER_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(), 
			"HIDDEN" => $hidden),
		"USER_FIELDS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USER_FIELDS"),
			"TYPE" => "LIST",
			"VALUES" => $F_USER_FIELDS,
			"MULTIPLE" => "Y",
			"DEFAULT" => array_keys($F_USER_FIELDS),
			"HIDDEN" => $hidden),
		"HELP_CONTENT" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_HELP_CONTENT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"HIDDEN" => ($GLOBALS["USER"]->IsAdmin() ? $hidden : "Y")),
		"RULES_CONTENT" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_RULES_CONTENT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"HIDDEN" => ($GLOBALS["USER"]->IsAdmin() ? $hidden : "Y")),
/*		"USE_DESC_PAGE_TOPIC" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USE_DESC_PAGE_TOPIC"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"HIDDEN" => $hidden),
*/
		"FORUMS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_FORUMS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intval(COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10"))),
		"TOPICS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TOPICS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intval(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"))),
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")),
		"PATH_TO_AUTH_FORM" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PATH_TO_AUTH_FORM"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"HIDDEN" => $hidden),
		"TIME_INTERVAL_FOR_USER_STAT" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TIME_INTERVAL_FOR_USER_STAT"),
			"TYPE" => "STRING",
			"DEFAULT" => "10",
			"HIDDEN" => $hidden),
		"DATE_FORMAT" => CComponentUtil::GetDateFormatField(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"USE_NAME_TEMPLATE" => Array(
			"NAME" => GetMessage("F_USE_NAME_TEMPLATE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"HIDDEN" => $hidden),
		"NAME_TEMPLATE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"TYPE" => "LIST",
			"NAME" => GetMessage("F_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"HIDDEN" => $hidden),
		"IMAGE_SIZE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_IMAGE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "500"),
		"ATTACH_MODE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_ATTACH_MODE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"THUMB" => GetMessage("F_ATTACH_MODE_THUMB"),
				"NAME" => GetMessage("F_ATTACH_MODE_NAME")
			),
			"MULTIPLE" => "Y",
			"DEFAULT" => array("NAME"),
			"REFRESH" => "Y"
		),
		"ATTACH_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_ATTACH_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "90",
			"HIDDEN" => "Y"),
		"EDITOR_CODE_DEFAULT" => Array(
			"NAME" => GetMessage("F_EDITOR_CODE_DEFAULT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"HIDDEN" => $hidden),

		"SEND_MAIL" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_MAIL"), "ADDITIONAL_SETTINGS", "E") + array("HIDDEN" => $hidden),
/*		"SHOW_USER_STATUS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SHOW_USER_STATUS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),*/
		"SET_NAVIGATION" => CForumParameters::GetSetNavigation(GetMessage("F_SET_NAVIGATION"), "ADDITIONAL_SETTINGS"),
		"SET_TITLE" => Array(),
		"SET_DESCRIPTION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_DESCRIPTION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => $hidden),
		"SET_PAGE_PROPERTY" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_PAGE_PROPERTY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => $hidden),
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("F_DISPLAY_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N",
			// "HIDDEN" => $hidden),

		"USE_RSS" => Array(
			"PARENT" => "RSS_SETTINGS",
			"NAME" => GetMessage("F_RSS_USE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"),

/*		"SHOW_FORUMS_LIST" => Array(
			"PARENT" => "ADMIN_SETTINGS",
			"NAME" => GetMessage("F_SHOW_FORUMS_LIST"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
*/		"SHOW_FORUM_ANOTHER_SITE" => Array(
			"PARENT" => "ADMIN_SETTINGS",
			"NAME" => GetMessage("F_SHOW_FORUM_ANOTHER_SITE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => $hidden),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"CACHE_TIME_USER_STAT" =>  array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("F_CACHE_TIME_USER_STAT"),
			"TYPE" => "STRING",
			"DEFAULT"=> "60"),
		"CACHE_TIME_FOR_FORUM_STAT" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("F_CACHE_TIME_FOR_FORUM_STAT"),
			"TYPE" => "STRING",
			"DEFAULT" => "3600"),
/*		"AJAX_TYPE" => Array(
			"PARENT" => "AJAX_SETTINGS",
			"NAME" => GetMessage("F_AJAX_TYPE_DIALOG"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"AJAX_MODE" => Array(),
*/	),
);
if (IsModuleInstalled("search"))
{
	$arComponentParameters["GROUPS"]["SEARCH_TEMPLATES_SETTINGS"] = array(
		"NAME" => GetMessage("F_SEARCH_SETTINGS"),
		"SORT" => 1);
	$arComponentParameters["PARAMETERS"]["RESTART"] = array(
		"PARENT" => "SEARCH_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("SEARCH_RESTART"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N");
	$arComponentParameters["PARAMETERS"]["NO_WORD_LOGIC"] = array(
		"PARENT" => "SEARCH_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("CP_BSP_NO_WORD_LOGIC"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N");
}

$arComponentParameters["PARAMETERS"]["DATE_FORMAT"]["HIDDEN"] = $hidden;
$arComponentParameters["PARAMETERS"]["DATE_TIME_FORMAT"]["HIDDEN"] = $hidden;
$arComponentParameters["PARAMETERS"]["SEND_MAIL"]["HIDDEN"] = $hidden;
$arComponentParameters["PARAMETERS"]["SET_NAVIGATION"]["HIDDEN"] = $hidden;

if($arCurrentValues["USE_RSS"]=="Y")
{
	$arComponentParameters["PARAMETERS"]["RSS_TYPE_RANGE"] = array(
		"PARENT" => "RSS_SETTINGS",
		"NAME" => GetMessage("F_RSS_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"RSS1" => "RSS 0.92",
			"RSS2" => "RSS 2.0",
			"ATOM" => "Atom 0.3"),
		"MULTIPLE" => "Y",
		"DEFAULT" => array("RSS1", "RSS2", "ATOM"), 
		"HIDDEN" => $hidden);
//	$arComponentParameters["PARAMETERS"]["RSS_FID_RANGE"] = CForumParameters::GetForumsMultiSelect(GetMessage("F_RSS_FORUM_RANGE"), "RSS_SETTINGS");
	$arComponentParameters["PARAMETERS"]["RSS_CACHE"] = array(
		"PARENT" => "CACHE_SETTINGS",
		"NAME" => GetMessage("F_RSS_CACHE"),
		"TYPE" => "STRING",
		"DEFAULT"=> "1800", 
		"HIDDEN" => $hidden);
	$arComponentParameters["PARAMETERS"]["RSS_COUNT"] = array(
		"PARENT" => "RSS_SETTINGS",
		"NAME" => GetMessage("F_RSS_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT"=>'30');
	$arComponentParameters["PARAMETERS"]["RSS_TN_TITLE"] = array(
		"PARENT" => "RSS_SETTINGS",
		"NAME" => GetMessage("RSS_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT"=> "", 
		"HIDDEN" => $hidden);
	$arComponentParameters["PARAMETERS"]["RSS_TN_DESCRIPTION"] = array(
		"PARENT" => "RSS_SETTINGS",
		"NAME" => GetMessage("RSS_DESCRIPTION"),
		"TYPE" => "STRING",
		"COLS" => "25",
		"ROWS" => "10",
		"DEFAULT"=> "", 
		"HIDDEN" => $hidden);
}
if (IsModuleInstalled("vote"))
{
	$right = $GLOBALS["APPLICATION"]->GetGroupRight("vote");
	if ($right >= "W")
	{
		$arComponentParameters["GROUPS"]["VOTE_SETTINGS"] = array("NAME" => GetMessage("F_VOTE_SETTINGS"));
		$arComponentParameters["PARAMETERS"]["SHOW_VOTE"] = array(
				"PARENT" => "VOTE_SETTINGS",
				"NAME" => GetMessage("F_SHOW_VOTE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N", 
				"REFRESH" => "Y");
		if ($arCurrentValues["SHOW_VOTE"] == "Y")
		{
			$arVoteChannels = array();
			CModule::IncludeModule("vote");
			$db_res = CVoteChannel::GetList($by = "", $order = "", array("ACTIVE" => "Y"), $is_filtered);
			if ($db_res && $res = $db_res->Fetch())
			{
				do 
				{
					$arVoteChannels[$res["ID"].""] = "[ ".$res["ID"]." ]".$res["TITLE"];
				} while ($res = $db_res->Fetch());
			}
			$arComponentParameters["PARAMETERS"]["VOTE_CHANNEL_ID"] = array(
					"PARENT" => "VOTE_SETTINGS",
					"NAME" => GetMessage("F_VOTE_CHANNEL_ID"),
					"TYPE" => "LIST",
					"VALUES" => $arVoteChannels,
					"DEFAULT" => "", 
					"REFRESH" => "Y");
			reset($arVoteChannels);
			if (intval($arCurrentValues["VOTE_CHANNEL_ID"]) > 0)
				$voteId = intval($arCurrentValues["VOTE_CHANNEL_ID"]);
			else
				$voteId = key($arVoteChannels);
			if (!empty($voteId))
			{
				$arPermissions = CVoteChannel::GetArrayGroupPermission($voteId);
				$arUGroupsEx = array();
				$db_res = CGroup::GetList($by = "c_sort", $order = "asc");
				while($res = $db_res -> Fetch())
				{
					if ((isset($arPermissions[$res["ID"]]) && intval($arPermissions[$res["ID"]]) >= 2) || intval($res["ID"]) == 1):
						$arUGroupsEx[$res["ID"]] = $res["NAME"]."[".$res["ID"]."]";
					endif;
				}
				if (!empty($arUGroupsEx)):
					$arComponentParameters["PARAMETERS"]["VOTE_GROUP_ID"] = array(
						"PARENT" => "VOTE_SETTINGS",
						"NAME" => GetMessage("F_VOTE_GROUP_ID"),
						"TYPE" => "LIST",
						"VALUES" => $arUGroupsEx,
						"DEFAULT" => "",
						"MULTIPLE" => "Y");
					$arComponentParameters["PARAMETERS"]["VOTE_TEMPLATE"] = array(
						"PARENT" => "VOTE_SETTINGS",
						"NAME" => GetMessage("F_VOTE_TEMPLATE"),
						"TYPE" => "LIST",
						"VALUES" => array(
							".default" => GetMessage("F_VOTE_TEMPLATE_DEFAULT"),
							"light" => GetMessage("F_VOTE_TEMPLATE_LIGHT"),
							/*"main_page" => GetMessage("F_VOTE_TEMPLATE_MAIN_PAGE")*/),
						"DEFAULT" => "light",
						"MULTIPLE" => "N",
						"ADDITIONAL_VALUES" => "Y");

					$arVoteUnique = array();
					if (IsModuleInstalled('statistic'))
					{
						$arVoteUnique["1"] = GetMessage("F_VOTE_UNIQUE_SESSION");
					}
					$arVoteUnique["2"] = GetMessage("F_VOTE_UNIQUE_COOKIE_ONLY");
					$arVoteUnique["4"] = GetMessage("F_VOTE_UNIQUE_IP_ONLY");
					$arVoteUnique["8"] = GetMessage("F_VOTE_UNIQUE_USER_ID_ONLY");

					$arComponentParameters["PARAMETERS"]["VOTE_UNIQUE"] = array(
						"VALUES" => $arVoteUnique,
						"PARENT" => "VOTE_SETTINGS",
						"NAME" => GetMessage("F_VOTE_UNIQUE"),
						"TYPE" => "LIST",
						"MULTIPLE" => "Y",
						"ADDITIONAL_VALUES" => "N",
						"DEFAULT" => array(8),
					);
					$arComponentParameters["PARAMETERS"]["VOTE_UNIQUE_IP_DELAY"] = array(
						"DEFAULT" => "10 D",
						"PARENT" => "VOTE_SETTINGS",
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
				endif;
			}
		}
	}
}

// rating
$arComponentParameters["GROUPS"]["RATING_SETTINGS"] = array("NAME" => GetMessage("F_RATING_SETTINGS"));
$arComponentParameters["PARAMETERS"]["SHOW_RATING"] = array(
	"PARENT" => "RATING_SETTINGS",
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
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("F_RATING_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arRatingsList,
		"DEFAULT" => "",
		"MULTIPLE" => "Y",
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
		"PARENT" => "RATING_SETTINGS",
	);	
}
if (!isset($arCurrentValues["ATTACH_MODE"]) && (intval($arCurrentValues["IMAGE_SIZE"]) > 0))
{
	$arComponentParameters["PARAMETERS"]["ATTACH_MODE"]["DEFAULT"] = array("THUMB", "NAME");
	$arComponentParameters["PARAMETERS"]["ATTACH_SIZE"]["DEFAULT"] = $arCurrentValues["IMAGE_SIZE"];
	$arComponentParameters["PARAMETERS"]["ATTACH_SIZE"]["HIDDEN"] = "N";
}
else
{
	if (!is_array($arCurrentValues["ATTACH_MODE"]) || empty($arCurrentValues["ATTACH_MODE"]))
		$arComponentParameters["PARAMETERS"]["ATTACH_MODE"]["DEFAULT"] = $arCurrentValues["ATTACH_MODE"] = array("NAME");
	$arComponentParameters["PARAMETERS"]["ATTACH_SIZE"]["HIDDEN"] = (in_array("THUMB", $arCurrentValues["ATTACH_MODE"]) ? "N" : "Y");
}
?>