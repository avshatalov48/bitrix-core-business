<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
$script = $arResult['SCRIPT'];

\CJSCore::init("sidepanel");
?>
<div style="width: 300px; display: inline-block">
<?php
$APPLICATION->IncludeComponent('bitrix:bizproc.automation', '', [
	'ONE_TEMPLATE_MODE' => true,
	'TEMPLATE' => $script,
	'DOCUMENT_TYPE' => [$script['MODULE_ID'], $script['ENTITY'], $script['DOCUMENT_TYPE']],
	'DOCUMENT_ID'                   => null,
	'DOCUMENT_CATEGORY_ID'          => null,
	'WORKFLOW_EDIT_URL'             => null,//'/bizproc/script/template/?id=#ID#',
	'CONSTANTS_EDIT_URL'            => '/bizproc/script/template/constants/?id=#ID#',
	'PARAMETERS_EDIT_URL'            => '/bizproc/script/template/parameters/?id=#ID#',
	'MARKETPLACE_ROBOT_CATEGORY' => $script['MODULE_ID'].'_bots',
	'MARKETPLACE_TRIGGER_PLACEMENT' => strtoupper($script['MODULE_ID']).'_ROBOT_TRIGGERS',
	'HIDE_TOOLBAR' => 'Y',
	'MESSAGES' => [
		'BIZPROC_AUTOMATION_CMP_AUTOMATION_EDIT' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_EDIT_TITLE'),
		'BIZPROC_AUTOMATION_CMP_ROBOT_HELP' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_EDIT_ROBOT_HELP'),
		'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_EDIT_DELAY_NOW_HELP'),
		'BIZPROC_AUTOMATION_CMP_DELAY_AFTER_HELP' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_SCRIPT_EDIT_DELAY_AFTER_HELP'),
	]
], $this);
?>
</div>
<div style="display: inline-block; vertical-align: top; padding: 20px">
	<? $u = \Bitrix\Main\Engine\UrlManager::getInstance()->create('deleteScript', [
		'c' => 'bitrix:bizproc.script.edit',
		'mode' => \Bitrix\Main\Engine\Router::COMPONENT_MODE_AJAX,
		'sessid' => bitrix_sessid(),
		'signedParameters' => $this->getComponent()->getSignedParameters(),
	]);
	?>
	<a class="ui-btn ui-btn-danger" href="<?=htmlspecialcharsbx($u)?>" target="_blank">
		<?=GetMessage('BIZPROC_SCRIPT_EDIT_ACTION_DELETE')?>
	</a>
	<br>
	<br>
	<br>
	<? $u = \Bitrix\Main\Engine\UrlManager::getInstance()->create('exportScript', [
		'c' => 'bitrix:bizproc.script.edit',
		'mode' => \Bitrix\Main\Engine\Router::COMPONENT_MODE_AJAX,
		'signedParameters' => $this->getComponent()->getSignedParameters(),
	]);
	?>
	<a class="ui-btn ui-btn-primary" href="<?=htmlspecialcharsbx($u)?>"><?=GetMessage('BIZPROC_SCRIPT_EDIT_ACTION_EXPORT')?></a>
</div>