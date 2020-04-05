<?php
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
?>
<?
$pageId = "group_files";
include("util_group_menu.php");
include("util_group_profile.php");
?>

<div class="bx-disk-container posr">
	<table style="width: 100%;" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<div class="bx-disk-interface-toolbar-container">
					<?php
					$APPLICATION->IncludeComponent(
						'bitrix:disk.folder.toolbar',
						'',
						array(
							'STORAGE' => $arResult['VARIABLES']['STORAGE'],
							'FOLDER_ID' => $arResult['VARIABLES']['FOLDER_ID'],
							'MODE' => 'trashcan',

							'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_LIST'], array('TRASH_PATH' => '', 'group_id' => $arResult['VARIABLES']['group_id'])),
							'URL_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('PATH' => '', 'group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_EXTERNAL_LINK_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_EXTERNAL_LINK_LIST'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_LIST'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'URL_TO_EMPTY_TRASHCAN' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('PATH' => '')),

							'TYPE' => 'list',

							'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
							'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
						),
						$component
					);
					?>
					</div>
					<?php
					$APPLICATION->IncludeComponent(
						'bitrix:disk.trashcan',
						'',
						array_merge($arResult, array(
							'STORAGE' => $arResult['VARIABLES']['STORAGE'],
							'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_LIST'], array('TRASH_PATH' => '', 'group_id' => $arResult['VARIABLES']['group_id'])),
							'URL_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('PATH' => '', 'group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_EXTERNAL_LINK_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_EXTERNAL_LINK_LIST'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_LIST'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_TRASHCAN_FILE_VIEW' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_FILE_VIEW'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'URL_TO_EMPTY_TRASHCAN' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('PATH' => '')),

							'FOLDER_ID' => $arResult['VARIABLES']['FOLDER_ID'],
							'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
							'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
						)),
						$component
					);?>
				</td>
				<td class="bx-disk-table-sidebar-cell" style="">
					<div id="bx_disk_empty_select_section" class="bx-disk-sidebar-section">
						<div class="bx-disk-info-panel">
							<div class="bx-disk-info-panel-relative tac">
								<div class="bx-disk-info-panel-icon-empty"><br></div>
									<div class="bx-disk-info-panel-empty-text">
										<?= Loc::getMessage('DISK_VIEW_SMALL_DETAIL_SIDEBAR') ?>
									</div>
							</div>
						</div>
					</div>

					<div id="disk_info_panel"></div>
				</td>
			</tr>

	</table>
</div>
