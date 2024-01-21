<?php

use Bitrix\Socialnetwork\Livefeed\Context\Context;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

?>

<div class="sn-spaces__group">
<?php
	$APPLICATION->includeComponent(
		'bitrix:disk.folder.list',
		'',
		[
			'CONTEXT' => Context::SPACES,

			'STORAGE' => $arParams['STORAGE'],
			'FOLDER' => $arParams['FOLDER'],
			'RELATIVE_PATH' => $arParams['RELATIVE_PATH'],
			'RELATIVE_ITEMS' => $arParams['RELATIVE_ITEMS'],

			'PATH_TO_FOLDER_LIST' => $arParams['PATH_TO_FOLDER_LIST'],
			'PATH_TO_FOLDER_VIEW' => $arParams['PATH_TO_FOLDER_VIEW'],
			'PATH_TO_FILE_VIEW' => $arParams['PATH_TO_FILE_VIEW'],

			'PATH_TO_DISK_BIZPROC_WORKFLOW_ADMIN' => $arParams['PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN'],
			'PATH_TO_DISK_START_BIZPROC' => $arParams['PATH_TO_GROUP_FILES_START_BIZPROC'],
			'PATH_TO_DISK_TASK_LIST' => $arParams['PATH_TO_GROUP_FILES_TASK_LIST'],
			'PATH_TO_DISK_TASK' => $arParams['PATH_TO_GROUP_FILES_TASK'],
			'PATH_TO_FILE_HISTORY' => $arParams['PATH_TO_GROUP_FILES_FILE_HISTORY'],
			'PATH_TO_TRASHCAN_LIST' => $arParams['PATH_TO_GROUP_FILES_TRASHCAN_LIST'],

			'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
			'PATH_TO_GROUP_GENERAL' => $arParams['PATH_TO_GROUP_DISCUSSIONS'],
			'PATH_TO_GROUP_CALENDAR' => $arParams['PATH_TO_GROUP_CALENDAR'],
			'PATH_TO_GROUP_DISK' => $arParams['PATH_TO_GROUP_FILES'],
			'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],
		],
	);

	$APPLICATION->IncludeComponent(
		'bitrix:disk.file.upload',
		'',
		[
			'STORAGE' => $arParams['STORAGE'],
			'FOLDER' => $arParams['FOLDER'],
			'CID' => 'FolderList',
			'DROPZONE' => 'document.getElementById("bx-disk-container")',
		],
		$component,
		["HIDE_ICONS" => "Y"]
	);
?>
</div>
