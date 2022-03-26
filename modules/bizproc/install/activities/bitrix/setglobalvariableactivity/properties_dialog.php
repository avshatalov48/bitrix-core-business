<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var $javascriptFunctions */
/** @var array $currentValues */
/** @var array $variables */
/** @var array $constants */
/** @var array $documentFields */
/** @var array $visibilityMessages */
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

\Bitrix\Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/setglobalvariableactivity/script.js'));
?>

<?= $javascriptFunctions ?>

<tr>
	<td colspan="2">
		<table width="100%" border="0" cellspacing="2" cellpadding="2" id="bp_sgva_addrow_table"></table>

		<?php CAdminCalendar::ShowScript() ?>

		<script>
			BX.ready(function () {
				BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile($dialog->getActivityFile())) ?>);
				BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)) ?>);

				var script = new BX.Bizproc.Activity.SetGlobalVariableActivity({
					isRobot: false,
					documentType: <?= CUtil::PhpToJSObject($dialog->getDocumentType()) ?>,
					signedDocumentType: '<?= CUtil::JSEscape(CBPDocument::signDocumentType($dialog->getDocumentType())) ?>',

					variables: <?= CUtil::PhpToJSObject($variables) ?>,
					constants: <?= CUtil::PhpToJSObject($constants) ?>,
					documentFields: <?= CUtil::PhpToJSObject($documentFields) ?>,

					currentValues: <?= CUtil::PhpToJSObject($currentValues) ?>,
					visibilityMessages:<?= CUtil::PhpToJSObject($visibilityMessages) ?>,
					formName: '<?= CUtil::JSEscape($dialog->getFormName()) ?>',

					addRowTable: document.getElementById('bp_sgva_addrow_table')
				});

				script.init();
			});
		</script>
	</td>
</tr>
