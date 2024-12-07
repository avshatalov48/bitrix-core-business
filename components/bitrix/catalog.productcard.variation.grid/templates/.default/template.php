<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $component \CatalogProductVariationGridComponent
 * @var $this \CBitrixComponentTemplate
 * @var \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\UI\Util;

Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.common',
	'ui.notification',
	'ui.dialogs.messagebox',
	'ui.hint',
]);

$containerId = 'catalog_variation_grid';
$createPropertyId = $containerId.'_create_property';
$createPropertyHintId = $createPropertyId.'_hint';

$isProduct = $arParams['VARIATION_ID_LIST'] === null;
$isCatalogHidden = $arResult['IS_CATALOG_HIDDEN'];
?>
<div class="catalog-variation-grid" id="<?=$containerId?>">
	<div class="catalog-variation-grid-content">
		<?php
		if ($isProduct && !$isCatalogHidden)
		{
			$disabledClass = $arResult['CAN_HAVE_SKU'] ? '' : ' ui-btn-disabled';
			?>
			<div class="catalog-variation-grid-add-block">
				<a class="ui-btn ui-btn-sm ui-btn-light catalog-variation-grid-add-btn<?=$disabledClass?>"
						data-role="catalog-productcard-variation-add-row"
						tabindex="-1">
					<?=Loc::getMessage('C_PVG_CREATE_VARIATION_MSGVER_1')?>
				</a>
			</div>
			<?php
		}
		?>
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:main.ui.grid',
			'',
			[
				'AJAX_MODE' => 'N',
				'AJAX_ID' => \CAjax::GetComponentID("bitrix:main.ui.grid", '', ''),

				//Strongly required
				'AJAX_OPTION_JUMP' => 'N',
				'AJAX_OPTION_STYLE' => 'N',
				'AJAX_OPTION_HISTORY' => 'N',

				// 'MODE' => $arResult['GRID']['MODE'],
				'GRID_ID' => $arResult['GRID']['GRID_ID'],
				'HEADERS' => $arResult['GRID']['HEADERS'],
				'SORT' => $arResult['GRID']['SORT'],
				'SORT_VARS' => $arResult['GRID']['SORT_VARS'],
				'ROWS' => $arResult['GRID']['ROWS'],
				'TOTAL_ROWS_COUNT' => $arResult['GRID']['TOTAL_ROWS_COUNT'],
				'NAV_OBJECT' => $arResult['GRID']['NAV_OBJECT'],

				'ADVANCED_EDIT_MODE' => true,
				'SHOW_CHECK_ALL_CHECKBOXES' => $isProduct ? $arResult['GRID']['SHOW_CHECK_ALL_CHECKBOXES'] : false,
				'SHOW_ROW_CHECKBOXES' => $arResult['GRID']['SHOW_ROW_CHECKBOXES'],
				'SHOW_ROW_ACTIONS_MENU' => $isProduct ? $arResult['GRID']['SHOW_ROW_ACTIONS_MENU'] : false,
				'SHOW_GRID_SETTINGS_MENU' => $arResult['GRID']['SHOW_GRID_SETTINGS_MENU'],
				'SHOW_NAVIGATION_PANEL' => $isProduct ? $arResult['GRID']['SHOW_NAVIGATION_PANEL'] : false,
				'SHOW_PAGINATION' => $arResult['GRID']['SHOW_PAGINATION'],
				'SHOW_SELECTED_COUNTER' => $arResult['GRID']['SHOW_SELECTED_COUNTER'],
				'SHOW_TOTAL_COUNTER' => $arResult['GRID']['SHOW_TOTAL_COUNTER'],
				'SHOW_PAGESIZE' => $arResult['GRID']['SHOW_PAGESIZE'],
				'PAGE_SIZES' => [
					['NAME' => '5', 'VALUE' => '5'],
					['NAME' => '10', 'VALUE' => '10'],
					['NAME' => '20', 'VALUE' => '20'],
					['NAME' => '50', 'VALUE' => '50'],
				],

				'SHOW_ACTION_PANEL' => $isProduct ? $arResult['GRID']['SHOW_ACTION_PANEL'] : false,
				'ACTION_PANEL' => $isProduct ? $arResult['GRID']['ACTION_PANEL'] : false,
				'HANDLE_RESPONSE_ERRORS' => true,
				'ENABLE_FIELDS_SEARCH' => $arResult['GRID']['ENABLE_FIELDS_SEARCH'],
				'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => $arResult['GRID']['USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP'],
			],
			$component
		);
		?>
	</div>
	<?php
	if ($arResult['CAN_HAVE_SKU'] && $isProduct)
	{
		?>
		<div class="catalog-variation-grid-link">
			<a class="ui-link ui-link-secondary ui-link-dashed" id="<?=$createPropertyId?>"
			><?=Loc::getMessage('C_PVG_CREATE_VARIATION_PROPERTY_MSGVER_1')?></a>
			<a href="<?=Util::getArticleUrlByCode('13274510')?>"
					class="ui-hint-icon"
					id="<?=$createPropertyHintId?>"></a>
		</div>
		<?php
	}
	?>
</div>
<script>
	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
	BX(function() {
		BX.Catalog.VariationGrid.Instance = new BX.Catalog.VariationGrid(<?=CUtil::PhpToJSObject([
			'createPropertyId' => $createPropertyId,
			'createPropertyHintId' => $createPropertyHintId,
			'gridId' => $component->getGridId(),
			'isGridReload' => $component->isAjaxGridAction(),
			'isNew' => $component->isNewProduct(),
			'isReadOnly' => $arResult['IS_READ_ONLY'],
			'isSimple' => $component->isSimpleProduct(),
			'hiddenProperties' => $arResult['GRID']['HIDDEN_PROPERTIES'],
			'modifyPropertyLink' => $arResult['PROPERTY_MODIFY_LINK'],
			'productCopyLink' => $arResult['PROPERTY_COPY_LINK'],
			'gridEditData' => $arResult['GRID']['EDIT_DATA'],
			'canHaveSku' => $arResult['CAN_HAVE_SKU'],
			'copyItemsMap' => $arResult['COPY_ITEM_MAP'] ?? null,
			'storeAmount' => $arResult['STORE_AMOUNT'],
			'isShowedStoreReserve' => $arResult['IS_SHOWED_STORE_RESERVE'],
			'reservedDealsSliderLink' => $arResult['RESERVED_DEALS_SLIDER_LINK'],
			'supportedAjaxFields' => $arResult['SUPPORTED_AJAX_FIELDS'],
		])?>);
	});
</script>
