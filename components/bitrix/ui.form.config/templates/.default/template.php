<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die;
}

$this->setFrameMode(true);

Extension::load([
	'ui.buttons',
	'ui.entity-selector',
	'ui.dialogs.messagebox',
]);
if ($arResult['grid']['FILTER'])
{
	$filterOptions = [
		'FILTER_ID' => $arResult['grid']['FILTER']['FILTER_ID'],
		'GRID_ID' => $arResult['grid']['GRID_ID'],
		'FILTER' => $arResult['grid']['FILTER']['FILTER_FIELDS'],
		'ENABLE_LIVE_SEARCH' => true,
		'ENABLE_LABEL' => true,
		"RESET_TO_DEFAULT_MODE" => true,
	];

	Toolbar::addFilter($filterOptions);
}
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['grid'],
);

$moduleId = $arParams['MODULE_ID'];
$componentId = 'bx-ui-form-config-group';
?>

<span id="<?= $componentId ?>"></span>

<script>
	BX.ready(function ()
	{
		new BX.Ui.Form.Config();
		BX.Loc.setMessage(<?=Json::encode([
			'UI_SCOPE_LIST_CONFIRM_TITLE_DELETE' => Loc::getMessage('UI_SCOPE_LIST_CONFIRM_TITLE_DELETE'),
			'UI_SCOPE_LIST_CONFIRM_ACCEPT_DELETE' => Loc::getMessage('UI_SCOPE_LIST_CONFIRM_ACCEPT_DELETE'),
			'UI_SCOPE_LIST_CONFIRM_CANCEL' => Loc::getMessage('UI_SCOPE_LIST_CONFIRM_CANCEL'),
			'UI_SCOPE_LIST_CONFIRM_TITLE_COPY' => Loc::getMessage('UI_SCOPE_LIST_CONFIRM_TITLE_COPY'),
			'UI_SCOPE_LIST_CONFIRM_ACCEPT_COPY' => Loc::getMessage('UI_SCOPE_LIST_CONFIRM_ACCEPT_COPY'),
			'UI_SCOPE_LIST_ALERT_EMPTY_CODES' => Loc::getMessage('UI_SCOPE_LIST_ALERT_EMPTY_CODES'),
		])?>);
	});
</script>
