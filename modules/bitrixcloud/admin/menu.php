<?php
IncludeModuleLangFile(__FILE__);
/** @global CUser $USER */

$menu = [
	'parent_menu' => 'global_menu_settings',
	'section' => 'bitrixcloud',
	'sort' => 1645,
	'text' => GetMessage('BCL_MENU_ITEM'),
	'icon' => 'bitrixcloud_menu_icon',
	'page_icon' => 'bitrixcloud_page_icon',
	'items_id' => 'menu_bitrixcloud',
	'items' => [],
];

if ($USER->CanDoOperation('bitrixcloud_backup'))
{
	$menu['items'][] = [
		'text' => GetMessage('BCL_MENU_BACKUP_ITEM'),
		'url' => 'bitrixcloud_backup.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'bitrixcloud_backup.php',
		],
	];
	$menu['items'][] = [
		'text' => GetMessage('BCL_MENU_BACKUP_JOB_ITEM'),
		'url' => 'bitrixcloud_backup_job.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'bitrixcloud_backup_job.php',
		],
	];
}

if ($USER->CanDoOperation('bitrixcloud_monitoring'))
{
	$menu['items'][] = [
		'text' => GetMessage('BCL_MENU_MONITORING_ITEM'),
		'url' => 'bitrixcloud_monitoring_admin.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'bitrixcloud_monitoring_admin.php',
			'bitrixcloud_monitoring_edit.php',
		],
	];
}

if ($menu['items'])
{
	return $menu;
}
else
{
	return false;
}
