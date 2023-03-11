<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBPDocumentService $documentService */
/** @var array $arCurrentValues */

\Bitrix\Main\UI\Extension::load(['bizproc.mixed-selector', 'bizproc.condition']);
\Bitrix\Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/mixedcondition/script.js'));

$conditions = (array)$arCurrentValues['conditions'];
if (!$conditions)
{
	$condition[] = ['operator' => '!empty'];
}?>

<script>
	BX.ready(function () {
		BX.Loc.setMessage(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)) ?>);

		var script = new BX.Bizproc.Activity.MixedCondition({
			conditions: <?= CUtil::PhpToJSObject($conditions) ?>,
			table: document.getElementById('id_bwfiba_type_mixedcondition'),
			objectTabs: {
				Parameter: window.arWorkflowParameters ?? [],
				Variable: window.arWorkflowVariables ?? [],
				Constant: window.arWorkflowConstants ?? [],
				GlobalConst: window.arWorkflowGlobalConstants ?? [],
				GlobalVar: window.arWorkflowGlobalVariables ?? [],
				Document: window.arDocumentFields ?? [],
				Activity: arAllActivities ?? []
			},
			template: [rootActivity.Serialize()],
			documentType: <?= CUtil::PhpToJSObject($documentType) ?>,
		});

		script.init();
	});
</script>
