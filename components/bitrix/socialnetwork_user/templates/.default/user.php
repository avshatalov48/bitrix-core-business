<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "user";

$componentParams = array(
	"PATH_TO_USER" => $arResult["PATH_TO_USER"],
	"PATH_TO_USER_EDIT" => $arResult["PATH_TO_USER_PROFILE_EDIT"],
	"PATH_TO_USER_FRIENDS" => $arResult["PATH_TO_USER_FRIENDS"],
	"PATH_TO_USER_GROUPS" => $arResult["PATH_TO_USER_GROUPS"],
	"PATH_TO_USER_FRIENDS_ADD" => $arResult["PATH_TO_USER_FRIENDS_ADD"],
	"PATH_TO_USER_FRIENDS_DELETE" => $arResult["PATH_TO_USER_FRIENDS_DELETE"],
	"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
	"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
	"PATH_TO_MESSAGES_USERS_MESSAGES" => $arResult["PATH_TO_MESSAGES_USERS_MESSAGES"],
	"PATH_TO_USER_SETTINGS_EDIT" => $arResult["PATH_TO_USER_SETTINGS_EDIT"],
	"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
	"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
	"PATH_TO_USER_FEATURES" => $arResult["PATH_TO_USER_FEATURES"],
	"PATH_TO_EXTMAIL" => $arResult["PATH_TO_MAIL"] ?? '',
	"PATH_TO_USER_REQUESTS" => $arResult["PATH_TO_USER_REQUESTS"],
	"PAGE_VAR" => $arResult["ALIASES"]["page"] ?? '',
	"USER_VAR" => $arResult["ALIASES"]["user_id"] ?? '',
	"PATH_TO_SEARCH" => $arResult["PATH_TO_SEARCH"],
	"PATH_TO_SEARCH_INNER" => $arResult["PATH_TO_SEARCH_INNER"],
	"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
	"SET_TITLE" => $arResult["SET_TITLE"],
	"USER_PROPERTY_MAIN" => $arResult["USER_PROPERTY_MAIN"],
	"USER_PROPERTY_CONTACT" => $arResult["USER_PROPERTY_CONTACT"],
	"USER_PROPERTY_PERSONAL" => $arResult["USER_PROPERTY_PERSONAL"],
	"USER_FIELDS_MAIN" => $arResult["USER_FIELDS_MAIN"],
	"USER_FIELDS_CONTACT" => $arResult["USER_FIELDS_CONTACT"],
	"USER_FIELDS_PERSONAL" => $arResult["USER_FIELDS_PERSONAL"],
	"EDITABLE_FIELDS" => $arParams["EDITABLE_FIELDS"],
	"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
	"SHORT_FORM" => "N",
	"ITEMS_COUNT" => $arParams["ITEM_MAIN_COUNT"],
	"ID" => $arResult["VARIABLES"]["user_id"],
	"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"],
	"PATH_TO_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
	"PATH_TO_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"],
	"BLOG_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
	"PATH_TO_GROUP_REQUEST_GROUP_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"],
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
	"PATH_TO_USER_FORUM" => $arResult["PATH_TO_USER_FORUM"],
	"PATH_TO_USER_FORUM_TOPIC" => $arResult["~PATH_TO_USER_FORUM_TOPIC"],
	"FORUM_ID" => $arParams["FORUM_ID"],
	"USER_ID" => $arResult["VARIABLES"]["user_id"],
	"SHOW_YEAR" => $arParams["SHOW_YEAR"],
	"SONET_USER_FIELDS_SEARCHABLE" => $arResult["USER_FIELDS_SEARCHABLE"],
	"SONET_USER_PROPERTY_SEARCHABLE" => $arResult["USER_PROPERTY_SEARCHABLE"],
	"PATH_TO_USER_SUBSCRIBE" => $arResult["PATH_TO_USER_SUBSCRIBE"],
	"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
	"PATH_TO_ACTIVITY" => $arResult["PATH_TO_ACTIVITY"],
	"PATH_TO_SUBSCRIBE" => $arResult["PATH_TO_SUBSCRIBE"],
	"PATH_TO_GROUP_SEARCH" => $arParams["PATH_TO_GROUP_SEARCH"],
	"CALENDAR_USER_IBLOCK_ID" => $arParams['CALENDAR_USER_IBLOCK_ID'] ?? 0,
	"TASK_VAR" => $arResult["ALIASES"]["task_id"] ?? '',
	"TASK_ACTION_VAR" => $arResult["ALIASES"]["action"] ?? '',
	"PATH_TO_GROUP_TASKS" => $arParams["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_TASKS_TASK" => $arParams["PATH_TO_GROUP_TASKS_TASK"],
	"PATH_TO_GROUP_TASKS_VIEW" => $arParams["PATH_TO_GROUP_TASKS_VIEW"],
	"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
	"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
	"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
	"TASK_FORUM_ID" => $arParams["TASK_FORUM_ID"],
	"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
	"PATH_TO_USER_CONTENT_SEARCH" => $arResult["PATH_TO_USER_CONTENT_SEARCH"],
	"THUMBNAIL_LIST_SIZE" => 30,
	"LOG_AVATAR_SIZE" => $arParams["LOG_THUMBNAIL_SIZE"] ?? null,
	"LOG_AVATAR_SIZE_COMMENT" => $arParams["LOG_COMMENT_THUMBNAIL_SIZE"] ?? null,
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"CAN_OWNER_EDIT_DESKTOP" => $arParams["CAN_OWNER_EDIT_DESKTOP"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"RATING_ID" => $arParams["RATING_ID"],
	"RATING_TYPE" => $arParams["RATING_TYPE"],
	"BLOG_ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
	"PATH_TO_USER_SECURITY" => $arResult["PATH_TO_USER_SECURITY"],
	"PATH_TO_USER_COMMON_SECURITY" => $arResult["PATH_TO_USER_COMMON_SECURITY"],
	"PATH_TO_USER_PASSWORDS" => $arResult["PATH_TO_USER_PASSWORDS"],
	"PATH_TO_USER_SYNCHRONIZE" => !empty($arResult["PATH_TO_USER_SYNCHRONIZE"]) ? $arResult["PATH_TO_USER_SYNCHRONIZE"] : null,
	"PATH_TO_USER_CODES" => $arResult["PATH_TO_USER_CODES"],
	"ALLOWALL_USER_PROFILE_FIELDS" => $arParams["ALLOWALL_USER_PROFILE_FIELDS"] ?? '',
);

if (\Bitrix\Main\ModuleManager::isModuleInstalled("intranet") && SITE_TEMPLATE_ID == "bitrix24")
{
	if (
		\Bitrix\Main\Context::getCurrent()->getRequest()->get('IFRAME') === 'Y'
		|| \Bitrix\Main\Context::getCurrent()->getRequest()->get('mode') === 'dev'
	)
	{
		include("util_menu.php");

		$APPLICATION->IncludeComponent(
			"bitrix:ui.sidepanel.wrapper",
			"",
			array(
				'POPUP_COMPONENT_NAME' => "bitrix:intranet.user.profile",
				"POPUP_COMPONENT_TEMPLATE_NAME" => "",
				"POPUP_COMPONENT_PARAMS" => array_merge($componentParams, array(
					"PATH_TO_POST_EDIT_PROFILE" => $arResult["PATH_TO_USER_BLOG_POST_EDIT_PROFILE"],
					"PATH_TO_POST_EDIT_GRAT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT_GRAT"],
					"PATH_TO_USER_GRAT" => $arResult["PATH_TO_USER_GRAT"],
					"PATH_TO_USER_STRESSLEVEL" => $arResult["PATH_TO_USER_STRESSLEVEL"],
				)),
				"POPUP_COMPONENT_USE_BITRIX24_THEME" => "Y",
				'USE_PADDING' => false,
				"POPUP_COMPONENT_BITRIX24_THEME_FOR_USER_ID" => $arResult["VARIABLES"]["user_id"],
				"POPUP_COMPONENT_PARENT" => $this->getComponent()
			)
		);
	}
	else
	{
		$intranetUserListComponentParams = [
			"PATH_TO_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"SLIDER_PROFILE_USER_ID" => $arResult["VARIABLES"]["user_id"],
			"LIST_URL" => SITE_DIR.'company/',
		];


		if (
			\Bitrix\Main\Loader::includeModule('extranet')
			&& CExtranet::isExtranetSite()
		)
		{
			$intranetSearchComponentParams["LIST_URL"] = SITE_DIR.'contacts/';
		}

		$APPLICATION->includeComponent("bitrix:intranet.user.list", "", $intranetUserListComponentParams);
	}
}
else
{
	include("util_menu.php");

	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.user_profile",
		"",
		$componentParams,
		$this->getComponent()
	);
}
