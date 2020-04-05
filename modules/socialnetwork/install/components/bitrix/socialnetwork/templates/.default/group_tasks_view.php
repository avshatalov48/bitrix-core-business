<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_tasks";
include("util_group_menu.php");
include("util_group_profile.php");
?>
<?
if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "tasks"))
{
	?>

	<?
	$APPLICATION->IncludeComponent("bitrix:intranet.tasks.menu", ".default", Array(
			"IBLOCK_ID" => $arParams["TASK_IBLOCK_ID"],
			"OWNER_ID" => $arResult["VARIABLES"]["group_id"],
			"TASK_TYPE" => 'group',
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
			"ACTION_VAR" => $arResult["ALIASES"]["action"],
			"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
			"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
			"PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
			"PAGE_ID" => "group_tasks_view",
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
			"OWNER_ID" => $arResult["VARIABLES"]["group_id"],
			"TASK_TYPE" => 'group',
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
			"ACTION_VAR" => $arResult["ALIASES"]["action"],
			"ACTION" => $arResult["VARIABLES"]["action"],
			"VIEW_ID" => $arResult["VARIABLES"]["view_id"],
			"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
			"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
			"PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
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