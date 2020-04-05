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
$pageId = "user_files";
include("util_menu.php");
include("util_profile.php");
?>

<div class="bx-disk-container posr">
	<table style="width: 100%;" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<?php
				$APPLICATION->IncludeComponent(
					'bitrix:disk.folder.list',
					'',
					array_merge($arResult, array(
						'TRASH_MODE' => true,
						'STORAGE' => $arResult['VARIABLES']['STORAGE'],
						'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_TRASHCAN_LIST'], array('TRASH_PATH' => '', 'user_id' => $arResult['VARIABLES']['user_id'])),
						'URL_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('PATH' => '', 'user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_EXTERNAL_LINK_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_EXTERNAL_LINK_LIST'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_TRASHCAN_LIST'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_TRASHCAN_FILE_VIEW' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_TRASHCAN_FILE_VIEW'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'URL_TO_EMPTY_TRASHCAN' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('PATH' => '')),
						'PATH_TO_FILE_HISTORY' => CComponentEngine::MakePathFromTemplate(
							$arResult['PATH_TO_USER_DISK_FILE_HISTORY'],
							array('user_id' => $arResult['VARIABLES']['user_id'])
						),

						'FOLDER_ID' => $arResult['VARIABLES']['FOLDER_ID'],
						'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
						'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
					)),
					$component
				);?>
				</td>
			</tr>

	</table>
</div>
