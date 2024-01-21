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
			'CONTEXT' => Context::SPACES,

			'STORAGE' => $arParams['STORAGE'],
			'FOLDER' => $arParams['FOLDER'],
			'RELATIVE_PATH' => $arParams['RELATIVE_PATH'],
			'RELATIVE_ITEMS' => $arParams['RELATIVE_ITEMS'],

			'PATH_TO_FOLDER_LIST' => $arParams['PATH_TO_FOLDER_LIST'],
			'PATH_TO_FOLDER_VIEW' => $arParams['PATH_TO_FOLDER_VIEW'],
			'PATH_TO_FILE_VIEW' => $arParams['PATH_TO_FILE_VIEW'],
			'PATH_TO_FILE_HISTORY' => $arParams['PATH_TO_FILE_HISTORY'],

			'PATH_TO_USER' => $arParams['PATH_TO_USER'],
			'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
		]
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
