<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Disk\Folder;
use Bitrix\Main\Context;

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

$folder = Folder::loadById($arResult['VARIABLES']['FOLDER_ID']);

$pageId = "group_files";

include("util_group_menu.php");
include("util_group_profile.php");

$componentParams = array_merge($arResult, [
	'STORAGE' => $arResult['VARIABLES']['STORAGE'],
	'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'],
		['group_id' => $arResult['VARIABLES']['group_id']]),
	'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'],
		['group_id' => $arResult['VARIABLES']['group_id']]),
	'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'],
		['group_id' => $arResult['VARIABLES']['group_id']]),
	'PATH_TO_DISK_BIZPROC_WORKFLOW_ADMIN' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_BIZPROC_WORKFLOW_ADMIN'],
		['group_id' => $arResult['VARIABLES']['group_id']]),
	'PATH_TO_DISK_START_BIZPROC' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_START_BIZPROC'],
		['group_id' => $arResult['VARIABLES']['group_id']]),
	'PATH_TO_DISK_TASK_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_TASK_LIST'],
		['group_id' => $arResult['VARIABLES']['group_id']]),
	'PATH_TO_DISK_TASK' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_TASK'],
		['group_id' => $arResult['VARIABLES']['group_id']]),
	'PATH_TO_FILE_HISTORY' => CComponentEngine::MakePathFromTemplate(
		$arResult['PATH_TO_GROUP_DISK_FILE_HISTORY'],
		['group_id' => $arResult['VARIABLES']['group_id']]
	),
	'FOLDER' => $folder,
	'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
	'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
]);

if (($_REQUEST['IFRAME'] ?? null) === 'Y')
{
	$this->SetViewTarget("below_pagetitle");
	$APPLICATION->IncludeComponent(
		'bitrix:disk.file.upload',
		'',
		[
			'STORAGE' => $arResult['VARIABLES']['STORAGE'],
			'FOLDER' => $folder,
			'CID' => 'FolderList',
			'INPUT_CONTAINER' => '((BX.__tmpvar=BX.findChild(BX("folder_toolbar"), {className : "element-upload"}, true))&&BX.__tmpvar?BX.__tmpvar.parentNode:null)',
			'DROPZONE' => 'BX("bx-disk-container")',
		],
		$component,
		["HIDE_ICONS" => "Y"]
	);
	$this->EndViewTarget();

	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		[
			'POPUP_COMPONENT_NAME' => "bitrix:disk.folder.list",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParams,
			"POPUP_COMPONENT_PARENT" => $component,
			"USE_UI_TOOLBAR" => "Y",
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
			'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
			'UI_TOOLBAR_FAVORITES_TITLE_TEMPLATE' => $arResult['PAGES_TITLE_TEMPLATE'],
		]
	);
}

$APPLICATION->SetPageProperty('FavoriteTitleTemplate', $arResult['PAGES_TITLE_TEMPLATE']);
?>

	<div class="bx-disk-container posr" id="bx-disk-container">
		<table style="width: 100%;" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<?php

					include("util_copy_disk.php");

					$APPLICATION->IncludeComponent(
						'bitrix:disk.folder.list',
						'',
						$componentParams,
						$component
					); ?>
				</td>
			</tr>
		</table>
		<?
		$APPLICATION->IncludeComponent(
			'bitrix:disk.file.upload',
			'',
			[
				'STORAGE' => $arResult['VARIABLES']['STORAGE'],
				'FOLDER' => $folder,
				'CID' => 'FolderList',
				'INPUT_CONTAINER' => '((BX.__tmpvar=BX.findChild(BX("folder_toolbar"), {className : "element-upload"}, true))&&BX.__tmpvar?BX.__tmpvar.parentNode:null)',
				'DROPZONE' => 'BX("bx-disk-container")',
			],
			$component,
			["HIDE_ICONS" => "Y"]
		); ?>
		<script type="text/javascript">
			BX.ready(function() {
				if (BX('BXDiskRightInputPlug') && BX.DiskUpload.getObj('FolderList'))
				{
					BX.DiskUpload.getObj('FolderList').agent.init(BX('BXDiskRightInputPlug'));
				}
			});
		</script>
	</div>
<?php