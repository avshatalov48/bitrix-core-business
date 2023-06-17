<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES'])): ?>
	<?php foreach($arResult['ERROR_MESSAGES'] as $error):?>
		<div class="ui-alert ui-alert-danger" style="margin-bottom: 0px;">
			<span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span>
		</div>
	<?php endforeach;?>
	<?php
	return;
endif;

$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('STORE_STOCK_CHART_STORES_GRID_TITLE_2'));

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['GRID']['ID'],
		'COLUMNS' => $arResult['GRID']['COLUMNS'],
		'ROWS' => $arResult['GRID']['ROWS'],
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_ROW_ACTIONS_MENU' => false,
		'SHOW_GRID_SETTINGS_MENU' => false,
		'SHOW_NAVIGATION_PANEL' => false,
		'SHOW_PAGINATION' => false,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => false,
		'SHOW_PAGESIZE' => true,
		'ALLOW_COLUMNS_SORT' => false,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => false,
		'ALLOW_SORT' => false,
		'ALLOW_PIN_HEADER' => false,
		'AJAX_OPTION_HISTORY' => 'N',
	]
);
?>

