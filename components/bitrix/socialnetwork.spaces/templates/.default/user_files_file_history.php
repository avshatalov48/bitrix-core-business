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

require_once __DIR__ . '/params.php';

$componentParams = array_merge(
	$componentParams,
	[
		'PAGE' => 'user_files_file_history',
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

$fileId = $arResult['VARIABLES']['FILE_ID'];

$file = \Bitrix\Disk\File::loadById($arResult['VARIABLES']['FILE_ID']);
if (!$file)
{
	return;
}

$toolbarComponentParams = array_merge(
	$componentParams,
	[

	],
);

$contentComponentParams = array_merge(
	$componentParams,
	[
		'STORAGE' => $file->getStorage(),
		'FILE' => $file,
		'FILE_ID' => $arResult['VARIABLES']['FILE_ID'],
	],
);

$includeToolbar = false;

require_once __DIR__ . '/template.php';
