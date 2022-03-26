<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @var array $variables */
/** @var array $constants */
/** @var array $operations */
/** @var array $documentFields */
/** @var array $currentValues */
/** @var array $visibilityMessages */

\Bitrix\Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/mathoperationactivity/script.js'));
\Bitrix\Main\UI\Extension::load(['bizproc.globals']);
?>

<div id="bp_moa_addrow_table"></div>

<script>
	BX.ready(function ()
	{
		BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile($dialog->getActivityFile())) ?>);

		var script = new BX.Bizproc.Activity.MathOperationActivity({
			isRobot: true,
			signedDocumentType: '<?= CUtil::JSEscape(CBPDocument::signDocumentType($dialog->getDocumentType())) ?>',

			variables: <?= CUtil::PhpToJSObject($variables) ?>,
			constants: <?= CUtil::PhpToJSObject($constants) ?>,
			operations: <?= CUtil::PhpToJSObject($operations) ?>,
			documentFields: <?= CUtil::PhpToJSObject($documentFields) ?>,

			currentValues: <?= CUtil::PhpToJSObject($currentValues) ?>,
			visibilityMessages:<?= CUtil::PhpToJSObject($visibilityMessages) ?>,

			addRowTable: document.getElementById('bp_moa_addrow_table')
		});

		script.init();
	});
</script>