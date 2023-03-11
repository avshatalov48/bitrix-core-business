<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @var array $variables */
/** @var array $constants */
/** @var array $documentFields */
/** @var array $currentValues */
/** @var array $visibilityMessages */

\Bitrix\Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/setglobalvariableactivity/script.js'));
\Bitrix\Main\Page\Asset::getInstance()->addCss(getLocalPath('activities/bitrix/setglobalvariableactivity/style.css'));

//$formatName = \Bitrix\Main\Application::getInstance()->getContext()->getCulture()->getFormatName();

foreach ($currentValues as $variable => $value)
{
	$parse = CBPActivity::parseExpression($variable);
	if (!$parse)
	{
		continue;
	}
	$property = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getById($parse['field']);
	if (!$property)
	{
		continue;
	}
	if ($property['Type'] === 'user')
	{
		$currentValues[$variable] = CBPHelper::UsersArrayToString(
			$value,
			null,
			$dialog->getDocumentType()
		);
	}
}
?>

<div id="bp_sgva_addrow_table"></div>
<div class="bizproc-type-control-clone-btn setglobalvariableactivity-dashed-grey" id="bp_sgva_add_button"></div>

<script>
	BX.ready(function ()
	{
		BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile($dialog->getActivityFile())) ?>);

		var script = new BX.Bizproc.Activity.SetGlobalVariableActivity({
			isRobot: true,
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