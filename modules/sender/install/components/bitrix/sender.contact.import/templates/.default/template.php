<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-sender-contact-import';
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.ContactImport.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUri' => $arResult['ACTION_URI'],
			'listId' => $arParams['LIST_ID'],
			'blacklist' => $arParams['BLACKLIST'],
			'pathToList' => $arParams['PATH_TO_LIST'],
			'mess' => array(
				'dlgBtnSelect' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_SELECT'),
				'dlgBtnCancel' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_CANCEL'),
				'dlgPreviewTitle' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_PREVIEW_TITLE'),
			)
		))?>);
	});
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-contact-import-wrap">
	<?=bitrix_sessid_post()?>

	<div class="sender-contact-import-list-box">
		<div class="sender-contact-import-list-caption"><?=Loc::getMessage('SENDER_CONTACT_IMPORT_RECIPIENTS')?></div>
		<textarea data-role="text-list" class="sender-contact-import-list-textarea"></textarea>

		<div data-role="loader" class="sender-contact-import-loader" style="display: none;">
			<div class="sender-contact-import-overlay"></div>
			<div class="sender-contact-import-progress-box">
				<div class="sender-contact-import-progress-inner">
					<div class="sender-contact-import-progress">
						<span class="sender-contact-import-progress-name"><?=Loc::getMessage('SENDER_CONTACT_IMPORT_LOADING')?>:</span>
						<span data-role="process" class="sender-contact-import-progress-number">0</span>
						<span class="sender-contact-import-progress-percent">%</span>
					</div>
					<div class="sender-contact-import-progress-bar">
						<div data-role="indicator" class="sender-contact-import-progress-bar-item"></div>
					</div>
				</div>
			</div>
			<?/*
		<div class="sender-contact-import-popup-confirm">
			<div class="sender-contact-import-popup-close">
				<div class="sender-contact-import-popup-close-item"></div>
			</div>
			<div class="sender-contact-import-popup-confirm-text">Status</div>
		</div>
		*/?>
		</div>
		
	</div>

	<div>
		<?if ($arParams['SHOW_SETS']):?>
			<div class="sender-contact-import-set">
				<?if ($arParams['LIST_ID']):?>
					<?=Loc::getMessage('SENDER_CONTACT_IMPORT_SET')?>
					<?=Loc::getMessage('SENDER_CONTACT_IMPORT_SET_NAME')?>
					"<?=htmlspecialcharsbx($arResult['SET_NAME'])?>"
				<?else:?>
					<?=Loc::getMessage('SENDER_CONTACT_IMPORT_SET')?>:
					<select data-role="set-id"
						onchange="this.nextElementSibling.style.display = this.value ? 'none' : '';"
						class="sender-form-control sender-form-control-select"
					>
						<option value="">[<?=Loc::getMessage('SENDER_CONTACT_IMPORT_SET_ADD')?>]</option>
						<?foreach ($arResult['SET_LIST'] as $index => $set):?>
							<option value="<?=htmlspecialcharsbx($set['ID'])?>" <?=(!$index ? 'selected' : '')?>>
								<?=htmlspecialcharsbx($set['NAME'])?>
							</option>
						<?endforeach;?>
					</select>
					<span style="<?=(empty($arResult['SET_LIST']) ? '' : 'display: none;')?>">
						<?=Loc::getMessage('SENDER_CONTACT_IMPORT_SET_NAME')?>
						<input type="text" class="sender-form-control">
					</span>
				<?endif;?>
			</div>
		<?endif;?>
	</div>

	<div class="sender-box-informer">
		<div class="sender-box-informer-text"><?=Loc::getMessage('SENDER_CONTACT_IMPORT_FORMAT_DESC')?></div>
	</div>

	<?
	$APPLICATION->IncludeComponent(
		"bitrix:sender.ui.button.panel",
		"",
		array(
			'SAVE' => array(
				'CAPTION' => Loc::getMessage('SENDER_CONTACT_IMPORT_BUTTON_LOAD')
			),
			'CANCEL' => array(
				'URL' => $arParams['PATH_TO_LIST']
			),
		),
		false
	);
	?>
</div>