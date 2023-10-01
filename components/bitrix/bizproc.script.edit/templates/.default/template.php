<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

global $APPLICATION;
$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-background no-hidden");

$script = $arResult['SCRIPT'];

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.forms",
	"ui.alerts",
	"ui.layout-form",
	"sidepanel",
	"ui.sidepanel-content",
]);

$menu = [
	[
		"NAME" => Loc::getMessage('BIZPROC_SCRIPT_EDIT_MENU_GENERAL'),
		"ACTIVE" => true,
		"ATTRIBUTES" => [
			"data-role" => "menu-item",
			"data-page" => "general"
		],
	],
	[
		"NAME" => Loc::getMessage('BIZPROC_SCRIPT_EDIT_MENU_ROBOTS'),
		"ATTRIBUTES" => [
			"data-role" => "menu-item",
			"data-page" => "robots"
		]
	],
	[
		"NAME" => Loc::getMessage('BIZPROC_SCRIPT_EDIT_MENU_CONFIGS'),
		"ATTRIBUTES" => [
			"data-role" => "menu-item",
			"data-page" => "configs"
		]
	]
];

$APPLICATION->IncludeComponent("bitrix:ui.sidepanel.wrappermenu", "", [
	"ID" => 'bizproc-script-edit-menu',
	"ITEMS" => $menu,
	'TITLE' => GetMessage('BIZPROC_SCRIPT_EDIT_MENU_TITLE'),
]);
?>

<div id="bizproc.script.edit-base" style="display: flex">
	<form id="bizproc.script.edit-form" data-section="general" class="ui-slider-section bizproc-script-edit-block bizproc-script-edit-block--general" onsubmit="return false;">
		<input type="hidden" name="ID" value="<?=(int)$script['ID']?>">
		<div class="ui-form">
			<div class="ui-slider-heading-4"><?= Loc::getMessage('BIZPROC_SCRIPT_EDIT_SECTION_GENERAL') ?></div>
			<div class="ui-form-row">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text"><?= Loc::getMessage('BIZPROC_SCRIPT_EDIT_SCRIPT_NAME') ?></div>
				</div>
				<div class="ui-form-content">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						<input value="<?=htmlspecialcharsbx($script['NAME'])?>" name="NAME" type="text" class="ui-ctl-element" placeholder="<?= Loc::getMessage('BIZPROC_SCRIPT_EDIT_SCRIPT_NAME_DEFAULT')?>">
					</div>
				</div>
			</div>
			<div class="ui-form-row">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text"><?=Loc::getMessage('BIZPROC_SCRIPT_EDIT_SCRIPT_DESCRIPTION')?></div>
				</div>
				<div class="ui-form-content">
					<div class="ui-ctl ui-ctl-textarea ui-ctl-no-resize ui-ctl-w100">
						<textarea name="DESCRIPTION" class="ui-ctl-element" placeholder="<?=Loc::getMessage('BIZPROC_SCRIPT_EDIT_SCRIPT_DESCRIPTION_TEXT')?>"><?=htmlspecialcharsbx($script['DESCRIPTION'])?></textarea>
					</div>
				</div>
			</div>
		</div>
	</form>
	<div data-section="robots" class="bizproc-script-edit-block bizproc-script-edit-block-hidden">
		<div class="ui-slider-section">
			<div class="ui-slider-heading-4"><?= Loc::getMessage('BIZPROC_SCRIPT_EDIT_SECTION_ROBOTS') ?></div>
			<?php
			$APPLICATION->IncludeComponent('bitrix:bizproc.automation', '', [
				'ONE_TEMPLATE_MODE' => true,
				'TEMPLATE' => [
					'ID' => $script['WORKFLOW_TEMPLATE_ID'] ?? 0,
				],
				'DOCUMENT_TYPE' => [$script['MODULE_ID'], $script['ENTITY'], $script['DOCUMENT_TYPE']],
				'DOCUMENT_ID'                   => null,
				'MARKETPLACE_ROBOT_CATEGORY' => $script['MODULE_ID'].'_bots',
				'HIDE_TOOLBAR' => 'Y',
				'HIDE_SAVE_CONTROLS' => 'Y',
				'SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING' => 'Y',
				'MESSAGES' => [
					'BIZPROC_AUTOMATION_CMP_ROBOT_HELP' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_EDIT_ROBOT_HELP'),
					'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_EDIT_DELAY_NOW_HELP'),
					'BIZPROC_AUTOMATION_CMP_DELAY_AFTER_HELP' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_EDIT_DELAY_AFTER_HELP'),
				]
			], $this);
			?>
		</div>
	</div>
	<div data-section="configs" class="bizproc-script-edit-block bizproc-script-edit-block-hidden">

	</div>
</div>
<div data-role="script-edit-buttons">
	<?$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
		'BUTTONS' =>
			[
				[
					'ID' => 'bizproc.script.edit-btn-save',
					'TYPE' => 'save',
				],
				'cancel'
			]
	]);?>
</div>
<script>
	BX.ready(function() {
		var messages = <?=\Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__))?>;
		BX.message(messages);

		var cmp = new BX.Bizproc.ScriptEditComponent({
			baseNode: document.getElementById('bizproc.script.edit-base'),
			leftMenuNode: document.getElementById('bizproc-script-edit-menu'),
			saveButtonNode: document.getElementById('bizproc.script.edit-btn-save'),
			formNode: document.getElementById('bizproc.script.edit-form'),
			documentType: '<?=CUtil::JSEscape($arResult['DOCUMENT_TYPE_SIGNED'])?>',
			signedParameters: '<?=CUtil::JSEscape($this->getComponent()->getSignedParameters())?>',
			saveCallback: function() {
				var sliderInstance = BX.getClass('BX.SidePanel.Instance');
				if (sliderInstance)
				{
					var thisSlider = sliderInstance.getSliderByWindow(window);
					if (thisSlider)
					{
						thisSlider.close();
					}
				}
			}
		});
		cmp.init();
	});
</script>