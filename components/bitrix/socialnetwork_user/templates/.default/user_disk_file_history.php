<?php
use Bitrix\Disk\Banner;
use Bitrix\Disk\Desktop;
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$pageId = "user_disk_file_history";
include("util_menu.php");
include("util_profile.php");
?>
<?php

$file = \Bitrix\Disk\File::loadById($arResult['VARIABLES']['FILE_ID']);
if (!$file)
{
	return;
}

$componentParameters = array_merge(
	$arResult,
	array(
		'STORAGE' => $file->getStorage(),
		'FILE' => $file,
		'FILE_ID' => $arResult['VARIABLES']['FILE_ID'],
	)
);

$APPLICATION->IncludeComponent(
	'bitrix:disk.file.history',
	'',
	$componentParameters,
	$component
);

