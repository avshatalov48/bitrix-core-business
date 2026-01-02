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
$htmlId = 'bizproc-workflow-start-edit-constants';
$constants = $template['CONSTANTS'];

$editConstantsData = [
	'documentType' => $arResult['documentType'],
	'signedDocumentType' => $arResult['signedDocumentType'],

	'templateId' => (int)$template['ID'],
	'templateName' => (string)$template['NAME'],
	'constants' => is_array($constants) ? \Bitrix\Bizproc\FieldType::normalizePropertyList($constants) : null,
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
		BX.message(<?= Json::encode(Loc::loadLanguageFile(
			\Bitrix\Main\Application::getDocumentRoot()
			. \Bitrix\Main\IO\Path::normalize('/bitrix/components/bitrix/bizproc.workflow.start/templates/slider/single_start.php')
		)) ?>);

		BX.Bizproc.Component.WorkflowEditConstants.Instance = new BX.Bizproc.Component.WorkflowEditConstants(
			<?= Json::encode($editConstantsData) ?>
		);

		BX.Dom.replace(
			document.getElementById('<?= "{$htmlId}-wrapper" ?>'),
			BX.Bizproc.Component.WorkflowEditConstants.Instance.render(),
		);
	});
</script>
