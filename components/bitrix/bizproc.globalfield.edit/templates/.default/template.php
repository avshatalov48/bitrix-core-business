<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */

global $APPLICATION;

CUtil::InitJSCore(['bp_field_type', 'date']);

\Bitrix\Main\UI\Extension::load([
	'ui.forms',
	'ui.layout-form',
	'ui.sidepanel-content',
	'ui.dialogs.messagebox',
	'bizproc.globals',
]);
?>

<?php $this->SetViewTarget('inside_pagetitle') ?>
	<button class="ui-btn ui-btn-light-border" onclick="top.BX.Helper.show('redirect=detail&code=14922854');">
		<?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_HEPL') ?>
	</button>
<?php $this->EndViewTarget() ?>

<form id="bizproc.globalfield.edit-form">
	<div class="ui-form">
		<div class="ui-form-row">
			<div class="ui-form-label">
				<div class="ui-ctl-label-text"><?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_NAME') ?></div>
			</div>
			<div class="ui-form-content">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input
						name="NAME"
						type="text"
						class="ui-ctl-element"
						placeholder="<?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY') ?>"
						value="<?= htmlspecialcharsbx($arResult['fieldInfo']['Name'] ?? null) ?>"
					>
				</div>
			</div>
		</div>
		<div class="ui-form-row">
			<div class="ui-form-label">
				<div class="ui-ctl-label-text"><?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_TYPE')?></div>
			</div>
			<div class="ui-form-content">
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select
						class="ui-ctl-element"
						<?= $arResult['disabled'] ?>
						name="TYPE"
						onchange="BX.Bizproc.Component.GlobalFieldEditComponent.Instance.editInputValue(
							this.value,
							<?= htmlspecialcharsbx(CUtil::PhpToJSObject($arResult['fieldInfo'])) ?>
							)"
					>
						<?php foreach ($arResult['fieldTypes'] as $key => $value): ?>
							<option
								value="<?= htmlspecialcharsbx($key) ?>"
								<?= (isset($arResult['fieldInfo']['Type']) && $key === $arResult['fieldInfo']['Type']) ? ' selected' : '' ?>
							>
								<?= htmlspecialcharsbx($value) ?>
							</option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="ui-form-row">
			<div class="ui-form-label">
				<div class="ui-ctl-label-text"><?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_MULTIPLE')?></div>
			</div>
			<div class="ui-form-content">
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select
						id="bizproc_globalfield_edit_form_input_multiple"
						class="ui-ctl-element"
						name="MULTIPLE"
						<?= $arResult['disabled']?>
						onchange="BX.Bizproc.Component.GlobalFieldEditComponent.Instance.editInputValue(
							document.getElementsByName('TYPE')[0].value,
							{Multiple: this.value}
						)"
					>
						<option value="N" <?= (isset($arResult['fieldInfo']['Multiple']) && $arResult['fieldInfo']['Multiple'] === false) ? ' selected' : '' ?>>
							<?= \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_NO') ?>
						</option>
						<option value="Y" <?= (isset($arResult['fieldInfo']['Multiple']) && $arResult['fieldInfo']['Multiple'] === true) ? ' selected' : ''?>>
							<?= \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_YES') ?>
						</option>
					</select>
				</div>
			</div>
		</div>
		<div class="ui-form-row">
			<div class="ui-form-label">
				<div class="ui-ctl-label-text"><?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_VALUE') ?></div>
			</div>
			<div class="ui-form-content">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input
						id="bizproc_globalfield_edit_form_input_value"
						name="VALUE"
						type="text"
						class="ui-ctl-element"
						placeholder="<?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY') ?>"
					>
				</div>
			</div>
		</div>
		<div class="ui-form-row">
			<div class="ui-form-label">
				<div class="ui-ctl-label-text"><?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_VISIBILITY')?></div>
			</div>
			<div class="ui-form-content">
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select class="ui-ctl-element" name="VISIBILITY" <?= $arResult['disabled']?>>
						<?php foreach ($arResult['visibilityTypes'] as $key => $value): ?>
							<option
								value="<?= htmlspecialcharsbx($key) ?>"
								<?= isset($arResult['fieldInfo']['Visibility']) && mb_strtoupper($key) === $arResult['fieldInfo']['Visibility'] ? ' selected' : '' ?>
							>
								<?= htmlspecialcharsbx($value) ?>
							</option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div class="ui-form-row">
			<div class="ui-form-label">
				<div class="ui-ctl-label-text"><?= GetMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_DESCRIPTION')?></div>
			</div>
			<div class="ui-form-content">
				<div class="ui-ctl ui-ctl-textarea ui-ctl-resize-y ui-ctl-w100" style="height: auto">
					<textarea class="ui-ctl-element ui-ctl-resize-y" name="DESCRIPTION"><?= htmlspecialcharsbx($arResult['fieldInfo']['Description'] ?? null) ?></textarea>
				</div>
			</div>
		</div>
	</div>
</form>

<div data-role="globalfield-edit-buttons">
	<?php $APPLICATION->IncludeComponent(
		'bitrix:ui.button.panel',
		'',
		[
			'BUTTONS' => [
				[
					'ID' => 'bizproc.globalfield.edit-btn-save',
					'TYPE' => 'save',
				],
				'cancel'
			]
		]
	) ?>
</div>

<script>
	BX.ready(function () {
		BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)) ?>);

		BX.Bizproc.Component.GlobalFieldEditComponent.Instance = new BX.Bizproc.Component.GlobalFieldEditComponent({
			property: <?= CUtil::PhpToJSObject($arResult['fieldInfo']) ?>,
			documentType: <?= CUtil::PhpToJSObject($arParams['DOCUMENT_TYPE']) ?>,
			signedDocumentType: '<?= CUtil::JSEscape($arParams['~DOCUMENT_TYPE_SIGNED']) ?>',
			mode: '<?= CUtil::JSEscape($arResult['mode']) ?>',
			types: <?= CUtil::PhpToJSObject($arResult['fieldTypes']) ?>,
			visibilityNames: <?= CUtil::PhpToJSObject($arResult['visibilityNames']) ?>,

			inputValueId: 'bizproc_globalfield_edit_form_input_value',

			multipleNode: document.getElementById('bizproc_globalfield_edit_form_input_multiple'),
			saveButtonNode: document.getElementById('bizproc.globalfield.edit-btn-save'),
			form: document.forms['bizproc.globalfield.edit-form'],

			slider: BX.getClass('BX.SidePanel.Instance') ? BX.SidePanel.Instance.getSliderByWindow(window) : null,
		});

		BX.Bizproc.Component.GlobalFieldEditComponent.Instance.init();
	});
</script>