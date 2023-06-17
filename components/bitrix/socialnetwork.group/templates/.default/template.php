<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!empty($arResult['FatalError']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.entity.error',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'ENTITY' => 'SONET_GROUP',
			],
		]
	);
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.group.iframe.popup",
		".default",
		array(
			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
			"PATH_TO_GROUP_EDIT" => htmlspecialcharsback($arResult["Urls"]["Edit"]).(mb_strpos($arResult["Urls"]["Edit"], "?") === false ? "?" : "&")."tab=edit",
			"PATH_TO_GROUP_INVITE" => htmlspecialcharsback($arResult["Urls"]["Edit"]).(mb_strpos($arResult["Urls"]["Edit"], "?") === false ? "?" : "&")."tab=invite",
			"ON_GROUP_ADDED" => "BX.DoNothing",
			"ON_GROUP_CHANGED" => "BX.DoNothing",
			"ON_GROUP_DELETED" => "BX.DoNothing"
		),
		null,
		array("HIDE_ICONS" => "Y")
	);

	$sSiteID = "_".(CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite() ? "extranet" : SITE_ID);

	$bCanEdit = (
		$arParams['CAN_OWNER_EDIT_DESKTOP']	!= "N"
		|| $USER->isAdmin()
		|| CSocNetUser::isCurrentUserModuleAdmin()
	);

	$arDesktopParams = array(
			"MODE" => "SG",
			"ID" => "sonet_group".$sSiteID."_".$arResult["Group"]["ID"],
			"DEFAULT_ID" => "sonet_group".$sSiteID,
			"SOCNET_GROUP_ID" => $arParams["GROUP_ID"],
			"THUMBNAIL_LIST_SIZE" => $arParams["THUMBNAIL_LIST_SIZE"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"PM_URL" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
			"PATH_TO_USER" => $arParams["~PATH_TO_USER"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"TYPE" => "sonet_group",
			"CAN_EDIT" => (($arResult["CurrentUserPerms"]["UserCanModifyGroup"] && $bCanEdit) ? "Y" : "N"),
			"COLUMNS" => "2",
			"COLUMN_WIDTH_0" => "65%",
			"COLUMN_WIDTH_1" => "35%",
			"GADGETS" => Array("ALL"),
			"GADGETS_FIXED" => array(
				0 => "SONET_GROUP_LINKS",
				1 => "SONET_GROUP_DESC",
			),
			"G_SONET_GROUP_DESC_ID" => $arResult["Group"]["ID"],
			"G_SONET_GROUP_DESC_NAME" => $arResult["Group"]["~NAME"],
			"G_SONET_GROUP_DESC_DESCRIPTION" => $arResult["Group"]["~DESCRIPTION"],
			"G_SONET_GROUP_DESC_CLOSED" => $arResult["Group"]["CLOSED"],
			"G_SONET_GROUP_DESC_OPENED" => $arResult["Group"]["OPENED"],
			"G_SONET_GROUP_DESC_VISIBLE" => $arResult["Group"]["VISIBLE"],
			"G_SONET_GROUP_DESC_SUBJECT_NAME" => htmlspecialcharsback($arResult["Group"]["~SUBJECT_NAME"]),
			"G_SONET_GROUP_DESC_DATE_CREATE" => $arResult["Group"]["DATE_CREATE"],
			"G_SONET_GROUP_DESC_NUMBER_OF_MEMBERS" => $arResult["Group"]["NUMBER_OF_MEMBERS"],
			"G_SONET_GROUP_DESC_PROPERTIES_SHOW" => $arResult["GroupProperties"]["SHOW"],
			"G_SONET_GROUP_DESC_PROPERTIES_DATA" => $arResult["GroupProperties"]["DATA"],
			"G_SONET_GROUP_DESC_REQUEST_SENT" => $arResult["bShowRequestSentMessage"],

			"G_SONET_GROUP_LINKS_NAME" => $arResult["Group"]["~NAME"],
			"G_SONET_GROUP_LINKS_SHOW_FEATURES" => ($arParams["USE_MAIN_MENU"] == "Y" ? "Y" : "N"),
			"G_SONET_GROUP_LINKS_IMAGE" => $arResult["Group"]["IMAGE_ID_IMG"],
			"G_SONET_GROUP_LINKS_CAN_SPAM_GROUP" => $arResult["CurrentUserPerms"]["UserCanSpamGroup"],
			"G_SONET_GROUP_LINKS_CAN_MODIFY_GROUP" => $arResult["CurrentUserPerms"]["UserCanModifyGroup"],
			"G_SONET_GROUP_LINKS_CAN_MODERATE_GROUP" => $arResult["CurrentUserPerms"]["UserCanModerateGroup"],
			"G_SONET_GROUP_LINKS_CAN_INITIATE" => $arResult["CurrentUserPerms"]["UserCanInitiate"],
			"G_SONET_GROUP_LINKS_USER_ROLE" => $arResult["CurrentUserPerms"]["UserRole"],
			"G_SONET_GROUP_LINKS_INITIATED_BY_TYPE" => $arResult["CurrentUserPerms"]["InitiatedByType"],
			"G_SONET_GROUP_LINKS_USER_IS_MEMBER" => $arResult["CurrentUserPerms"]["UserIsMember"],
			"G_SONET_GROUP_LINKS_USER_IS_AUTO_MEMBER" => $arResult["CurrentUserPerms"]["UserIsAutoMember"],
			"G_SONET_GROUP_LINKS_USER_IS_OWNER" => $arResult["CurrentUserPerms"]["UserIsOwner"],
			"G_SONET_GROUP_LINKS_HIDE_ARCHIVE_LINKS" => $arResult["HideArchiveLinks"],
			"G_SONET_GROUP_LINKS_URL_MESSAGE_TO_GROUP" => htmlspecialcharsback($arResult["Urls"]["MessageToGroup"]),
			"G_SONET_GROUP_LINKS_URL_EDIT" => htmlspecialcharsback($arResult["Urls"]["Edit"]),
			"G_SONET_GROUP_LINKS_URL_FEATURES" => htmlspecialcharsback($arResult["Urls"]["Features"]),
			"G_SONET_GROUP_LINKS_URL_GROUP_DELETE" => htmlspecialcharsback($arResult["Urls"]["GroupDelete"]),
			"G_SONET_GROUP_LINKS_URL_GROUP_MODS" => htmlspecialcharsback($arResult["Urls"]["GroupMods"]),
			"G_SONET_GROUP_LINKS_URL_GROUP_USERS" => htmlspecialcharsback($arResult["Urls"]["GroupUsers"]),
			"G_SONET_GROUP_LINKS_URL_GROUP_BAN" => htmlspecialcharsback($arResult["Urls"]["GroupBan"]),
			"G_SONET_GROUP_LINKS_URL_GROUP_REQUEST_SEARCH" => htmlspecialcharsback($arResult["Urls"]["GroupRequestSearch"]),
			"G_SONET_GROUP_LINKS_URL_GROUP_REQUESTS" => htmlspecialcharsback($arResult["Urls"]["GroupRequests"]),
			"G_SONET_GROUP_LINKS_URL_GROUP_REQUESTS_OUT" => htmlspecialcharsback($arResult["Urls"]["GroupRequestsOut"]),
			"G_SONET_GROUP_LINKS_URL_USER_REQUEST_GROUP" => htmlspecialcharsback($arResult["Urls"]["UserRequestGroup"]),
			"G_SONET_GROUP_LINKS_URL_USER_LEAVE_GROUP" => htmlspecialcharsback($arResult["Urls"]["UserLeaveGroup"]),
			"G_SONET_GROUP_LINKS_URL_SUBSCRIBE" => htmlspecialcharsback($arResult["Urls"]["Subscribe"]),
			"G_SONET_GROUP_LINKS_OPENED" => $arResult["Group"]["OPENED"],
			"G_SONET_GROUP_LINKS_USE_BAN" => $arParams["GROUP_USE_BAN"],

			"G_SONET_GROUP_MODS_MODERATORS_LIST" => $arResult["Moderators"]["List"],
			"G_SONET_GROUP_MODS_CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"G_SONET_GROUP_MODS_CACHE_TIME" => $arParams["CACHE_TIME"],

			"G_SONET_GROUP_USERS_MEMBERS_LIST" => $arResult["Members"]["List"],
			"G_SONET_GROUP_USERS_CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"G_SONET_GROUP_USERS_CACHE_TIME" => $arParams["CACHE_TIME"],
			"G_SONET_GROUP_USERS_URL_GROUP_USERS" => htmlspecialcharsback($arResult["Urls"]["GroupUsers"]),
			"G_SONET_GROUP_USERS_NUMBER_OF_MEMBERS" => $arResult["Group"]["NUMBER_OF_MEMBERS"],

			"G_UPDATES_ENTITY_TEMPLATE_NAME" => ".default",
			"G_UPDATES_ENTITY_GROUP_ID" => $arParams["GROUP_ID"],
			"G_UPDATES_ENTITY_USER_VAR" => $arParams["VARIABLE_ALIASES"]["user_id"],
			"G_UPDATES_ENTITY_GROUP_VAR" => $arParams["VARIABLE_ALIASES"]["group_id"],
			"G_UPDATES_ENTITY_PAGE_VAR" => $arParams["VARIABLE_ALIASES"]["page"],
			"G_UPDATES_ENTITY_PATH_TO_USER" => $arParams["PATH_TO_USER"],
			"G_UPDATES_ENTITY_PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
			"G_UPDATES_ENTITY_ITEMS_COUNT" => 10,
			"G_UPDATES_ENTITY_LIST_URL" => $arParams["~PATH_TO_USER_LOG"],
			"G_UPDATES_ENTITY_PATH_TO_GROUP_LOG" => $arResult["Urls"]["GroupLog"],
			"G_UPDATES_ENTITY_SUBSCRIBE_ONLY" => $arParams["LOG_SUBSCRIBE_ONLY"],
			"G_UPDATES_ENTITY_PATH_TO_USER_BLOG_POST" => $arParams["PATH_TO_POST"],
			"G_UPDATES_ENTITY_PATH_TO_USER_BLOG_POST_EDIT" => $arParams["PATH_TO_POST_EDIT"],

			"G_SONET_GROUP_TAGS_PAGE_ELEMENTS" => $arParams["SEARCH_TAGS_PAGE_ELEMENTS"],
			"G_SONET_GROUP_TAGS_PERIOD" => $arParams["SEARCH_TAGS_PERIOD"],
			"G_SONET_GROUP_TAGS_GROUP_ID" => $arParams["GROUP_ID"],
			"G_SONET_GROUP_TAGS_PATH_TO_GROUP_CONTENT_SEARCH" => $arParams["~PATH_TO_GROUP_CONTENT_SEARCH"],
			"G_SONET_GROUP_TAGS_FONT_MAX" => $arParams["SEARCH_TAGS_FONT_MAX"],
			"G_SONET_GROUP_TAGS_FONT_MIN" => $arParams["SEARCH_TAGS_FONT_MIN"],
			"G_SONET_GROUP_TAGS_COLOR_NEW" => $arParams["SEARCH_TAGS_COLOR_NEW"],
			"G_SONET_GROUP_TAGS_COLOR_OLD" => $arParams["SEARCH_TAGS_COLOR_OLD"],
		);


	if(!empty($arResult["BLOG"]["SHOW"]))
	{
		$arDesktopParams["G_SONET_BLOG_TITLE"] = $arResult["ActiveFeatures"]["blog"];
		$arDesktopParams["G_SONET_BLOG_SHOW"] = "Y";
		$arDesktopParams["G_SONET_BLOG_SHOW_TITLE"] = "N";
		$arDesktopParams["G_SONET_BLOG_TITLE"] = $arResult["BLOG"]["TITLE"];
		$arDesktopParams["G_SONET_BLOG_TEMPLATE_NAME"] = ".default";
		$arDesktopParams["G_SONET_BLOG_PATH_TO_BLOG"] = $arParams["~PATH_TO_BLOG"];
		$arDesktopParams["G_SONET_BLOG_PATH_TO_POST"] = $arParams["~PATH_TO_POST"];
		$arDesktopParams["G_SONET_BLOG_PATH_TO_GROUP_BLOG_POST"] = $arParams["~PATH_TO_GROUP_BLOG_POST"];
		$arDesktopParams["G_SONET_BLOG_PATH_TO_GROUP_BLOG"] = $arParams["~PATH_TO_GROUP_BLOG"];
		$arDesktopParams["G_SONET_BLOG_PATH_TO_USER"] = $arParams["~PATH_TO_USER"];
		$arDesktopParams["G_SONET_BLOG_PATH_TO_SMILE"] = $arParams["PATH_TO_SMILE"];
		$arDesktopParams["G_SONET_BLOG_CACHE_TYPE"] = $arParams["CACHE_TYPE"];
		$arDesktopParams["G_SONET_BLOG_CACHE_TIME"] = $arParams["CACHE_TIME"];
		$arDesktopParams["G_SONET_BLOG_BLOG_VAR"] = $arParams["VARIABLE_ALIASES"]["blog"];
		$arDesktopParams["G_SONET_BLOG_POST_VAR"] = $arParams["VARIABLE_ALIASES"]["post_id"];
		$arDesktopParams["G_SONET_BLOG_USER_VAR"] = $arParams["VARIABLE_ALIASES"]["user_id"];
		$arDesktopParams["G_SONET_BLOG_PAGE_VAR"] = $arParams["VARIABLE_ALIASES"]["page"];
		$arDesktopParams["G_SONET_BLOG_DATE_TIME_FORMAT"] = $arParams["DATE_TIME_FORMAT"];
		$arDesktopParams["G_SONET_BLOG_SOCNET_GROUP_ID"] = $arParams["GROUP_ID"];
		$arDesktopParams["G_SONET_BLOG_GROUP_ID"] = $arParams["BLOG_GROUP_ID"];
		$arDesktopParams["G_SONET_BLOG_ALLOW_POST_CODE"] = $arParams["BLOG_ALLOW_POST_CODE"];
	}
	else
		$arDesktopParams["G_SONET_BLOG_SHOW"] = "N";

	if(!empty($arResult["forum"]["SHOW"]))
	{
		$arDesktopParams["G_SONET_FORUM_TITLE"] = $arResult["ActiveFeatures"]["forum"];
		$arDesktopParams["G_SONET_FORUM_SHOW"] = "Y";
		$arDesktopParams["G_SONET_FORUM_FID"] = $arParams["FORUM_ID"];
		$arDesktopParams["G_SONET_FORUM_URL_TEMPLATES_MESSAGE"] = $arParams["~PATH_TO_GROUP_FORUM_MESSAGE"];
		$arDesktopParams["G_SONET_FORUM_URL_TEMPLATES_TOPIC"] = $arParams["~PATH_TO_GROUP_FORUM_TOPIC"];
		$arDesktopParams["G_SONET_FORUM_URL_TEMPLATES_USER"] = $arParams["~PATH_TO_USER"];
		$arDesktopParams["G_SONET_FORUM_DATE_TIME_FORMAT"] = $arParams["DATE_TIME_FORMAT"];
		$arDesktopParams["G_SONET_FORUM_CACHE_TYPE"] = $arParams["CACHE_TYPE"];
		$arDesktopParams["G_SONET_FORUM_CACHE_TIME"] = $arParams["CACHE_TIME"];
		$arDesktopParams["G_SONET_FORUM_SOCNET_GROUP_ID"] = $arParams["GROUP_ID"];
	}
	else
		$arDesktopParams["G_SONET_FORUM_SHOW"] = "N";

	if(!empty($arResult["tasks"]["SHOW"]))
	{
		$arDesktopParams["G_TASKS_TITLE"] = $arResult["ActiveFeatures"]["tasks"];
		$arDesktopParams["G_TASKS_SHOW"] = "Y";
		$arDesktopParams["G_TASKS_SHOW_TITLE"] = "N";
		$arDesktopParams["G_TASKS_SHOW_FOOTER"] = "N";
		$arDesktopParams["G_TASKS_TEMPLATE_NAME"] = ".default";
		$arDesktopParams["G_TASKS_OWNER_ID"] = $arResult["Group"]["ID"];
		$arDesktopParams["G_TASKS_TASK_TYPE"] = 'group';
		$arDesktopParams["G_TASKS_ITEMS_COUNT"] = 10;
		$arDesktopParams["G_TASKS_PAGE_VAR"] = $arParams["PAGE_VAR"];
		$arDesktopParams["G_TASKS_GROUP_VAR"] = $arParams["GROUP_VAR"];
		$arDesktopParams["G_TASKS_VIEW_VAR"] = $arParams["VIEW_VAR"];
		$arDesktopParams["G_TASKS_TASK_VAR"] = $arParams["TASK_VAR"];
		$arDesktopParams["G_TASKS_ACTION_VAR"] = $arParams["TASK_ACTION_VAR"];
		$arDesktopParams["G_TASKS_PATH_TO_GROUP_TASKS"] = $arParams["PATH_TO_GROUP_TASKS"];
		$arDesktopParams["G_TASKS_PATH_TO_GROUP_TASKS_TASK"] = $arParams["PATH_TO_GROUP_TASKS_TASK"];
		$arDesktopParams["G_TASKS_PATH_TO_GROUP_TASKS_VIEW"] = $arParams["PATH_TO_GROUP_TASKS_VIEW"];
		$arDesktopParams["G_TASKS_FORUM_ID"] = $arParams["TASK_FORUM_ID"];
	}
	else
		$arDesktopParams["G_TASKS_SHOW"] = "N";

	if ($this->__component->__parent && $this->__component->__parent->arResult && is_array($this->__component->__parent->arResult))
		$arDesktopParams["PARENT_COMPONENT_RESULT"] = $this->__component->__parent->arResult;

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