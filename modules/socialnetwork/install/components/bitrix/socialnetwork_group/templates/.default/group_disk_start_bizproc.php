<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

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
/** @var \Bitrix\Disk\Internals\BaseComponent $component */

$storage = \Bitrix\Disk\Driver::getInstance()->getStorageByGroupId($arResult['VARIABLES']['group_id']);
$arResult['VARIABLES']['STORAGE'] = $storage;
?>
<div class="bx-disk-bizproc-section">
<?
$APPLICATION->IncludeComponent('bitrix:disk.bizproc.start', '', Array(
	'MODULE_ID'     => \Bitrix\Disk\Driver::INTERNAL_MODULE_ID,
	'STORAGE_ID' => $arResult['VARIABLES']['STORAGE']->getId(),
	'DOCUMENT_ID'   => $arResult['VARIABLES']['ELEMENT_ID'],
	'SET_TITLE'     => 'Y'),
	$component,
	array('HIDE_ICONS' => 'Y')
);
?>
</div>