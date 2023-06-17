<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
\Bitrix\Main\Loader::includeModule('socialnetwork');
CUtil::InitJSCore(
	['tooltip', 'admin_interface', 'date', 'uploader', 'file_dialog', 'bp_user_selector', 'bp_field_type']
);
\Bitrix\Main\UI\Extension::load([
	'bizproc.automation',
	'ui.buttons',
	'ui.hint',
	'ui.entity-selector',
	'ui.design-tokens'
]);
\Bitrix\Main\UI\Extension::load(['bizproc.automation', 'ui.buttons', 'ui.hint', 'ui.entity-selector']);
/**
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 */

if (isset($arResult['USE_DISK']) && $arResult['USE_DISK'])
{
	$this->addExternalJs($this->GetFolder().'/disk_uploader.js');
	$this->addExternalCss('/bitrix/js/disk/css/legacy_uf_common.css');
}
$messages = \Bitrix\Main\Localization\Loc::loadLanguageFile(__DIR__.DIRECTORY_SEPARATOR.'template.php');

if (isset($arParams['~MESSAGES']) && is_array($arParams['MESSAGES']))
{
	$messages = $arParams['~MESSAGES'] + $messages;
}

?>
<script>
	BX.ready(function()
	{
		BX.namespace('BX.Bizproc.Automation');
		if (typeof BX.Bizproc.Automation.Component === 'undefined')
		{
			return;
		}

		BX.message(<?=\Bitrix\Main\Web\Json::encode($messages)?>);
		BX.message({
			BIZPROC_AUTOMATION_YES: '<?=GetMessageJS('MAIN_YES')?>',
			BIZPROC_AUTOMATION_NO: '<?=GetMessageJS('MAIN_NO')?>'
		});

		BX.Bizproc.Automation.API.documentName = '<?= CUtil::JSEscape($arResult['DOCUMENT_NAME']) ?>';
		BX.Bizproc.Automation.API.documentSigned = <?=\Bitrix\Main\Web\Json::encode($arResult['DOCUMENT_SIGNED'])?>;
		BX.Bizproc.Automation.API.documentFields = <?=\Bitrix\Main\Web\Json::encode($arResult['DOCUMENT_FIELDS'])?>;
		BX.Bizproc.Automation.API.documentUserGroups = <?=\Bitrix\Main\Web\Json::encode($arResult['DOCUMENT_USER_GROUPS'])?>;
	});
</script>