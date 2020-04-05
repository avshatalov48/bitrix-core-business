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

if(isset($_REQUEST['document_id']))
{
	$backUrl = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP_DISK"], array('group_id' => $arResult['VARIABLES']['group_id'], "PATH" => ""));
}
else
{
	$backUrl = urldecode($_REQUEST["back_url"]);
	$backUrl .= '#tab-bp';
}
if(!preg_match('#^(?:/|\?|https?://)(?:\w|$)#D', $backUrl))
{
	$backUrl = '#';
}

$arResult['TOOLBAR'] = array(
	'BUTTONS' => array(
		array(
			'TEXT' => Loc::getMessage('DISK_FILE_VIEW_START_BIZPROC_GO_BACK_TEXT'),
			'TITLE' => Loc::getMessage('DISK_FILE_VIEW_START_BIZPROC__GO_BACK_TITLE'),
			'LINK' => $backUrl,
			'ICON' => 'back',
		),
	),
);

$APPLICATION->includeComponent(
	'bitrix:disk.interface.toolbar',
	'',
	array(
		'TOOLBAR_ID' => 'bp_start_toolbar',
		'CLASS_NAME' => 'bx-filepage',
		'BUTTONS'    => $arResult['TOOLBAR']['BUTTONS'],
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);
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