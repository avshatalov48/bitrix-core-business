<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
?>

<?
$APPLICATION->IncludeComponent(
	'bitrix:disk.folder.list',
	'',
	array_merge($arResult, array(
		'TRASH_MODE' => true,
		'PATH_TO_TRASHCAN_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_TRASHCAN_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_FILE_VIEW'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
		'STORAGE' => $arResult['VARIABLES']['STORAGE'],
		'FOLDER_ID' => $arResult['VARIABLES']['FOLDER_ID'],
		'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
	)),
	$component
);?>