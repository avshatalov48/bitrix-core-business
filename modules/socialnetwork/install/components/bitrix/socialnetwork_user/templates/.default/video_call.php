<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (
	!IsModuleInstalled("im") 
	&& IsModuleInstalled("calendar")
)
{
	$APPLICATION->IncludeComponent(
		"bitrix:video.call",
		"",
		Array(
			"SET_TITLE" => $arResult["SET_TITLE"],
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"PATH_TO_VIDEO_MEETING_DETAIL" => COption::GetOptionString('calendar', 'path_to_vr', ""),
			"IBLOCK_ID" => COption::GetOptionString('calendar', 'vr_iblock_id', ""),
		),
		$component
	);
}
?>