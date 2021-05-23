<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)	die();
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

Loc ::loadMessages(__FILE__);

CUtil::InitJSCore(['marketplace']);
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'pagetitle-toolbar-field-view no-background crm-pagetitle-view');
if ($arParams['SET_TITLE'])
{
	$APPLICATION->SetTitle(Loc::getMessage('REST_INTEGRATION_GRID_PAGE_TITLE'));
}

?>
<div class="filter-integration-grid pagetitle-container pagetitle-flexible-space">
	<?
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		array(
			'FILTER_ID' => $arParams['FILTER_NAME'],
			'FILTER' => $arResult['FILTER'],
			'GRID_ID' => $arParams['GRID_ID'],
			'ENABLE_LIVE_SEARCH' => false,
			'ENABLE_LABEL' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => 'DEFAULT'
		),
		$component,
		array('HIDE_ICONS' => true)
	);
	?>
</div>
<div>
	<? $APPLICATION -> IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		array(
			'GRID_ID' => $arParams['GRID_ID'],
			'COLUMNS' => $arResult['GRID_HEADERS'],
			'ROWS' => $arResult['GRID_ITEMS'],
			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_SELECTED_COUNTER' => true,
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
			'NAV_PARAM_NAME' => $arResult['NAV_OBJECT']->getId(),
			'CURRENT_PAGE' => $arResult['NAV_OBJECT']->getCurrentPage(),
			"PAGE_SIZES" => array(
				array('NAME' => '5', 'VALUE' => '5'),
				array('NAME' => '10', 'VALUE' => '10'),
				array('NAME' => '20', 'VALUE' => '20'),
				array('NAME' => '50', 'VALUE' => '50'),
			),
			"DEFAULT_PAGE_SIZE" => $arParams['DEFAULT_PAGE_SIZE']
		),
		$component,
		array(
			'HIDE_ICONS' => 'Y'
		)
	); ?>
</div>
<script>
	var restIntegrationGridComponent = <?=Json::encode(
		[
			'signetParameters' => $this->getComponent()->getSignedParameters(),
			'filterName' => $arParams['FILTER_NAME'],
			'gridId' => $arParams['GRID_ID'],
		]
	)?>;
	BX.ready(function ()
	{
		new BX.rest.integration.grid.init({
			gridId: '<?=CUtil::JSEscape($arResult['GRID_ID'])?>'
		});
	});
</script>
