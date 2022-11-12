<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var CBitrixComponent $component */

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load([
	'bizproc.debugger',
	'ui.alerts',
	'ui.sidepanel-content',
	'ui.fonts.opensans',
	'ui.hint',
]);

/** @var \Bitrix\Bizproc\Debugger\Session\Session | null $activeSession */
$activeSession = $arResult['activeSession'] ?? null;
$activeSessionData = $activeSession ? $activeSession->toArray() : null;

?>
<div class="bizproc-debugger-start__wrapper">
	<div class="ui-slider-section ui-slider-section-icon --rounding">
		<span class="ui-icon ui-slider-icon">
			<i></i>
		</span>
		<div class="ui-slider-content-box">
			<div class="ui-slider-heading-2">
				<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_TITLE')) ?>
			</div>
			<div class="ui-slider-inner-box">
				<p class="ui-slider-paragraph">
					<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_SUBTITLE')) ?>
				</p>
			</div>
		</div>
	</div>

	<div class="ui-slider-section --bizproc-large-section --rounding">
		<div class="ui-slider-heading-1">
			<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_SELECT_DEAL')) ?>
		</div>
		<div class="ui-slider-frame">
			<div class="bizproc-debugger--subtitle">
				<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_TEST_DEAL_TITLE'))?>
			</div>
			<div class="bizproc-debugger-start__block-deal">
				<div>
					<div class="bizproc-debugger--text">
						<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_TEST_DEAL_SUBTITLE')) ?>
					</div>
				</div>
				<div class="bizproc-debugger-start__block-btn">
					<button class="ui-btn ui-btn-sm ui-btn-success ui-btn-round" id="bizproc-debugger-start-experimental-element">
						<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIPZROC_DEBUGGER_START_TEMPLATE_START'))?>
					</button>
				</div>
			</div>
		</div>

		<div class="ui-slider-frame">
			<div class="bizproc-debugger--subtitle">
				<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_INTERCEPTION_DEAL_TITLE'))?>
			</div>
			<div class="bizproc-debugger-start__block-deal">
				<div>
					<div class="bizproc-debugger--text">
						<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_INTERCEPTION_DEAL_SUBTITLE')) ?>
					</div>
				</div>
				<div class="bizproc-debugger-start__block-btn">
					<button
						class="ui-btn ui-btn-sm ui-btn-success ui-btn-round"
						id="bizproc-debugger-start-interception-element"
					>
						<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIPZROC_DEBUGGER_START_TEMPLATE_START'))?>
					</button>
				</div>
			</div>
		</div>

		<a class="bizproc-debugger-help-link" id="bizproc-debugger-start-help" href="#">
			<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_INFO_MESSAGE'))?>
		</a>
	</div>
</div>
<div class="bizproc-debugger-start__background"></div>

<script>
	BX.ready(function()
	{
		BX.message({
			'BIZPROC_DEBUGGER_START_TEMPLATE_FINISH': '<?= GetMessageJS('BIPZROC_DEBUGGER_START_TEMPLATE_FINISH') ?>',
			'BIZPROC_DEBUGGER_START_TEMPLATE_START': '<?= GetMessageJS('BIPZROC_DEBUGGER_START_TEMPLATE_START') ?>'
		});

		const component =  new BX.Bizproc.Component.DebuggerStartComponent({
			documentSigned: <?= CUtil::PhpToJSObject($arResult['documentSigned']) ?>,
			activeSession: <?= CUtil::PhpToJSObject($activeSessionData) ?>,
			currentUserId: "<?= CUtil::JSEscape($arResult['currentUserId'])?>",
		});

		component.init();

		if (top.BX.Helper)
		{
			BX.bind(
				BX('bizproc-debugger-start-help'),
				'click',
				function(e)
				{
					e.preventDefault();
					top.BX.Helper.show('redirect=detail&code=16087164');
				}
			);
		}
		else
		{
			BX.Dom.remove(BX('bizproc-debugger-start-help'));
		}
	});
</script>

