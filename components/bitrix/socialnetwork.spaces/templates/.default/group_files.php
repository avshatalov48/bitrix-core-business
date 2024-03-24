<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var array $componentParams */
/** @var int $groupId */
/** @var int $userId */

require_once __DIR__ . '/params.php';

$componentParams = array_merge(
	$componentParams,
	[
		'PAGE' => 'group_files',
		'PAGE_TYPE' => 'group',
		'PAGE_ID' => 'files',
	],
);

$listComponentParams = array_merge(
	$componentParams,
	[

	],
);

$menuComponentParams = array_merge(
	$componentParams,
	[

	],
);

/**
 * @var \Bitrix\Disk\Storage $storage
 */
$storage = $arResult['VARIABLES']['STORAGE'];
$storage->getProxyType()->setSefUrl($arParams['SEF_FOLDER']);

$folder = \Bitrix\Disk\Folder::loadById($arResult['VARIABLES']['FOLDER_ID']);

$toolbarComponentParams = array_merge(
	$componentParams,
	[
		'STORAGE' => $storage,
		'FOLDER' => $folder,
		'IS_TRASH_MODE' => false,
		'PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN'],
			['group_id' => $groupId]
		),

		'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_TRASHCAN_LIST'],
			[
				'group_id' => $groupId,
				'TRASH_PATH' => '',
			]
		),
	],
);

$contentComponentParams = array_merge(
	$componentParams,
	[
		'STORAGE' => $storage,
		'FOLDER' => $folder,
		'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
		'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],

		'PATH_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES'],
			['group_id' => $groupId]
		),
		'PATH_TO_FOLDER_VIEW' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_FILE'],
			['group_id' => $groupId]
		),
		'PATH_TO_FILE_VIEW' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_FILE'],
			['group_id' => $groupId]
		),
		'PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN'],
			['group_id' => $groupId]
		),
		'PATH_TO_GROUP_FILES_START_BIZPROC' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_START_BIZPROC'],
			['group_id' => $groupId]
		),
		'PATH_TO_GROUP_FILES_TASK_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_TASK_LIST'],
			['group_id' => $groupId]
		),
		'PATH_TO_GROUP_FILES_TASK' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_TASK'],
			['group_id' => $groupId]
		),
		'PATH_TO_GROUP_FILES_FILE_HISTORY' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_FILE_HISTORY'],
			['group_id' => $groupId]
		),
		'PATH_TO_GROUP_FILES_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_GROUP_FILES_TRASHCAN_LIST'],
			[
				'group_id' => $groupId,
				'TRASH_PATH' => '',
			]
		),
	],
);

require_once __DIR__ . '/template.php';
