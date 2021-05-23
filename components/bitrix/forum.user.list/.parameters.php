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
		"SHOW_USER_STATUS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SHOW_USER_STATUS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),

		"URL_TEMPLATES_MESSAGE_SEND" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_SEND_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_send.php?TYPE=#TYPE#&UID=#UID#"),
		"URL_TEMPLATES_PM_EDIT" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_EDIT_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_edit.php?FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		"URL_TEMPLATES_USER_POST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_USER_POST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "user_post.php?UID=#UID#&mode=#mode#"),
			
		"USERS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_USERS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => "20"),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"DATE_FORMAT" => CComponentUtil::GetDateFormatField(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		
		"SET_TITLE" => Array(),
		"CACHE_TIME" => Array(),
	)
);
if (is_set($arCurrentValues, "SEND_ICQ"))
{
	$arComponentParameters["PARAMETERS"]["SEND_ICQ"] = CForumParameters::GetSendMessageRights(GetMessage("F_SEND_ICQ"), "BASE", "A", "ICQ");
}
if (is_set($arCurrentValues, "SEND_MAIL"))
{
	$arComponentParameters["PARAMETERS"]["SEND_MAIL"] = CForumParameters::GetSendMessageRights(GetMessage("F_SEND_MAIL"), "BASE", "E");
}
?>