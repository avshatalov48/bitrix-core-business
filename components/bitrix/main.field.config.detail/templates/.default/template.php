<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load([
	'ui.forms',
	'ui.dialogs.messagebox',
	'main.loader',
	'ui.userfield',
	'ui.buttons',
	'ui.alerts',
    'date',
]);

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/dd.js');
$hasErrors = (!empty($arResult['errors']) && is_array($arResult['errors']));

if(!$hasErrors) {
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrappermenu',
		"",
		[
			'TITLE' => Loc::getMessage('MAIN_FIELD_CONFIG_SETTINGS'),
			'ITEMS' => [
				[
					'NAME' => Loc::getMessage('MAIN_FIELD_CONFIG_MENU_COMMON'),
					'ATTRIBUTES' => [
						'onclick' => 'BX.Main.UserField.Config.handleLeftMenuClick(' . ((int)$arResult['field']['ID']) . ', \'common\');',
						'data-role' => 'tab-common',
					],
					'ACTIVE' => true,
				],
				[
					'NAME' => Loc::getMessage('MAIN_FIELD_CONFIG_MENU_LIST'),
					'ATTRIBUTES' => [
						'onclick' => 'BX.Main.UserField.Config.handleLeftMenuClick(' . ((int)$arResult['field']['ID']) . ', \'list\');',
						'data-role' => 'tab-list',
						'style' => 'display: none;',
					],
				],
				[
					'NAME' => Loc::getMessage('MAIN_FIELD_CONFIG_MENU_LABELS'),
					'ATTRIBUTES' => [
						'onclick' => 'BX.Main.UserField.Config.handleLeftMenuClick(' . ((int)$arResult['field']['ID']) . ', \'labels\');',
						'data-role' => 'tab-labels',
					],
				],
				[
					'NAME' => Loc::getMessage('MAIN_FIELD_CONFIG_MENU_ADDITIONAL'),
					'ATTRIBUTES' => [
						'onclick' => 'BX.Main.UserField.Config.handleLeftMenuClick(' . ((int)$arResult['field']['ID']) . ', \'additional\');',
						'data-role' => 'tab-additional',
					],
				],
			],
		],
		$this->getComponent()
	);
}
?>
<div class="main-user-field-edit-container" id="main-user-field-edit-container">
	<div class="main-user-field-edit-tab main-user-field-edit-tab-current" data-tab="common">
		<div class="user-field-list-errors-container ui-alert ui-alert-danger"<?= (!$hasErrors ? ' style="display: none;"' : '') ;?>>
			<div class="main-user-field-error ui-alert-message" id="main-user-field-edit-errors">
			<?php if($hasErrors):
				foreach($arResult['errors'] as $error):
					echo htmlspecialcharsbx($error->getMessage());
				endforeach;
				return;
			endif;?>
			</div>
			<span class="ui-alert-close-btn" onclick="this.parentNode.style.display = 'none';"></span>
		</div>
		<input
			type="hidden"
			name="ID"
			value="<?= (int)$arResult['field']['ID']; ?>"
			data-role="main-user-field-id"
		>
		<input
			type="hidden"
			name="ENTITY_ID"
			value="<?= htmlspecialcharsbx($arResult['field']['ENTITY_ID']); ?>"
			data-role="main-user-field-entityId"
		>
		<div class="main-user-field-edit-input">
			<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($arResult['form']['userTypeId']['label']); ?></div>
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<select
						class="ui-ctl-element"
						name="USER_TYPE_ID"
						<?= ($arResult['field']['ID'] > 0) ? 'disabled="disabled"' : '' ;?>
						data-role="main-user-field-userTypeId"
				>
					<?php foreach($arResult['types'] as $type) :?>
						<option
								value="<?= htmlspecialcharsbx($type['USER_TYPE_ID']); ?>"
								<?= ($arResult['field']['USER_TYPE_ID'] === $type['USER_TYPE_ID'] ? 'selected="selected"' : ''); ?>
						><?= htmlspecialcharsbx($type['DESCRIPTION']); ?></option>
					<?php endforeach;?>
				</select>
			</div>
		</div>
		<div class="main-user-field-edit-input main-user-field-name<?= (!$arResult['field']['ID'] ? ' main-user-field-name-with-prefix' : ''); ?>">
			<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($arResult['form']['fieldName']['label']); ?></div>
			<?php if($arResult['field']['ID'] > 0): ?>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input
						type="text"
						class="ui-ctl-element"
						name="FIELD_NAME"
						value="<?= htmlspecialcharsbx($arResult['field']['FIELD_NAME']); ?>"
						disabled="disabled"
						data-role="main-user-field-fieldName"
					>
				</div>
			<?php else: ?>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-inline main-user-field-prefix">
					<input
						type="text"
						class="ui-ctl-element"
						name="FIELD_PREFIX"
						value="<?= htmlspecialcharsbx($arResult['form']['fieldName']['prefix']); ?>"
						disabled="disabled"
						data-role="main-user-field-fieldPrefix"
					>
				</div><div class="ui-ctl ui-ctl-textbox ui-ctl-inline main-user-field-prefix-name">
					<input
						type="text"
						class="ui-ctl-element"
						name="FIELD_NAME"
						value="<?= time(); ?>"
						data-role="main-user-field-fieldName"
					>
				</div>
			<?php endif; ?>
		</div>
		<?php foreach($arResult['form']['editFormLabel'] as $label):
			if(!$label['language']['isCurrent'])
			{
				continue;
			}
			?>
			<div
				class="main-user-field-edit-input main-user-field-label"
				data-language="<?= htmlspecialcharsbx($label['language']['id']); ?>"
			>
				<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($label['label']); ?></div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input
						type="text"
						class="ui-ctl-element"
						name="EDIT_FORM_LABEL[<?= htmlspecialcharsbx($label['language']['id']); ?>]"
						value="<?= htmlspecialcharsbx($arResult['field']['EDIT_FORM_LABEL'][$label['language']['id']]); ?>"
						data-role="main-user-field-editFormLabel"
					>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="main-user-field-edit-input">
			<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($arResult['form']['sort']['label']); ?></div>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
				<input
					type="text"
					class="ui-ctl-element"
					name="SORT"
					value="<?= (int)$arResult['field']['SORT']; ?>"
					data-role="main-user-field-sort"
				>
			</div>
		</div>
		<div class="main-user-field-edit-input main-user-field-edit-input-checkbox">
			<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
				<input
					type="checkbox"
					class="ui-ctl-element"
					name="MULTIPLE" value="Y"
					<?= ($arResult['field']['MULTIPLE'] === 'Y' ? ' checked="checked"' : '') ?>
					<?= ($arResult['field']['ID'] > 0 ? 'disabled="disabled"' : '') ?>
					data-role="main-user-field-multiple"
				>
				<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($arResult['form']['multiple']['label']); ?></div>
			</label>
		</div>
		<div class="main-user-field-edit-input main-user-field-edit-input-checkbox">
			<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
				<input
					type="checkbox"
					class="ui-ctl-element"
					name="MANDATORY" value="Y"
					<?= ($arResult['field']['MANDATORY'] === 'Y' ? ' checked="checked"' : '') ?>
					data-role="main-user-field-mandatory"
				>
				<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($arResult['form']['mandatory']['label']); ?></div>
			</label>
		</div>
		<div class="main-user-field-edit-input main-user-field-edit-input-checkbox">
			<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
				<input
					type="checkbox"
					class="ui-ctl-element"
					name="SHOW_FILTER" value="Y"
					<?= ($arResult['field']['SHOW_FILTER'] !== 'N' ? ' checked="checked"' : '') ?>
					data-role="main-user-field-showFilter"
				>
				<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($arResult['form']['showFilter']['label']); ?></div>
			</label>
		</div>
		<div class="main-user-field-edit-input main-user-field-edit-input-checkbox">
			<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
				<input
						type="checkbox"
						class="ui-ctl-element"
						name="IS_SEARCHABLE" value="Y"
						<?= ($arResult['field']['IS_SEARCHABLE'] === 'Y' ? ' checked="checked"' : '') ?>
						data-role="main-user-field-isSearchable"
				>
				<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($arResult['form']['isSearchable']['label']); ?></div>
			</label>
		</div>
	</div>
	<div class="main-user-field-edit-tab" data-tab="labels">
		<?php foreach($arResult['form']['editFormLabel'] as $label): ?>
			<div
					class="main-user-field-edit-input main-user-field-label"
					data-role="main-user-field-label-container"
					data-language="<?= htmlspecialcharsbx($label['language']['id']); ?>"
			>
				<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($label['label']); ?> (<?= htmlspecialcharsbx($label['language']['name']); ?>)</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input
							type="text"
							class="ui-ctl-element"
							name="EDIT_FORM_LABEL[<?= htmlspecialcharsbx($label['language']['id']); ?>]"
							value="<?= htmlspecialcharsbx($arResult['field']['EDIT_FORM_LABEL'][$label['language']['id']]); ?>"
							data-role="main-user-field-editFormLabel-<?= htmlspecialcharsbx($label['language']['id']); ?>"
					>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="main-user-field-edit-tab" data-tab="additional">
		<div class="main-user-field-edit-input" data-role="main-user-field-settings-container">
			<div class="main-user-field-settings-title">
				<span class="main-user-field-settings-title-text"><?= htmlspecialcharsbx($arResult['form']['settings']['label']); ?></span>
			</div>
			<form data-role="main-user-field-settings">
				<table class="main-user-field-edit-settings" data-role="main-user-field-settings-table">
					<?= $arResult['form']['settings']['html']; ?>
				</table>
			</form>
		</div>
	</div>
	<div class="main-user-field-edit-tab" data-tab="list">
		<div class="main-user-field-enum-row">
			<span class="main-user-field-enum-title"><?= Loc::getMessage('MAIN_FIELD_CONFIG_LIST_ITEMS_TITLE'); ?></span>
		</div>
		<div class="main-user-field-enum-row-list">
			<?php if(!empty($arResult['field']['ENUM'])):
				foreach($arResult['field']['ENUM'] as $enum): ?>
					<div class="main-user-field-enum-row" data-role="main-user-field-enum-row" data-id="<?= (int)$enum['ID'] ;?>">
						<div class="main-user-field-enum-row-inner ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row">
							<span class="main-user-field-enum-row-draggable" style=""></span>
							<input
								class="ui-ctl-element"
								type="text"
								name="ENUM[<?= (int)$enum['ID']; ?>][VALUE]"
								value="<?= htmlspecialcharsbx($enum['VALUE']); ?>"
								data-role="main-user-field-enum-value"
							>
							<div class="main-user-field-enum-delete" data-role="main-user-field-enum-delete"></div>
						</div>
					</div>
				<?php endforeach;
			endif;?>
			<div class="main-user-field-enum-row" data-role="main-user-field-enum-row">
				<div class="main-user-field-enum-row-inner ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row">
					<span class="main-user-field-enum-row-draggable" style=""></span>
					<input class="ui-ctl-element" type="text" name="ENUM[][VALUE]" value="" data-role="main-user-field-enum-value">
					<div class="main-user-field-enum-delete" data-role="main-user-field-enum-delete"></div>
				</div>
			</div>
		</div>
		<div class="main-user-field-edit-input">
			<span class="main-user-field-enum-add" data-role="main-user-field-enum-add"><?= Loc::getMessage('MAIN_FIELD_CONFIG_LIST_ITEMS_ADD'); ?></span>
		</div>
		<div class="main-user-field-edit-input">
			<div class="ui-ctl-label-text"><?= Loc::getMessage('MAIN_FIELD_CONFIG_LIST_ITEMS_DEFAULT') ?></div>
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<select
						class="ui-ctl-element"
						name="DEFAULT"
						data-role="main-user-field-enumDefault"
						<?= ($arResult['field']['MULTIPLE'] === 'Y' ? ' multiple="multiple"' : '') ?>
				>
					<option value="empty"><?= Loc::getMessage('MAIN_FIELD_CONFIG_LIST_ITEMS_DEFAULT_EMPTY'); ?></option>
					<?php if(!empty($arResult['field']['ENUM'])):
						foreach($arResult['field']['ENUM'] as $enum): ?>
							<option
								<?= ($enum['DEF'] === 'Y' ? 'selected="selected"' : ''); ?>
								data-id="<?= (int)$enum['ID'] ;?>"
								value="<?= htmlspecialcharsbx($enum['VALUE']); ?>"
							><?= htmlspecialcharsbx($enum['VALUE']); ?></option>
						<?php endforeach;;
					endif; ?>
				</select>
			</div>
		</div>
	</div>
	<div class="main-user-field-edit-buttons">
		<?php
		$buttons = [
			[
				'TYPE' => 'save',
			],
			'cancel'
		];
		if($arResult['field']['ID'] > 0)
		{
			$buttons[] = [
				'TYPE' => 'remove',
			];
		}
		$APPLICATION->IncludeComponent(
			'bitrix:ui.button.panel',
			"",
			[
				'BUTTONS' => $buttons,
				'ALIGN' => 'center'
			],
			$this->getComponent()
		);
		?>
	</div>
</div>
<script>
	BX.ready(function()
	{
		<?= 'BX.message('.\CUtil::PhpToJSObject(Loc::loadLanguageFile(__FILE__)).');' ?>
		var params = <?= CUtil::PhpToJSObject($arResult['jsParams']);?>;
		params.container = document.getElementById('main-user-field-edit-container');
		params.errorsContainer = document.getElementById('main-user-field-edit-errors');
		new BX.Main.UserField.Config(params);

		var listBlock = document.querySelector('[data-tab="list"]');
		var listItems = listBlock.querySelectorAll('[data-role="main-user-field-enum-row"]');

		listItems.forEach(function(item){
			var dragDropItem = new BX.Main.UserField.DragDropItem(item);
			dragDropItem.init(item);
		}.bind(this));

		var dragDropBtnContainer = new BX.Main.UserField.DragDropBtnContainer();
		dragDropBtnContainer.init();
	});
</script>