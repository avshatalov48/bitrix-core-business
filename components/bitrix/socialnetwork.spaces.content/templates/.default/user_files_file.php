<?php
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
	$APPLICATION->IncludeComponent(
		'bitrix:disk.file.view',
		'',
		[
			'STORAGE' => $arParams['STORAGE'],
			'FILE_ID' => $arParams['FILE_ID'],
			'RELATIVE_PATH' => $arParams['RELATIVE_PATH'],

			'PATH_TO_FOLDER_LIST' => $arParams['PATH_TO_FOLDER_LIST'],
			'PATH_TO_FILE_VIEW' => $arParams['PATH_TO_FILE_VIEW'],

			'PATH_TO_FILE_HISTORY' => $arParams['PATH_TO_FILE_HISTORY'],
			'PATH_TO_TRASHCAN_LIST' => $arParams['PATH_TO_TRASHCAN_LIST'],
			'PATH_TO_TRASHCAN_FILE_VIEW' => $arParams['PATH_TO_TRASHCAN_FILE_VIEW'],

			'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP_DISCUSSIONS'],
			'PATH_TO_GROUP_GENERAL' => $arParams['PATH_TO_GROUP_DISCUSSIONS'],
			'PATH_TO_GROUP_CALENDAR' => $arParams['PATH_TO_GROUP_CALENDAR'],
			'PATH_TO_GROUP_DISK' => $arParams['PATH_TO_GROUP_FILES'],
			'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],
		],
	);
	?>
</div>
