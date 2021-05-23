<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$this->setFrameMode(true);

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['grid']
);

$moduleId = $arParams['MODULE_ID'];
$componentId = 'bx-ui-form-config-group';
?>

<span id="<?= $componentId ?>"></span>

<script>
	BX.ready(function ()
	{
		new BX.Ui.Form.Config(<?= CUtil::PhpToJSObject([
			'scopes' => $arResult['jsData'],
			'componentId' => $componentId
		]) ?>);
	});
</script>

<?php

$initPopupEvent = 'ui:onComponentLoad';
$openPopupEvent = 'ui:onComponentOpen';

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.selector',
	'.default',
	[
		'API_VERSION' => 3,
		'ID' => $componentId,
		'BIND_ID' => $componentId,
		'ITEMS_SELECTED' => [],
		'CALLBACK' => [
			'select' => 'BX.Ui.Form.ConfigItem.onMemberSelect',
			'unSelect' => 'BX.Ui.Form.ConfigItem.onMemberUnselect',
			'openDialog' => 'function(){}',
			'closeDialog' => 'BX.Ui.Form.ConfigItem.onDialogClose',
		],
		'OPTIONS' => [
			'eventInit' => 'BX.Ui.Form.ConfigItem:onComponentLoad',
			'eventOpen' => 'BX.Ui.Form.ConfigItem:onComponentOpen',
			'useContainer' => 'Y',
			'lazyLoad' => 'Y',
			'context' => 'UI_EDITOR_CONFIG',
			'contextCode' => '',
			'useSearch' => 'Y',
			'useClientDatabase' => 'Y',
			'allowEmailInvitation' => 'N',
			'enableAll' => 'N',
			'enableUsers' => 'Y',
			'enableDepartments' => 'Y',
			'enableGroups' => 'Y',
			'departmentSelectDisable' => 'N',
			'allowAddUser' => 'Y',
			'allowAddCrmContact' => 'N',
			'allowAddSocNetGroup' => 'N',
			'allowSearchEmailUsers' => 'N',
			'allowSearchCrmEmailUsers' => 'N',
			'allowSearchNetworkUsers' => 'N',
			'useNewCallback' => 'Y',
			'multiple' => 'Y',
			'enableSonetgroups' => 'Y',
			'showVacations' => 'Y'
		]
	],
	false,
	['HIDE_ICONS' => 'Y']
);