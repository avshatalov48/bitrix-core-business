<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arParams["SET_NAV_CHAIN"] != "N")
	$APPLICATION->AddChainItem(GetMessage("BP_TASK"), CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_BIZPROC"], array()));

if (!IsModuleInstalled("im")):
	?><?$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.messages_menu",
		"",
		Array(
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
			"PATH_TO_MESSAGES_OUTPUT" => $arResult["PATH_TO_MESSAGES_OUTPUT"],
			"PATH_TO_USER_BAN" => $arResult["PATH_TO_USER_BAN"],
			"PATH_TO_MESSAGES_USERS" => $arResult["PATH_TO_MESSAGES_USERS"],
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
			"PATH_TO_SUBSCRIBE" => $arResult["PATH_TO_SUBSCRIBE"],
			"PATH_TO_BIZPROC" => $arResult["PATH_TO_BIZPROC"],
			"PATH_TO_TASKS" => $arResult["PATH_TO_TASKS"],
			"PAGE_ID" => "bizproc",
			"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
			"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
		),
		$component
	);
	?><?
endif;

?><?$APPLICATION->IncludeComponent(
	"bitrix:bizproc.task", 
	"", 
	Array(
	"TASK_ID" => $arResult["VARIABLES"]["task_id"],
	"TASK_EDIT_URL" => str_replace("#task_id#", "#ID#", $arResult["PATH_TO_BIZPROC_EDIT"]),
	"USER_ID" => 0, 
	"WORKFLOW_ID" => "", 
	"DOCUMENT_URL" => "", 
	"SET_TITLE" => $arParams["SET_TITLE"],
	"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>