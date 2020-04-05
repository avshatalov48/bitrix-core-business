<?php
IncludeModuleLangFile(__FILE__);
/** @global CUser $USER */
$menu = array(
	"parent_menu" => "global_menu_settings",
	"section" => "bitrixcloud",
	"sort" => 1645,
	"text" => GetMessage("BCL_MENU_ITEM"),
	"icon" => "bitrixcloud_menu_icon",
	"page_icon" => "bitrixcloud_page_icon",
	"items_id" => "menu_bitrixcloud",
	"items" => array(),
);

if ($USER->CanDoOperation("bitrixcloud_cdn"))
{
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_CONTROL_ITEM"),
		"url" => "bitrixcloud_cdn.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"bitrixcloud_cdn.php",
		),
	);
}

if ($USER->CanDoOperation("bitrixcloud_backup"))
{
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_BACKUP_ITEM"),
		"url" => "bitrixcloud_backup.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"bitrixcloud_backup.php",
		),
	);
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_BACKUP_JOB_ITEM"),
		"url" => "bitrixcloud_backup_job.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"bitrixcloud_backup_job.php",
		),
	);
}

if ($USER->CanDoOperation("bitrixcloud_monitoring"))
{
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_MONITORING_ITEM"),
		"url" => "bitrixcloud_monitoring_admin.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"bitrixcloud_monitoring_admin.php",
			"bitrixcloud_monitoring_edit.php",
		),
	);
}

if ($menu["items"])
{
	return $menu;
}
else
{
	return false;
}
