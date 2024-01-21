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

<div class="sn-spaces__user">
<?php
	$APPLICATION->includeComponent(
		'bitrix:disk.folder.list',
		'',
		[
			'TRASH_MODE' => true,
			'CONTEXT' => Context::SPACES,

			'STORAGE' => $arParams['STORAGE'],
			'FOLDER_ID' => $arParams['FOLDER_ID'],

			'RELATIVE_PATH' => $arParams['RELATIVE_PATH'],
			'RELATIVE_ITEMS' => $arParams['RELATIVE_ITEMS'],

			'PATH_TO_USER' => $arParams['PATH_TO_USER'],
			'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],

			'URL_TO_TRASHCAN_LIST' => $arParams['URL_TO_TRASHCAN_LIST'],
			'URL_TO_FOLDER_LIST' => $arParams['URL_TO_FOLDER_LIST'],
			'URL_TO_EMPTY_TRASHCAN' => $arParams['URL_TO_EMPTY_TRASHCAN'],

			'PATH_TO_EXTERNAL_LINK_LIST' => $arParams['PATH_TO_USER_FILES_EXTERNAL_LINK_LIST'],
			'PATH_TO_FOLDER_LIST' => $arParams['PATH_TO_FOLDER_LIST'],
			'PATH_TO_FOLDER_VIEW' => $arParams['PATH_TO_FOLDER_VIEW'],
			'PATH_TO_FILE_VIEW' => $arParams['PATH_TO_FILE_VIEW'],
			'PATH_TO_TRASHCAN_LIST' => $arParams['PATH_TO_USER_FILES_TRASHCAN_LIST'],
			'PATH_TO_TRASHCAN_FILE_VIEW' => $arParams['PATH_TO_USER_FILES_TRASHCAN_FILE_VIEW'],
			'PATH_TO_FILE_HISTORY' => $arParams['PATH_TO_FILE_HISTORY'],
		],
	);
?>
</div>
