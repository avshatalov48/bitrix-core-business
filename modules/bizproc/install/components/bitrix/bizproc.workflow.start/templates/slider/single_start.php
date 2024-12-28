<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

global $APPLICATION;

\Bitrix\Main\UI\Extension::load([
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
	'ui.lottie',
]);

/**
 * @var array $arResult
 */

$template = $arResult['template'];
$htmlId = 'bizproc-workflow-start-single-start';

$hasParameters = is_array($template['PARAMETERS'] ?? null) && $template['PARAMETERS'];
$parameters = $hasParameters ? $template['PARAMETERS']: null;
$constants = $arResult['isConstantsTuned'] ? null : ($template['CONSTANTS'] ?? null);

$singleStartData = [
	'documentType' => $arResult['documentType'],
	'signedDocumentType' => $arResult['signedDocumentType'],
	'signedDocumentId' => $arResult['signedDocumentId'],

	'hasParameters' => $hasParameters,
	'isConstantsTuned' => $arResult['isConstantsTuned'],

	'id' => (int)$template['ID'],
	'name' => (string)$template['NAME'],
	'description' => (string)$template['DESCRIPTION'],
	'duration' => $arResult['duration'],
	'constants' => is_array($constants) ? \Bitrix\Bizproc\FieldType::normalizePropertyList($constants) : null,
	'parameters' => is_array($parameters) ? \Bitrix\Bizproc\FieldType::normalizePropertyList($parameters) : null,
];
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

		BX.Bizproc.Component.WorkflowSingleStart.Instance = new BX.Bizproc.Component.WorkflowSingleStart(
			<?= Json::encode($singleStartData) ?>
		);

		BX.Dom.replace(
			document.getElementById('<?= "{$htmlId}-wrapper" ?>'),
			BX.Bizproc.Component.WorkflowSingleStart.Instance.render(),
		);

		BX.Bizproc.Component.WorkflowSingleStart.Instance.onAfterRender();
	});
</script>
