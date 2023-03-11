<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage("BIZPROC_AUTOMATION_SCHEME_TITLE_ACTION_{$arResult['action']}"));
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'no-paddings no-background');

/**
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

\Bitrix\Main\UI\Extension::load([
	'bizproc.automation',
	'ui.buttons',
	'ui.forms',
	'ui.alerts',
	'ui.fonts.opensans',
]);

/** @var BizprocAutomationSchemeComponent $component */
$component = $this->getComponent();
?>
<?php $this->SetViewTarget('pagetitle') ?>
<div class="ui-btn-container">
	<button class="ui-btn ui-btn-light-border" onclick="top.BX.Helper.show('redirect=detail&code=14922900');">
		<?=Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_HELP_BUTTON')?>
	</button>
</div>
<?php $this->EndViewTarget() ?>

<div data-role="errors-container"></div>

<div class="bizproc-automation-scheme bizproc-automation-scheme__scope">
	<div class="bizproc-automation-scheme__step">
		<div class="bizproc-automation-scheme__step-counter">
			<div class="bizproc-automation-scheme__step-number">
				<div class="bizproc-automation-scheme__step-number--check"></div>
				<div class="bizproc-automation-scheme__step-number--value">1</div>
			</div>
		</div>
		<div class="bizproc-automation-scheme__step-container">
			<div class="bizproc-automation-scheme__step-head">
				<div class="bizproc-automation-scheme__step-head--title"><?= Loc::getMessage("BIZPROC_AUTOMATION_SCHEME_DST_TYPE_ACTION_{$arResult['action']}")?></div>
			</div>
			<div class="bizproc-automation-scheme__step-content">
				<div class="bizproc-automation-scheme__content --padding-15">
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<div class="ui-ctl-element"><?=Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_DROPDOWN_PLACEHOLDER')?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="bizproc-automation-scheme__step">
		<div class="bizproc-automation-scheme__step-counter">
			<div class="bizproc-automation-scheme__step-number">
				<div class="bizproc-automation-scheme__step-number--check"></div>
				<div class="bizproc-automation-scheme__step-number--value">2</div>
			</div>
		</div>
		<div class="bizproc-automation-scheme__step-container">
			<div class="bizproc-automation-scheme__step-head">
				<div class="bizproc-automation-scheme__step-head--title"><?=Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_DST_CATEGORY')?></div>
			</div>
			<div class="bizproc-automation-scheme__step-content">
				<div class="bizproc-automation-scheme__content --padding-15">
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<div class="ui-ctl-element"><?=Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_DROPDOWN_PLACEHOLDER')?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="bizproc-automation-scheme__step">
		<div class="bizproc-automation-scheme__step-counter">
			<div class="bizproc-automation-scheme__step-number">
				<div class="bizproc-automation-scheme__step-number--check"></div>
				<div class="bizproc-automation-scheme__step-number--value">3</div>
			</div>
		</div>
		<div class="bizproc-automation-scheme__step-container">
			<div class="bizproc-automation-scheme__step-head">
				<div class="bizproc-automation-scheme__step-head--title"><?=Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_DST_STAGE')?></div>
			</div>
			<div class="bizproc-automation-scheme__step-content">
				<div class="bizproc-automation-scheme__content --padding-15">
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<div class="ui-ctl-element"><?=Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_DROPDOWN_PLACEHOLDER')?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
$buttons = [
	[
		'ID' => 'bizproc.automation.scheme.execute',
		'TYPE' => 'save',
		'CAPTION' => Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_EXECUTE_BUTTON'),
	],
	[
		'TYPE' => 'cancel',
		'ONCLICK' => 'BX.SidePanel.Instance.close()',
	],
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	'',
	[
		'BUTTONS' => $buttons,
		'ALIGN' => 'center',
	],
	$component
);
?>

<script>
	BX.ready(function()
	{
		BX.message({
			'BIZPROC_AUTOMATION_SCHEME_DROPDOWN_PLACEHOLDER': '<?=GetMessageJS("BIZPROC_AUTOMATION_SCHEME_DROPDOWN_PLACEHOLDER")?>',
			'BIZPROC_AUTOMATION_SCHEME_CATEGORIES_NOT_EXISTS': '<?=GetMessageJS("BIZPROC_AUTOMATION_SCHEME_CATEGORIES_NOT_EXISTS")?>',
			'BIZPROC_AUTOMATION_SCHEME_DESTINATION_SCOPE_ERROR_ACTION_COPY': '<?=GetMessageJS("BIZPROC_AUTOMATION_SCHEME_DESTINATION_SCOPE_ERROR_ACTION_COPY")?>',
			'BIZPROC_AUTOMATION_SCHEME_DESTINATION_SCOPE_ERROR_ACTION_MOVE': '<?=GetMessageJS("BIZPROC_AUTOMATION_SCHEME_DESTINATION_SCOPE_ERROR_ACTION_MOVE")?>',
		});

		var component = new BX.Bizproc.Component.Scheme({
			scheme: <?= \Bitrix\Main\Web\Json::encode($arResult['templatesScheme']) ?>,
			signedParameters: '<?= $component->getSignedParameters() ?>',
			action: '<?=CUtil::JSEscape($arResult['action'])?>',

			errorsContainer: document.querySelector('[data-role="errors-container"]'),
			steps: document.querySelectorAll('.bizproc-automation-scheme__step'),
			executeButton: document.getElementById('bizproc.automation.scheme.execute'),
			stepsContentContainers: document.querySelectorAll('.bizproc-automation-scheme__step-content'),
		});

		component.init();
	});
</script>