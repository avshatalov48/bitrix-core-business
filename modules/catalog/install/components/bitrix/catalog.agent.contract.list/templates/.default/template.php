<?php
/**
 * @var $component \CatalogAgentContractList
 * @var $this \CBitrixComponentTemplate
 * @var \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\UI;

global $APPLICATION;
$APPLICATION->SetTitle(Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_LIST_TEMPLATE_TITLE'));

UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

Main\UI\Extension::load([
	'ui.icons',
	'ui.entity-editor',
	'catalog.agent-contract',
]);

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.info.error',
		'',
		[
			'TITLE' => $arResult['ERROR_MESSAGES'][0],
		]
	);

	return;
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['GRID_ID'],
		'COLUMNS' => $arResult['COLUMNS'],
		'ROWS' => $arResult['ROWS'],
		'SHOW_ROW_CHECKBOXES' => true,
		'NAV_OBJECT' => $arResult['NAV_OBJECT'],
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
		'PAGE_SIZES' => [
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100'],
		],
		'AJAX_OPTION_JUMP' => 'N',
		'SHOW_CHECK_ALL_CHECKBOXES' => true,
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => true,
		'SHOW_TOTAL_COUNTER' => true,
		'SHOW_PAGESIZE' => true,
		'SHOW_ACTION_PANEL' => true,
		'ACTION_PANEL' => $arResult['ACTION_PANEL'],
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'AJAX_OPTION_HISTORY' => 'N',
		'STUB' => $arResult['STUB'],
	]
);
?>
<script>
	BX.message(<?=Main\Web\Json::encode(Main\Localization\Loc::loadLanguageFile(__FILE__))?>);

	BX.ready(function () {
		if (!BX.Reflection.getClass('BX.Catalog.Component.AgentContractList.Instance'))
		{
			BX.Catalog.Component.AgentContractList.Instance = new BX.Catalog.Component.AgentContractList({
				gridId: '<?=CUtil::JSEscape($arResult['GRID_ID'])?>',
				createUrl: '<?=CUtil::JSEscape($arResult['CREATE_URL'])?>',
			});
		}
	});
</script>