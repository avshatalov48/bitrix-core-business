<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!IsModuleInstalled("im")):
	?><?
	/*
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.user_menu",
		"",
		Array(
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_USER_EDIT" => $arResult["PATH_TO_USER_PROFILE_EDIT"],
			"PATH_TO_USER_FRIENDS" => $arResult["PATH_TO_USER_FRIENDS"],
			"PATH_TO_USER_GROUPS" => $arResult["PATH_TO_USER_GROUPS"],
			"PATH_TO_USER_FRIENDS_ADD" => $arResult["PATH_TO_USER_FRIENDS_ADD"],
			"PATH_TO_USER_FRIENDS_DELETE" => $arResult["PATH_TO_USER_FRIENDS_DELETE"],
			"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
			"PATH_TO_USER_CONTENT_SEARCH" => $arResult["PATH_TO_USER_CONTENT_SEARCH"],
			"ID" => $arResult["VARIABLES"]["user_id"],
		),
		$component
	);
	*/
	?><?
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.message_form", 
		"", 
		Array(
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"PATH_TO_SMILE" => $arResult["PATH_TO_SMILE"],
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
			"PATH_TO_MESSAGES_OUTPUT" => $arResult["PATH_TO_MESSAGES_OUTPUT"],
			"MESSAGE_ID" => $arResult["VARIABLES"]["message_id"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		),
		$component 
	);
	?><?
endif;
?>