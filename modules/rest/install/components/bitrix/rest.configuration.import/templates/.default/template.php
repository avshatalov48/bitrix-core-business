<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CAllMain $APPLICATION */
/** @global CAllUser $USER */
/** @global CAllDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\FileInput;
Loc::loadMessages(__FILE__);
Extension::load(["ui.buttons", "ui.common", "ui.notification"]);
$containerId = 'rest-configuration-import';

$titleBlock = '';
if ($arParams['MODE'] == 'ROLLBACK')
{
	$titleBlock = Loc::getMessage('REST_CONFIGURATION_IMPORT_ROLLBACK_TITLE_BLOCK');
}
else
{
	if(!empty($arResult['MANIFEST']['IMPORT_TITLE_BLOCK']))
	{
		$titleBlock = $arResult['MANIFEST']['IMPORT_TITLE_BLOCK'];
	}
	else
	{
		$titleBlock = Loc::getMessage('REST_CONFIGURATION_IMPORT_TITLE_BLOCK');
	}
}

?>
<div id="<?=$containerId?>" class="rest-configuration">
	<div class="rest-configuration-wrapper">
		<div class="rest-configuration-title"><?=htmlspecialcharsbx($titleBlock)?></div>
		<? if($arResult['IMPORT_ACCESS'] === true):?>
			<? if($arParams['MODE'] == 'ROLLBACK'):?>
				<? if(!empty($arResult['IMPORT_FOLDER_FILES'])):?>
					<?php
					$APPLICATION->includeComponent(
						'bitrix:rest.configuration.install',
						'',
						array(
							'IMPORT_PATH' => $arResult['IMPORT_FOLDER_FILES'],
							'IMPORT_MANIFEST' => $arResult['IMPORT_MANIFEST_FILE'],
							'APP' => $arResult['APP'],
							'MODE' => $arParams['MODE'],
							'MANIFEST_CODE' => $arResult['MANIFEST_CODE'],
							'UNINSTALL_APP_ON_FINISH' => $arResult['UNINSTALL_APP_ON_FINISH']
						),
						$component,
						array('HIDE_ICONS' => 'Y')
					);
					?>
				<? elseif(!empty($arResult['IMPORT_ROLLBACK_DISK_FOLDER_ID'])):?>
					<?php
					$APPLICATION->includeComponent(
						'bitrix:rest.configuration.install',
						'',
						array(
							'IMPORT_DISK_STORAGE_PARAMS' => $arResult['IMPORT_ROLLBACK_STORAGE_PARAMS'],
							'IMPORT_DISK_FOLDER_ID' => $arResult['IMPORT_ROLLBACK_DISK_FOLDER_ID'],
							'MODE' => $arParams['MODE'],
							'MANIFEST_CODE' => $arResult['MANIFEST_CODE'],
							'IMPORT_MANIFEST' => $arResult['IMPORT_MANIFEST_FILE'],
							'UNINSTALL_APP_ON_FINISH' => $arResult['UNINSTALL_APP_ON_FINISH']
						),
						$component,
						array('HIDE_ICONS' => 'Y')
					);
					?>
				<? elseif(!empty($arResult['ROLLBACK_ITEMS'])):?>
					<div class="rest-configuration-start-icon-main rest-configuration-start-icon-main-zip">
						<div class="rest-configuration-start-icon-refresh"></div>
						<div class="rest-configuration-start-icon"></div>
						<div class="rest-configuration-start-icon-circle"></div>
					</div>
					<p  class="rest-configuration-info"><?=Loc::getMessage("REST_CONFIGURATION_IMPORT_ROLLBACK_MODE_DESCRIPTION");?></p>
					<form method="post">
						<?=bitrix_sessid_post()?>
						<? foreach($arResult['ROLLBACK_ITEMS'] as $item):?>
							<label><input type="radio" name="ROLLBACK_ID" value="<?=$item['ID']?>" required>
								<?=htmlspecialcharsbx($item['NAME'])?>
							</label>
							<br>
						<? endforeach;?>
						<br>
						<button class="ui-btn ui-btn-primary ui-btn-round"><?=Loc::getMessage("REST_CONFIGURATION_IMPORT_ROLLBACK_SUBMIT_BTN")?></button>
					</form>
				<? else:?>
					<div class="rest-configuration-start-icon-main rest-configuration-start-icon-main-success">
						<div class="rest-configuration-start-icon-refresh"></div>
						<div class="rest-configuration-start-icon"></div>
						<div class="rest-configuration-start-icon-circle"></div>
					</div>
					<p  class="rest-configuration-info"><?=Loc::getMessage("REST_CONFIGURATION_IMPORT_EASY_DELETE_APP")?></p>
				<? endif;?>
			<? elseif(!empty($arResult['IMPORT_CONTEXT'])):?>
				<?php
					$APPLICATION->includeComponent(
					'bitrix:rest.configuration.install',
					'',
					array(
						'IMPORT_CONTEXT' => $arResult['IMPORT_CONTEXT'],
						'IMPORT_MANIFEST' => $arResult['IMPORT_MANIFEST_FILE'],
						'MANIFEST_CODE' => $arResult['MANIFEST_CODE'],
						'APP' => $arResult['APP']
					),
					$component,
					array('HIDE_ICONS' => 'Y')
				);
				?>
			<? else:
				if(!empty($arResult['MANIFEST']['IMPORT_DESCRIPTION_UPLOAD']))
				{
					$importFileDescription = $arResult['MANIFEST']['IMPORT_DESCRIPTION_UPLOAD'];
				}
				else
				{
					$importFileDescription = Loc::getMessage('REST_CONFIGURATION_IMPORT_SAVE_FILE_DESCRIPTION');
				}
				?>
				<div class="rest-configuration-start-icon-main rest-configuration-start-icon-main-zip">
					<div class="rest-configuration-start-icon-refresh"></div>
					<div class="rest-configuration-start-icon"></div>
					<div class="rest-configuration-start-icon-circle"></div>
				</div>
				<form id="<?=$containerId?>-file-form" method="post" enctype="multipart/form-data">
					<?=bitrix_sessid_post()?>
					<div class="rest-configuration-controls rest-configuration-upload-file">
						<label class="ui-btn ui-btn-lg ui-btn-primary">
							<input id="<?=$containerId?>-file-upload" type="file" name="CONFIGURATION" >
							<?=Loc::getMessage('REST_CONFIGURATION_IMPORT_SAVE_FILE_BTN')?>
						</label>
					</div>
				</form>
				<p class="rest-configuration-info"><?=htmlspecialcharsbx($importFileDescription)?></p>
			<? endif;?>
		<? else:?>
			<div class="rest-configuration-start-icon-main rest-configuration-start-icon-main-error">
				<div class="rest-configuration-start-icon-refresh"></div>
				<div class="rest-configuration-start-icon"></div>
				<div class="rest-configuration-start-icon-circle"></div>
			</div>
			<p class="rest-configuration-info"><?=Loc::getMessage('REST_CONFIGURATION_IMPORT_APP_ERROR_TYPE')?></p>
		<? endif;?>
		<script type="text/javascript">
			BX.ready(function () {
				BX.message(<?=Json::encode(
						[
							'REST_CONFIGURATION_IMPORT_ERRORS_MAX_FILE_SIZE' => Loc::getMessage(
								'REST_CONFIGURATION_IMPORT_ERRORS_MAX_FILE_SIZE',
								[
									'#SIZE#' => $arResult['MAX_FILE_SIZE']['MEGABYTE']
								]
							),
						]
					);
					?>);
				BX.Rest.Configuration.Import.init(<?=Json::encode(
					[
						'id' => $containerId,
						'signedParameters' => $this->getComponent()->getSignedParameters(),
						'fileMaxSize' => $arResult['MAX_FILE_SIZE']['BYTE']
					]
				)?>);
			});
		</script>
	</div>
</div>