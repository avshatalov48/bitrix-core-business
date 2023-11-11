<?php
/**
 * @global \CMain $APPLICATION
 * @var \CatalogProductGridComponent $component
 * @var $this \CBitrixComponentTemplate
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('CATALOG_PRODUCT_GRID_TPL_MESS_PAGE_TITLE'));

Extension::load([
	'window',
	'ui.forms',
	'ui.dialogs.messagebox',
	'catalog.iblock-product-list',
	'catalog.product-selector',
	// for FileFieldAssembler
	'file_input',
]);

$productListParams = [
	'gridId' => $arResult['GRID']['GRID_ID'],
	'rowIdMask' => 'E#ID#',
	'variationFieldNames' => $arResult['SKU_FIELD_NAMES'],
	'productVariationMap' => $arResult['SKU_PRODUCT_MAP'],
	'createNewProductHref' => $arResult['URL_TO_ADD_PRODUCT'],
	'showCatalogWithOffers' => $arParams['SKU_SELECTOR_ENABLE'] === 'Y',
	'canEditPrice' => $arResult['CAN_EDIT_PRICE'],
];

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID'],
	$component,
	['HIDE_ICONS' => true]
);
?>
<script>
	BX.Event.ready(function(){
		const productListParams = <?= CUtil::PhpToJsObject($productListParams) ?>;
		const grid = BX.Main.gridManager.getInstanceById(productListParams.gridId);
		if (grid)
		{
			window.IblockGridInstance = BX.Catalog.productGridInit(grid);
			new BX.Catalog.IblockProductList(productListParams);
		}
		else
		{
			console.error('Product grid is absent');
		}
	});
</script>
<?php
$component->showDetailPageSlider();
