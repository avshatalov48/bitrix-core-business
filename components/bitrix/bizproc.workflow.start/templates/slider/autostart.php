<?php

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

global $APPLICATION;

Extension::load([
	'bp_field_type',
	'sidepanel',
	'main.date',
	'ui.design-tokens',
	'ui.icon-set.api.core',
	'ui.icon-set.main',
	'ui.icon-set.actions',
	'ui.alerts',
	'ui.forms',
	'ui.buttons',
	'ui.dialogs.messagebox',
]);

$templates = [];
foreach ($arResult['templates'] as $template)
{
	$templates[] = [
		'id' => (int)$template['ID'],
		'name' => (string)$template['NAME'],
		'description' => (string)$template['DESCRIPTION'],
		'parameters' => (
			is_array($template['PARAMETERS'] ?? null)
				? FieldType::normalizePropertyList($template['PARAMETERS'])
				: null
		),
	];
}

$autostartData = [
	'templates' => $templates,
	'documentType' => $arResult['documentType'],
	'signedDocumentType' => $arResult['signedDocumentType'],
	'signedDocumentId' => $arResult['signedDocumentId'],
	'autoExecuteType' => (int)$arResult['autoExecuteType'],
];

$htmlId = 'bizproc-workflow-start-autostart';
?>

<div id="<?= "{$htmlId}-wrapper" ?>"></div>
<div id="<?= "{$htmlId}-sticky-buttons" ?>"></div>
<div class="bizproc__ws_single-start__background"></div>

<?php $APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	'',
	[
		'ID' => "{$htmlId}-buttons",
		'STICKY_CONTAINER' => "#{$htmlId}-sticky-buttons",
		'BUTTONS' => [],
	],
) ?>

<script>
	BX.Event.ready(() => {
		BX.message(<?= Json::encode(Loc::loadLanguageFile(__FILE__)) ?>);
		BX.message(<?= Json::encode(Loc::loadLanguageFile(
			\Bitrix\Main\Application::getDocumentRoot()
			. \Bitrix\Main\IO\Path::normalize('/bitrix/components/bitrix/bizproc.workflow.start/templates/slider/single_start.php')
		)) ?>);

		BX.Bizproc.Component.WorkflowAutoStart.Instance = new BX.Bizproc.Component.WorkflowAutoStart(
			<?= Json::encode($autostartData) ?>
		);

		BX.Dom.replace(
			document.getElementById('<?= "{$htmlId}-wrapper" ?>'),
			BX.Bizproc.Component.WorkflowAutoStart.Instance.render(),
		);

		BX.Bizproc.Component.WorkflowAutoStart.Instance.onAfterRender();
	});
</script>

