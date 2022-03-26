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
?>

<tr>
	<td colspan="2">
		<table width="100%" border="0" cellspacing="2" cellpadding="2" id="bp_moa_addrow_table"></table>

		<script>
			BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile($dialog->getActivityFile())) ?>);
		</script>

		<script>
			BX.ready(function () {
				var script = new BX.Bizproc.Activity.MathOperationActivity({
					isRobot: false,

					variables: <?= CUtil::PhpToJSObject($variables) ?>,
					constants: <?= CUtil::PhpToJSObject($constants) ?>,
					documentFields: <?= CUtil::PhpToJSObject($documentFields) ?>,
					operations: <?= CUtil::PhpToJSObject($operations) ?>,

					currentValues: <?= CUtil::PhpToJSObject($currentValues) ?>,
					visibilityMessages:<?= CUtil::PhpToJSObject($visibilityMessages) ?>,

					addRowTable: document.getElementById('bp_moa_addrow_table')
				});

				script.init();
			});
		</script>
	</td>
</tr>
