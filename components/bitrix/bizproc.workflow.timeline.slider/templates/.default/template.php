<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage("BIZPROC_WORKFLOW_TIMELINE_SLIDER_TITLE_MSGVER_1"));
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'no-paddings no-background');

/**
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

Extension::load([
	'bizproc.router',
	'bizproc.workflow.timeline',
	'ui.buttons',
	'ui.forms',
	'ui.alerts',
	'ui.fonts.opensans',
]);

$culture = Application::getInstance()->getContext()->getCulture();

$config = [
	'dateFormat' => $culture?->getLongDateFormat(),
	'dateFormatShort' => $culture?->getDayMonthFormat(),
	'timeFormat' => $culture?->getShortTimeFormat(),
];

/** @var BizprocAutomationSchemeComponent $component */
$component = $this->getComponent();
?>
<?php $this->SetViewTarget('pagetitle') ?>
<div class="ui-btn-container" data-role="page-toolbar">
	<button class="ui-btn ui-btn-light-border ui-btn-themes" onclick="top.BX.Helper.show('redirect=detail&code=21290220');">
		<?= Loc::getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_HELP_BUTTON') ?>
	</button>
</div>
<?php $this->EndViewTarget() ?>

<div id="bizproc-workflow-timeline-container"></div>

<script>
	BX.ready(function () {

		BX.Bizproc.Router.init();
		const timeline = new BX.Bizproc.Workflow.Timeline(
			<?= Json::encode($arResult['timelineProps']) ?>,
			<?= Json::encode($config) ?>
		);

		const timelineWrapper = document.getElementById('bizproc-workflow-timeline-container');

		BX.Dom.append(timeline.render(), timelineWrapper);
	});

	setTimeout(() => {
		BX.Runtime.loadExtension('ui.analytics').then(({ sendData }) => {
			sendData({
				tool: 'automation',
				category: 'bizproc_operations',
				event: 'process_log_view',
				p1: document.querySelector('.bizproc-workflow-timeline-title')?.textContent,
			});
		});
	}, 2000);
</script>
