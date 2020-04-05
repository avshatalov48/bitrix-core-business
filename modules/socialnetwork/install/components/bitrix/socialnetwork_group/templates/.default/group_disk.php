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

$folder = \Bitrix\Disk\Folder::loadById($arResult['VARIABLES']['FOLDER_ID']);
switch(LANGUAGE_ID)
{
	case 'en':
	case 'ru':
	case 'ua':
	case 'de':
	case 'br':
	case 'la':
	case 'sc':
	case 'tc':
		$bannerName = 'disk_banner2_' . LANGUAGE_ID .  '.png';
		break;
	default:
		$bannerName = 'disk_banner2_' . Loc::getDefaultLang(LANGUAGE_ID) .  '.png';
		break;
}
?>
<?
$pageId = "group_files";
include("util_group_menu.php");
include("util_group_profile.php");
?>

<div class="bx-disk-container posr" id="bx-disk-container">
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
							'FOLDER' => $folder,

							'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_LIST'], array('TRASH_PATH' => '', 'group_id' => $arResult['VARIABLES']['group_id'])),
							'URL_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('PATH' => '', 'group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_EXTERNAL_LINK_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_EXTERNAL_LINK_LIST'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_LIST'], array('group_id' => $arResult['VARIABLES']['group_id'])),

							'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
							'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],

							'TYPE' => 'list'
						),
						$component
					);
					?>
				</div>
				<?php
				$APPLICATION->IncludeComponent(
					'bitrix:disk.folder.list',
					'',
					array_merge($arResult, array(
						'STORAGE' => $arResult['VARIABLES']['STORAGE'],
						'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
						'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
						'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
						'PATH_TO_DISK_BIZPROC_WORKFLOW_ADMIN' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_BIZPROC_WORKFLOW_ADMIN'], array('group_id' => $arResult['VARIABLES']['group_id'])),
						'PATH_TO_DISK_START_BIZPROC' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_START_BIZPROC'], array('group_id' => $arResult['VARIABLES']['group_id'])),
						'PATH_TO_DISK_TASK_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_TASK_LIST'], array('group_id' => $arResult['VARIABLES']['group_id'])),
						'PATH_TO_DISK_TASK' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_TASK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
						'FOLDER' => $folder,
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

				<div class="bx-disk-sidebar-section">
					<?php
					$APPLICATION->IncludeComponent(
						'bitrix:disk.breadcrumbs.tree',
						'',
						array_merge($arResult, array(
							'STORAGE' => $arResult['VARIABLES']['STORAGE'],
							'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
							'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),


							'FOLDER' => $folder,
							'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
							'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
						)),
						$component
					);?>


				</div>

				<? if(!Desktop::isDesktopDiskInstall() && Banner::isActive('install_disk')) {?>
				<div class="bx-disk-sidebar-section">
					<a href="javascript: BX.Disk.deactiveBanner('install_disk'); BX.Disk.getDownloadDesktop();"><img src="/bitrix/images/disk/<?= $bannerName ?>" alt=""></a>
				</div>
				<? }

				if ($folder->canAdd($arResult['VARIABLES']['STORAGE']->getCurrentUserSecurityContext()))
				{
					$groupFields = \CSocNetGroup::getByID($arResult['VARIABLES']['group_id']);
					if (
						!empty($groupFields)
						&& is_array($groupFields)
						&& !(
							isset($groupFields['CLOSED'])
							&& $groupFields['CLOSED'] == 'Y'
							&& \Bitrix\Main\Config\Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y'
						)
					)
					{
						?>
						<div class="bx-disk-sidebar-section">
							<div class="wduf-uploader">
								<span class="bx-disk wd-fa-add-file-light">
									<span class="wd-fa-add-file-light-text">
										<span class="wd-fa-add-file-light-title">
											<span class="wd-fa-add-file-light-title-text"><?= Loc::getMessage('DISK_UPLOAD_FILE_OR_IMAGE') ?></span>
										</span>
										<span class="wd-fa-add-file-light-descript"><?= Loc::getMessage('DISK_UPLOAD_WITH_FILE_DRAG_AND_DROP') ?></span>
									</span>
								</span>
								<input type="file" size="1" multiple="multiple" class="wduf-fileUploader" id="BXDiskRightInputPlug">
							</div>
						</div>
						<?
					}
 				} ?>
			</td>
		</tr>
	</table>
<?$APPLICATION->IncludeComponent(
	'bitrix:disk.file.upload',
	'',
	array(
		'STORAGE' => $arResult['VARIABLES']['STORAGE'],
		'FOLDER' => $folder,
		'CID' => 'FolderList',
		'INPUT_CONTAINER' => '((BX.__tmpvar=BX.findChild(BX("folder_toolbar"), {className : "element-upload"}, true))&&BX.__tmpvar?BX.__tmpvar.parentNode:null)',
		'DROPZONE' => 'BX("bx-disk-container")'
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
<script type="text/javascript">
BX.ready(function(){
	if (BX('BXDiskRightInputPlug') && BX.DiskUpload.getObj('FolderList'))
	{
		BX.DiskUpload.getObj('FolderList').agent.init(BX('BXDiskRightInputPlug'));
	}
});
</script>
</div>
