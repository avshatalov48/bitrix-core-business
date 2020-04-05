<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;

if (CSocNetUser::IsFriendsAllowed() && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite())):

	if ($arGadgetParams["IS_CURRENT_USER"]):

		$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:socialnetwork.user_birthday",
				"",
				array(
					"ID" => $arGadgetParams["USER_ID"],
					"USER_ID" => $arGadgetParams["USER_ID"],						
					"ITEMS_COUNT" => 4,
					"PATH_TO_USER" => htmlspecialcharsback($arParams["PATH_TO_USER"]),
					"PATH_TO_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
					"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
					"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
					"PAGE_VAR" => $arGadgetParams["PAGE_VAR"],
					"USER_VAR" => $arGadgetParams["USER_VAR"],
					"THUMBNAIL_LIST_SIZE" => $arParams["THUMBNAIL_LIST_SIZE"],
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"SHOW_YEAR" => $arParams["SHOW_YEAR"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
				),
				false, 
				array("HIDE_ICONS" => "Y")
		);

	else:
		echo GetMessage('GD_SONET_USER_BIRTHDAY_ONLY_CURRENT');	
	endif;

else:
	echo GetMessage('GD_SONET_USER_BIRTHDAY_NOT_ALLOWED');
endif;
?>