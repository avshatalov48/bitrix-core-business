<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\FileInputUtility;

global $APPLICATION;

$fileInputUtility = FileInputUtility::instance();
$APPLICATION->IncludeComponent(
	'bitrix:main.file.input',
	'drag_n_drop',
	[
		'CONTROL_ID' => $fileInputUtility->getUserFieldCid($arResult['userField']),
		'INPUT_NAME' => $arResult['userField']['FIELD_NAME'],
		'INPUT_NAME_UNSAVED' => 'FILE_NEW_TMP',
		'INPUT_VALUE' => $arResult['value'],
		'MAX_FILE_SIZE' => (int)$arResult['userField']['SETTINGS']['MAX_ALLOWED_SIZE'],
		'MULTIPLE' => ($arResult['userField']['MULTIPLE'] === 'Y' ? 'Y' : 'N'),
		'MODULE_ID' => 'uf',
		'ALLOW_UPLOAD' => 'A'
	],
	null,
	['HIDE_ICONS' => true]
);
