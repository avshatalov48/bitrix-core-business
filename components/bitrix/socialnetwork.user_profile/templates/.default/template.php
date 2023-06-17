<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}

	$sSiteID = "_".(
		CModule::IncludeModule('extranet')
		&& CExtranet::IsExtranetSite()
			? "extranet"
			: SITE_ID
	);

	$bCanEdit = (
		$arParams['CAN_OWNER_EDIT_DESKTOP']	!= "N"
		|| $GLOBALS["USER"]->IsAdmin()
		|| CSocNetUser::IsCurrentUserModuleAdmin()
	);

	$arDesktopParams = Array(
			"MODE" => "SU",
			"USER_ID" => $arResult["User"]["ID"],
			"USER_ACTIVE" => $arResult["User"]["ACTIVE"],
			"USER_TYPE" => $arResult["User"]["TYPE"],
			"ID" => "sonet_user".$sSiteID."_".$arResult["User"]["ID"],
			"DEFAULT_ID" => "sonet_user".$sSiteID,
			"THUMBNAIL_LIST_SIZE" => $arParams["THUMBNAIL_LIST_SIZE"],
			"LOG_AVATAR_SIZE" => $arParams["LOG_AVATAR_SIZE"],
			"LOG_AVATAR_SIZE_COMMENT" => $arParams["LOG_AVATAR_SIZE_COMMENT"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
			"PM_URL" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_USER" => $arParams["~PATH_TO_USER"],
			"PATH_TO_GROUP" => $arParams["~PATH_TO_GROUP"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
			"TYPE" => "sonet_user",
			"CAN_EDIT" => (($arResult["CurrentUserPerms"]["Operations"]["modifyuser"] && $bCanEdit) ? "Y" : "N"),
			"COLUMNS" => "2",
			"COLUMN_WIDTH_0" => "35%",
			"COLUMN_WIDTH_1" => "65%",
			"GADGETS" => Array("ALL"),
			"GADGETS_FIXED" => array(
				0 => "SONET_USER_LINKS",
				1 => "SONET_USER_DESC",
			),
			"G_SONET_USER_LINKS_SHOW_FEATURES" => ($arParams["USE_MAIN_MENU"] == "Y" ? "Y" : "N"),
			"G_SONET_USER_LINKS_IMAGE" => $arResult["User"]["PersonalPhotoImg"],
			"G_SONET_USER_LINKS_IS_ONLINE" => $arResult['IS_ONLINE'],
			"G_SONET_USER_LINKS_IS_BIRTHDAY" => $arResult['IS_BIRTHDAY'],
			"G_SONET_USER_LINKS_IS_ABSENT" => $arResult['IS_ABSENT'],
			"G_SONET_USER_LINKS_IS_HONOURED" => $arResult['IS_HONOURED'],
			"G_SONET_USER_LINKS_IS_CURRENT_USER" => $arResult["CurrentUserPerms"]["IsCurrentUser"],
			"G_SONET_USER_LINKS_RELATION" => $arResult["CurrentUserPerms"]["Relation"],
			"G_SONET_USER_LINKS_CAN_MESSAGE" => (
				!IsModuleInstalled('mail')
				|| $arResult["User"]["EXTERNAL_AUTH_ID"] != 'email'
					? $arResult["CurrentUserPerms"]["Operations"]["message"]
					: false
			),
			"G_SONET_USER_LINKS_CAN_INVITE_GROUP" => $arResult["CurrentUserPerms"]["Operations"]["invitegroup"],
			"G_SONET_USER_LINKS_CAN_VIEW_PROFILE" => $arResult["CurrentUserPerms"]["Operations"]["viewprofile"],
			"G_SONET_USER_LINKS_CAN_MODIFY_USER" => $arResult["CurrentUserPerms"]["Operations"]["modifyuser"],
			"G_SONET_USER_LINKS_CAN_MODIFY_USER_MAIN" => $arResult["CurrentUserPerms"]["Operations"]["modifyuser_main"],
			"G_SONET_USER_LINKS_URL_MESSAGE_CHAT" => htmlspecialcharsback($arResult["Urls"]["MessageChat"]),
			"G_SONET_USER_LINKS_URL_USER_MESSAGES" => htmlspecialcharsback($arResult["Urls"]["UserMessages"]),
			"G_SONET_USER_LINKS_URL_FRIENDS_DELETE" => htmlspecialcharsback($arResult["Urls"]["FriendsDelete"]),
			"G_SONET_USER_LINKS_URL_FRIENDS_ADD" => htmlspecialcharsback($arResult["Urls"]["FriendsAdd"]),
			"G_SONET_USER_LINKS_URL_REQUEST_GROUP" => htmlspecialcharsback($arResult["Urls"]["RequestGroup"]),
			"G_SONET_USER_LINKS_URL_SUBSCRIBE" => htmlspecialcharsback($arResult["Urls"]["Subscribe"]),
			"G_SONET_USER_LINKS_URL_LOG_SETTINGS" => htmlspecialcharsback($arResult["Urls"]["SubscribeList"]),
			"G_SONET_USER_LINKS_URL_EDIT" => htmlspecialcharsback($arResult["Urls"]["Edit"]),
			"G_SONET_USER_LINKS_URL_SETTINGS" => htmlspecialcharsback($arResult["Urls"]["Settings"]),
			"G_SONET_USER_LINKS_URL_FEATURES" => htmlspecialcharsback($arResult["Urls"]["Features"]),
			"G_SONET_USER_LINKS_URL_EXTMAIL" => htmlspecialcharsback($arResult["Urls"]["ExternalMail"]),
			"G_SONET_USER_LINKS_URL_REQUESTS" => htmlspecialcharsback($arResult["Urls"]["UserRequests"]),
			"G_SONET_USER_LINKS_URL_SUBSCRIBE_LIST" => htmlspecialcharsback($arResult["Urls"]["SubscribeList"]),
			"G_SONET_USER_LINKS_CAN_VIDEOCALL" => $arResult["CurrentUserPerms"]["Operations"]["videocall"],
			"G_SONET_USER_LINKS_URL_VIDEOCALL" => htmlspecialcharsback($arResult["Urls"]["VideoCall"]),
			"G_SONET_USER_LINKS_URL_SECURITY" => htmlspecialcharsback($arResult["Urls"]["Security"]),
			"G_SONET_USER_LINKS_URL_PASSWORDS" => htmlspecialcharsback($arResult["Urls"]["Passwords"]),
			"G_SONET_USER_LINKS_URL_SYNCHRONIZE" => htmlspecialcharsback($arResult["Urls"]["Synchronize"]),
			"G_SONET_USER_LINKS_URL_CODES" => htmlspecialcharsback($arResult["Urls"]["Codes"]),
			"G_SONET_USER_OTP" => $arResult["User"]["OTP"],

			"G_SONET_USER_GROUPS_IS_CURRENT_USER" => $arResult["CurrentUserPerms"]["IsCurrentUser"],
			"G_SONET_USER_GROUPS_GROUPS_LIST" => $arResult["Groups"]["ListFull"],
			"G_SONET_USER_GROUPS_CAN_VIEW_GROUPS" => $arResult["CurrentUserPerms"]["Operations"]["viewgroups"],
			"G_SONET_USER_GROUPS_CAN_CREATE_GROUP" => $arResult["ALLOW_CREATE_GROUP"],
			"G_SONET_USER_GROUPS_URL_GROUPS" => htmlspecialcharsback($arResult["Urls"]["Groups"]),
			"G_SONET_USER_GROUPS_URL_GROUPS_ADD" => htmlspecialcharsback($arResult["Urls"]["GroupsAdd"]),
			"G_SONET_USER_GROUPS_URL_GROUPS_SEARCH" => htmlspecialcharsback($arResult["Urls"]["GroupSearch"]),
			"G_SONET_USER_GROUPS_URL_LOG_GROUPS" => htmlspecialcharsback($arResult["Urls"]["LogGroups"]),

			"G_SONET_USER_TAGS_PAGE_ELEMENTS" => $arParams["SEARCH_TAGS_PAGE_ELEMENTS"],
			"G_SONET_USER_TAGS_PERIOD" => $arParams["SEARCH_TAGS_PERIOD"],
			"G_SONET_USER_TAGS_USER_ID" => $arResult["User"]["ID"],
			"G_SONET_USER_TAGS_PATH_TO_USER_CONTENT_SEARCH" => $arParams["~PATH_TO_USER_CONTENT_SEARCH"],
			"G_SONET_USER_TAGS_FONT_MAX" => $arParams["SEARCH_TAGS_FONT_MAX"],
			"G_SONET_USER_TAGS_FONT_MIN" => $arParams["SEARCH_TAGS_FONT_MIN"],
			"G_SONET_USER_TAGS_COLOR_NEW" => $arParams["SEARCH_TAGS_COLOR_NEW"],
			"G_SONET_USER_TAGS_COLOR_OLD" => $arParams["SEARCH_TAGS_COLOR_OLD"],
			"G_SONET_USER_ACTIVITY_PATH_TO_POST" => $arParams["~PATH_TO_POST"],
			"G_SONET_USER_ACTIVITY_PATH_TO_POST_EDIT" => $arParams["~PATH_TO_POST_EDIT"],
			"G_SONET_USER_ACTIVITY_LIST_URL" => htmlspecialcharsback($arResult["Urls"]["Log"])
	);

	if (CSocNetUser::IsFriendsAllowed() && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()))
	{
		$arDesktopParams["G_SONET_USER_FRIENDS_FRIENDS_COUNT"] = $arResult["Friends"]["Count"];
		$arDesktopParams["G_SONET_USER_FRIENDS_CAN_VIEW_FRIENDS"] = $arResult["CurrentUserPerms"]["Operations"]["viewfriends"];
		$arDesktopParams["G_SONET_USER_FRIENDS_FRIENDS_LIST"] = $arResult["Friends"]["List"];
		$arDesktopParams["G_SONET_USER_FRIENDS_URL_FRIENDS"] = htmlspecialcharsback($arResult["Urls"]["Friends"]);
		$arDesktopParams["G_SONET_USER_FRIENDS_URL_SEARCH"] = htmlspecialcharsback($arResult["Urls"]["Search"]);
		$arDesktopParams["G_SONET_USER_FRIENDS_URL_LOG_USERS"] = htmlspecialcharsback($arResult["Urls"]["LogUsers"]);
		$arDesktopParams["G_SONET_USER_FRIENDS_IS_CURRENT_USER"] = $arResult["CurrentUserPerms"]["IsCurrentUser"];

		$arDesktopParams["G_SONET_USER_BIRTHDAY_USER_ID"] = $arResult["User"]["ID"];
		$arDesktopParams["G_SONET_USER_BIRTHDAY_IS_CURRENT_USER"] = $arResult["CurrentUserPerms"]["IsCurrentUser"];
		$arDesktopParams["G_SONET_USER_BIRTHDAY_PAGE_VAR"] = $arParams["PAGE_VAR"];
		$arDesktopParams["G_SONET_USER_BIRTHDAY_USER_VAR"] = $arParams["USER_VAR"];
	}

	if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
	{
		$arDesktopParams["G_SONET_USER_DESC_USER_NAME"] = $arResult["User"]["NAME_FORMATTED"];
		$arDesktopParams["G_SONET_USER_DESC_CAN_VIEW_PROFILE"] = $arResult["CurrentUserPerms"]["Operations"]["viewprofile"];
		$arDesktopParams["G_SONET_USER_DESC_CAN_VIEW_CONTACTS"] = $arResult["CurrentUserPerms"]["Operations"]["viewcontacts"];
		$arDesktopParams["G_SONET_USER_DESC_FIELDS_MAIN_SHOW"] = $arResult["UserFieldsMain"]["SHOW"];
		$arDesktopParams["G_SONET_USER_DESC_FIELDS_MAIN_DATA"] = $arResult["UserFieldsMain"]["DATA"];
		$arDesktopParams["G_SONET_USER_DESC_PROPERTIES_MAIN_SHOW"] = $arResult["UserPropertiesMain"]["SHOW"];
		$arDesktopParams["G_SONET_USER_DESC_PROPERTIES_MAIN_DATA"] = $arResult["UserPropertiesMain"]["DATA"];
		$arDesktopParams["G_SONET_USER_DESC_FIELDS_CONTACT_SHOW"] = $arResult["UserFieldsContact"]["SHOW"];
		$arDesktopParams["G_SONET_USER_DESC_FIELDS_CONTACT_DATA"] = $arResult["UserFieldsContact"]["DATA"];
		$arDesktopParams["G_SONET_USER_DESC_PROPERTIES_CONTACT_SHOW"] = $arResult["UserPropertiesContact"]["SHOW"];
		$arDesktopParams["G_SONET_USER_DESC_PROPERTIES_CONTACT_DATA"] = $arResult["UserPropertiesContact"]["DATA"];
		$arDesktopParams["G_SONET_USER_DESC_FIELDS_PERSONAL_SHOW"] = $arResult["UserFieldsPersonal"]["SHOW"];
		$arDesktopParams["G_SONET_USER_DESC_FIELDS_PERSONAL_DATA"] = $arResult["UserFieldsPersonal"]["DATA"];
		$arDesktopParams["G_SONET_USER_DESC_PROPERTIES_PERSONAL_SHOW"] = $arResult["UserPropertiesPersonal"]["SHOW"];
		$arDesktopParams["G_SONET_USER_DESC_PROPERTIES_PERSONAL_DATA"] = $arResult["UserPropertiesPersonal"]["DATA"];
		$arDesktopParams["G_SONET_USER_DESC_OTP"] = $arResult["User"]["OTP"];
		$arDesktopParams["G_SONET_USER_DESC_EMAIL_FORWARD_TO"] = (isset($arResult["User"]["EMAIL_FORWARD_TO"]) ? $arResult["User"]["EMAIL_FORWARD_TO"] : array());

		if (
			array_key_exists("RATING_ID_ARR", $arParams)
			&& is_array($arParams["RATING_ID_ARR"])
			&& count($arParams["RATING_ID_ARR"]) > 0
			&& array_key_exists("RatingMultiple", $arResult)
		)
			$arDesktopParams["G_SONET_USER_DESC_RATING_MULTIPLE"] = $arResult["RatingMultiple"];
		elseif (intval($arParams["RATING_ID"]) > 0 && array_key_exists("Rating", $arResult))
		{
			$arDesktopParams["G_SONET_USER_DESC_RATING_NAME"] = $arResult["Rating"]["NAME"];
			$arDesktopParams["G_SONET_USER_DESC_RATING_VALUE"] = $arResult["User"]["RATING_".$arParams["RATING_ID"]."_CURRENT_VALUE"];
		}

		if (CModule::IncludeModule('intranet'))
		{
			$arDesktopParams["G_SONET_USER_HEAD_USER_ID"] = $arResult["User"]["ID"];
			$arDesktopParams["G_SONET_USER_HONOUR_USER_ID"] = $arResult["User"]["ID"];
			$arDesktopParams["G_SONET_USER_HONOUR_NUM_ENTRIES"] = 10;
			$arDesktopParams["G_SONET_USER_ABSENCE_USER_ID"] = $arResult["User"]["ID"];
			$arDesktopParams["G_SONET_USER_ABSENCE_IBLOCK_ID"] = $arParams["CALENDAR_USER_IBLOCK_ID"];
			$arDesktopParams["G_SONET_USER_DESC_MANAGERS"] = $arResult["MANAGERS"];
			$arDesktopParams["G_SONET_USER_DESC_DEPARTMENTS"] = $arResult["DEPARTMENTS"];
		}

		if (CModule::IncludeModule('mail'))
		{
			$arDesktopParams["G_SONET_USER_LINKS_EXTERNAL_AUTH_ID"] = $arResult["User"]["EXTERNAL_AUTH_ID"];
		}

		if($arResult["tasks"]["SHOW"])
		{
			$arDesktopParams["G_TASKS_TITLE"] = $arResult["ActiveFeatures"]["tasks"];
			$arDesktopParams["G_TASKS_SHOW"] = "Y";
			$arDesktopParams["G_TASKS_SHOW_TITLE"] = "N";
			$arDesktopParams["G_TASKS_SHOW_FOOTER"] = "N";
			$arDesktopParams["G_TASKS_TEMPLATE_NAME"] = ".default";
			$arDesktopParams["G_TASKS_OWNER_ID"] = $arResult["User"]["ID"];
			$arDesktopParams["G_TASKS_TASK_TYPE"] = 'user';
			$arDesktopParams["G_TASKS_ITEMS_COUNT"] = 10;
			$arDesktopParams["G_TASKS_PAGE_VAR"] = $arParams["PAGE_VAR"];
			$arDesktopParams["G_TASKS_GROUP_VAR"] = $arParams["GROUP_VAR"];
			$arDesktopParams["G_TASKS_VIEW_VAR"] = $arParams["VIEW_VAR"];
			$arDesktopParams["G_TASKS_TASK_VAR"] = $arParams["TASK_VAR"];
			$arDesktopParams["G_TASKS_ACTION_VAR"] = $arParams["TASK_ACTION_VAR"];
			$arDesktopParams["G_TASKS_PATH_TO_GROUP_TASKS"] = $arParams["PATH_TO_GROUP_TASKS"];
			$arDesktopParams["G_TASKS_PATH_TO_GROUP_TASKS_TASK"] = $arParams["PATH_TO_GROUP_TASKS_TASK"];
			$arDesktopParams["G_TASKS_PATH_TO_GROUP_TASKS_VIEW"] = $arParams["PATH_TO_GROUP_TASKS_VIEW"];
			$arDesktopParams["G_TASKS_PATH_TO_USER_TASKS"] = $arParams["PATH_TO_USER_TASKS"];
			$arDesktopParams["G_TASKS_PATH_TO_USER_TASKS_TASK"] = $arParams["PATH_TO_USER_TASKS_TASK"];
			$arDesktopParams["G_TASKS_PATH_TO_USER_TASKS_VIEW"] = $arParams["PATH_TO_USER_TASKS_VIEW"];
			$arDesktopParams["G_TASKS_FORUM_ID"] = $arParams["TASK_FORUM_ID"];
		}
		else
			$arDesktopParams["G_TASKS_SHOW"] = "N";

		if($arResult["forum"]["SHOW"])
		{
			$arDesktopParams["G_SONET_FORUM_TITLE"] = $arResult["ActiveFeatures"]["forum"];
			$arDesktopParams["G_SONET_FORUM_SHOW"] = "Y";
			$arDesktopParams["G_SONET_FORUM_FID"] = $arParams["FORUM_ID"];
			$arDesktopParams["G_SONET_FORUM_URL_TEMPLATES_MESSAGE"] = $arParams["~PATH_TO_USER_FORUM_MESSAGE"];
			$arDesktopParams["G_SONET_FORUM_URL_TEMPLATES_TOPIC"] = $arParams["~PATH_TO_USER_FORUM_TOPIC"];
			$arDesktopParams["G_SONET_FORUM_URL_TEMPLATES_USER"] = $arParams["~PATH_TO_USER"];
			$arDesktopParams["G_SONET_FORUM_DATE_TIME_FORMAT"] = $arParams["DATE_TIME_FORMAT"];
			$arDesktopParams["G_SONET_FORUM_CACHE_TYPE"] = $arParams["CACHE_TYPE"];
			$arDesktopParams["G_SONET_FORUM_CACHE_TIME"] = $arParams["CACHE_TIME"];
			$arDesktopParams["G_SONET_FORUM_USER_ID"] = $arParams["ID"];
		}
		else
			$arDesktopParams["G_SONET_FORUM_SHOW"] = "N";

		if($arResult["BLOG"]["SHOW"])
		{
			$arDesktopParams["G_SONET_BLOG_TITLE"] = $arResult["ActiveFeatures"]["blog"];
			$arDesktopParams["G_SONET_BLOG_SHOW"] = "Y";
			$arDesktopParams["G_SONET_BLOG_TEMPLATE_NAME"] = ".default";
			$arDesktopParams["G_SONET_BLOG_PATH_TO_BLOG"] = $arParams["~PATH_TO_BLOG"];
			$arDesktopParams["G_SONET_BLOG_PATH_TO_POST"] = $arParams["~PATH_TO_POST"];
			$arDesktopParams["G_SONET_BLOG_PATH_TO_GROUP_BLOG_POST"] = $arParams["~PATH_TO_GROUP_BLOG_POST"];
			$arDesktopParams["G_SONET_BLOG_PATH_TO_USER"] = $arParams["~PATH_TO_USER"];
			$arDesktopParams["G_SONET_BLOG_PATH_TO_SMILE"] = $arParams["PATH_TO_BLOG_SMILE"];
			$arDesktopParams["G_SONET_BLOG_CACHE_TYPE"] = $arParams["CACHE_TYPE"];
			$arDesktopParams["G_SONET_BLOG_CACHE_TIME"] = $arParams["CACHE_TIME"];
			$arDesktopParams["G_SONET_BLOG_BLOG_VAR"] = $arParams["VARIABLE_ALIASES"]["blog"];
			$arDesktopParams["G_SONET_BLOG_POST_VAR"] = $arParams["VARIABLE_ALIASES"]["post_id"];
			$arDesktopParams["G_SONET_BLOG_USER_VAR"] = $arParams["VARIABLE_ALIASES"]["user_id"];
			$arDesktopParams["G_SONET_BLOG_PAGE_VAR"] = $arParams["VARIABLE_ALIASES"]["page"];
			$arDesktopParams["G_SONET_BLOG_DATE_TIME_FORMAT"] = $arParams["DATE_TIME_FORMAT"];
			$arDesktopParams["G_SONET_BLOG_USER_ID"] =  $arParams["ID"];
			$arDesktopParams["G_SONET_BLOG_GROUP_ID"] = $arParams["BLOG_GROUP_ID"];
			$arDesktopParams["G_SONET_BLOG_ALLOW_POST_CODE"] = $arParams["BLOG_ALLOW_POST_CODE"];
		}
		else
			$arDesktopParams["G_SONET_BLOG_SHOW"] = "N";

	}
	?>
	<div><?$APPLICATION->IncludeComponent(
		"bitrix:desktop",
		"",
		$arDesktopParams,
		false,
		array("HIDE_ICONS" => "Y")
	);?></div>
	<?
}
?>