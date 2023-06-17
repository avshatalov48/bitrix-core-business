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

$pageId = "user_files";
include("util_menu.php");
include("util_profile.php");


if (($_REQUEST['IFRAME'] ?? null) === 'Y')
{
	$this->SetViewTarget("below_pagetitle");
	$APPLICATION->IncludeComponent(
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
	);
	$this->EndViewTarget();

	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:disk.folder.list",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => array_merge($arResult, array(
				'STORAGE' => $arResult['VARIABLES']['STORAGE'],
				'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('user_id' => $arResult['VARIABLES']['user_id'])),
				'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
				'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
				'PATH_TO_FILE_HISTORY' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_USER_DISK_FILE_HISTORY'],
					array('user_id' => $arResult['VARIABLES']['user_id'])
				),

				'FOLDER' => $folder,
				'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
				'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
			)),
			"POPUP_COMPONENT_PARENT" => $component,
			"USE_UI_TOOLBAR" => "Y",
		)
	);
}
else
{
?>
	<div class="bx-disk-container posr" id="bx-disk-container">
		<table style="width: 100%;" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<?php
					$APPLICATION->IncludeComponent(
						'bitrix:disk.folder.list',
						'',
						array_merge($arResult, array(
							'STORAGE' => $arResult['VARIABLES']['STORAGE'],
							'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_FILE_HISTORY' => CComponentEngine::MakePathFromTemplate(
								$arResult['PATH_TO_USER_DISK_FILE_HISTORY'],
								array('user_id' => $arResult['VARIABLES']['user_id'])
							),

							'FOLDER' => $folder,
							'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
							'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
						)),
						$component
					);?>
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
	</div>
<?
}
?>

<script type="text/javascript">
BX.ready(function(){
	if (BX('BXDiskRightInputPlug') && BX.DiskUpload.getObj('FolderList'))
	{
		BX.DiskUpload.getObj('FolderList').agent.init(BX('BXDiskRightInputPlug'));
	}
});
</script>
