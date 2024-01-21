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

$userId = (int) $arResult['VARIABLES']['user_id'];

require_once __DIR__ . '/params.php';

$componentParams = array_merge(
	$componentParams,
	[
		'PAGE' => 'user_files_trashcan_list',
		'PAGE_TYPE' => 'user',
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
$storage->getProxyType()->setEntityUrl(
	CComponentEngine::makePathFromTemplate(
		$arResult['PATH_TO_SPACES_USER_FILES'],
		['user_id' => $userId]
	)
);

$toolbarComponentParams = array_merge(
	$componentParams,
	[
		'STORAGE' => $storage,
		'IS_TRASH_MODE' => true,

		'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_TRASHCAN_LIST'],
			[
				'user_id' => $userId,
				'TRASH_PATH' => '',
			]
		),
	],
);

$contentComponentParams = array_merge(
	$componentParams,
	[
		'STORAGE' => $storage,
		'FOLDER_ID' => $arResult['VARIABLES']['FOLDER_ID'],
		'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
		'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],

		'URL_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES'],
			[
				'user_id' => $userId,
				'PATH' => '',
			]
		),
		'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_TRASHCAN_LIST'],
			[
				'user_id' => $userId,
				'TRASH_PATH' => '',
			]
		),
		'URL_TO_EMPTY_TRASHCAN' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES'],
			['PATH' => '']
		),

		'PATH_TO_FOLDER_VIEW' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_FILE'],
			['user_id' => $userId]
		),
		'PATH_TO_FILE_VIEW' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_FILE'],
			['user_id' => $userId]
		),
		'PATH_TO_FILE_HISTORY' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_FILE_HISTORY'],
			['user_id' => $userId]
		),
		'PATH_TO_USER_FILES_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_TRASHCAN_LIST'],
			['user_id' => $userId]
		),
		'PATH_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES'],
			['user_id' => $userId]
		),
		'PATH_TO_USER_FILES_EXTERNAL_LINK_LIST' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_EXTERNAL_LINK_LIST'],
			['user_id' => $userId]
		),
		'PATH_TO_USER_FILES_TRASHCAN_FILE_VIEW' => CComponentEngine::makePathFromTemplate(
			$arResult['PATH_TO_USER_FILES_TRASHCAN_FILE_VIEW'],
			['user_id' => $userId]
		),
	],
);

require_once __DIR__ . '/template.php';
