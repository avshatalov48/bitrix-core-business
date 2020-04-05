<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!IsModuleInstalled("im")):
	?><?
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.messages_menu",
		"",
		Array(
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
			"PATH_TO_MESSAGES_OUTPUT" => $arResult["PATH_TO_MESSAGES_OUTPUT"],
			"PATH_TO_MESSAGES_USERS" => $arResult["PATH_TO_MESSAGES_USERS"],
			"PATH_TO_USER_BAN" => $arResult["PATH_TO_USER_BAN"],
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
			"PATH_TO_SUBSCRIBE" => $arResult["PATH_TO_SUBSCRIBE"],
			"PATH_TO_BIZPROC" => $arResult["PATH_TO_BIZPROC"],
			"PATH_TO_TASKS" => $arResult["PATH_TO_TASKS"],
			"PAGE_ID" => "messages_users",
			"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
			"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"]
		),
		$component
	);
	?><?
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.messages_requests", 
		"", 
		Array(
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],		
			"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
			"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_SMILE" => $arResult["PATH_TO_SMILE"],
			"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"SET_NAV_CHAIN" => "N",
			"SET_TITLE" => "N",
			"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
			"USE_AUTOSUBSCRIBE" => "N"
		),
		$component 
	);
	?>
	<?
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.messages_users", 
		"", 
		Array(
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
			"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_MESSAGES_USERS" => $arResult["PATH_TO_MESSAGES_USERS"],
			"PATH_TO_MESSAGES_USERS_MESSAGES" => $arResult["PATH_TO_MESSAGES_USERS_MESSAGES"],
			"PATH_TO_SMILE" => $arResult["PATH_TO_SMILE"],
			"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"]
		),
		$component 
	);
	?><?
endif;
?>