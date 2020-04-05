<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user_tasks";
include("util_menu.php");
include("util_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	?>
	<?
	$APPLICATION->IncludeComponent("bitrix:intranet.tasks.menu", ".default", Array(
			"IBLOCK_ID" => $arParams["TASK_IBLOCK_ID"],
			"OWNER_ID" => $arResult["VARIABLES"]["user_id"],
			"TASK_TYPE" => 'user',
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
			"ACTION_VAR" => $arResult["ALIASES"]["action"],
			"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
			"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
			"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
			"PAGE_ID" => "user_tasks_view",
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
	?>
	<?
	$APPLICATION->IncludeComponent(
		"bitrix:intranet.tasks.create_view",
		".default",
		Array(
			"IBLOCK_ID" => $arParams["TASK_IBLOCK_ID"],
			"OWNER_ID" => $arResult["VARIABLES"]["user_id"],
			"TASK_TYPE" => 'user',
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
			"ACTION_VAR" => $arResult["ALIASES"]["action"],
			"ACTION" => $arResult["VARIABLES"]["action"],
			"VIEW_ID" => $arResult["VARIABLES"]["view_id"],
			"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
			"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
			"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
			"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"], 
			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"FORUM_ID" => $arParams["TASK_FORUM_ID"],
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
}
?>