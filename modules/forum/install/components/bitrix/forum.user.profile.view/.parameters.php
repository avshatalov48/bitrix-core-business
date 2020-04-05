<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$userProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	
	"PARAMETERS" => Array(
		"UID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_UID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["UID"]}'),
		
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
		"URL_TEMPLATES_PROFILE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile.php?UID=#UID#"),
		"URL_TEMPLATES_USER_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_USER_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "user_list.php"),
		"URL_TEMPLATES_PM_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_list.php"),
		"URL_TEMPLATES_MESSAGE_SEND" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_SEND_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_send.php?TYPE=#TYPE#&UID=#UID#"),
		"URL_TEMPLATES_PM_EDIT" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_EDIT_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_edit.php"),
		"URL_TEMPLATES_SUBSCR_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_SUBSCR_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "subscr_list.php"),
		"URL_TEMPLATES_USER_POST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_USER_POST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "user_post.php?UID=#UID#&mode=#mode#"),

		"FID_RANGE" => CForumParameters::GetForumsMultiSelect(GetMessage("F_FID_RANGE"), "ADDITIONAL_SETTINGS"),
		"DATE_FORMAT" => CComponentUtil::GetDateFormatField(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"SEND_MAIL" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_MAIL"), "ADDITIONAL_SETTINGS", "E"),
		"SEND_ICQ" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_ICQ"), "ADDITIONAL_SETTINGS", "E", "ICQ"),
		"USER_PROPERTY" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("USER_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array()),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),
	)
);


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
?>
