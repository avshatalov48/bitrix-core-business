<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

Loader::includeModule('ui');
Loc::loadMessages(__FILE__);

CJSCore::Init(
	[
		'amcharts',
		'amcharts_serial',
		'ui.design-tokens',
		'ui.fonts.opensans',
	]
);
Asset::getInstance()->addJs('/bitrix/js/main/amcharts/3.21/gantt.js');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'no-background');
?>
<div class="filter-statistic pagetitle-container pagetitle-flexible-space">
	<?
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		array(
			'FILTER_ID' => $arParams['FILTER_NAME'],
			'FILTER' => $arResult['FILTER'],
			'GRID_ID' => $arParams['GRID_ID'],
			'ENABLE_LIVE_SEARCH' => true,
			'DISABLE_SEARCH' => true,
			'ENABLE_LABEL' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => 'DEFAULT'
		),
		$component,
		array(
			'HIDE_ICONS' => true
		)
	);
	?>
</div>

<div id="appHistoryChart"></div>

<div id="appHistoryGrid">
	<? $APPLICATION -> IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		array(
			'GRID_ID' => $arParams['GRID_ID'],
			'COLUMNS' => $arResult['GRID_HEADERS'],
			'ROWS' => $arResult['GRID_ITEMS'],
			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_SELECTED_COUNTER' => false,
			'SHOW_TOTAL_COUNTER' => true,
			'ALLOW_COLUMNS_SORT' => false,
			'NAV_OBJECT' => $arResult['NAV_OBJECT'],
			'AJAX_MODE' => 'Y',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
			'SHOW_PAGESIZE' => true,
			'SHOW_MORE_BUTTON' => false,
			'TOTAL_ROWS_COUNT' => $arResult['NAV_OBJECT']->getRecordCount(),
		),
		$component,
		array(
			'HIDE_ICONS' => true
		)
	); ?>
</div>
<script>
	BX.message(<?=Json::encode(
		[
			'REST_STATISTIC_EMPTY_DATA' => Loc::getMessage('REST_STATISTIC_EMPTY_DATA')
		]
	);?>);
	var CRestStatisticComponent = <?=Json ::encode(
		[
			'signetParameters' => $this->getComponent()->getSignedParameters(),
			'filterName' => $arParams['FILTER_NAME'],
			'langQuery' => Loc::getMessage('REST_STATISTIC_GRAPHS_QUERY'),
			'langRemain' => Loc::getMessage('REST_STATISTIC_GRAPHS_REMAIN'),
			'langLotOf' => Loc::getMessage('REST_STATISTIC_GRAPHS_LOT_OF'),
		]
	)?>;
</script>