<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "";
include("util_menu.php");
?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.user_requests.ex", 
	"", 
	Array(
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"] ?? null,
		"USER_VAR" => $arResult["ALIASES"]["user_id"] ?? null,
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"] ?? null,
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"] ?? null,
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"USE_KEYWORDS" => $arParams["GROUP_USE_KEYWORDS"],
		"USE_AUTOSUBSCRIBE" => "N",
	),
	$component 
);
?>