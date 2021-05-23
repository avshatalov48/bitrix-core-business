<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Buttons;
use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

Extension::load(['sidepanel']);

if ($arParams['ADMIN_MODE'])
{
	$APPLICATION->IncludeComponent('bitrix:ui.toolbar', 'admin', []);
}

Toolbar::addFilter([
	'GRID_ID' => $arParams['GRID_ID'],
	'FILTER_ID' => $arParams['FILTER_ID'],
	'FILTER' => $arResult['FILTERS'],
	'DISABLE_SEARCH' => true,
	'ENABLE_LABEL' => true,
]);

$addButton = new Buttons\Button([
	'color' => Buttons\Color::PRIMARY,
	'icon' => Buttons\Icon::ADD,
	'click' => new Buttons\JsCode(
		'BX.SidePanel.Instance.open(\''.str_replace('#id#', 0, $arParams['PATH_TO_EDIT']).'\')'
	),
	'text' => Loc::getMessage('MAIN_USER_CONSENT_ADD_BUTTON')
]);
Toolbar::addButton($addButton);

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arParams['GRID_ID'],
		'COLUMNS' => $arResult['COLUMNS'],
		'ROWS' => $arResult['ROWS'],
		'NAV_OBJECT' => $arResult['NAV_OBJECT'],
		'~NAV_PARAMS' => ['SHOW_ALWAYS' => false],
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_GRID_SETTINGS_MENU' =>true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'TOTAL_ROWS_COUNT' => $arResult['TOTAL_ROWS_COUNT'],
		'ALLOW_COLUMNS_SORT' => false,
		'ALLOW_COLUMNS_RESIZE' => false,
		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'AJAX_OPTION_HISTORY' => 'N'
	]
);
?>
<script>
	BX.ready(function ()
	{
		BX.Main.UserConsent.List = new BX.Main.UserConsent.List({
			pathToEdit: <?=CUtil::phpToJSObject($arParams['PATH_TO_EDIT'])?>,
			pathToConsentList: <?=CUtil::phpToJSObject($arParams['PATH_TO_CONSENT_LIST'])?>
		});
	});
</script>