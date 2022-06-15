<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $component
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
	'ui.common',
	'ui.notification',
	'ui.dialogs.messagebox',
	'ui.hint',
]);

?>
<div class="productcard_store_amount_wrapper">
<?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		$arResult['GRID'],
		);

	if ($arResult['TOTAL_WRAPPER_ID'])
	{
		if ($arResult['STORE_RESERVE_ENABLE'])
		{
			?>
			<div id="<?=$arResult['TOTAL_WRAPPER_ID']?>" class="product-stores-amount-total-wrapper" style="display: none">
				<div class="product-stores-amount-total-container">
					<table class="product-stores-amount-total-table">
						<tr class="product-stores-amount-total-table-row">
							<td><?=Loc::getMessage('C_PSA_TOTAL_PRODUCT_AVAILABLE')?>:</td>
							<td id="total_quantity"></td>
						</tr>
						<tr class="product-stores-amount-total-table-row">
							<td><?=Loc::getMessage('C_PSA_TOTAL_PRODUCT_RESERVED')?>:</td>
							<td id="total_quantity_reserved"></td>
						</tr>
						<tr class="product-stores-amount-total-table-row">
							<td><?=Loc::getMessage('C_PSA_TOTAL_PRODUCT_COMMON_PRICE')?>:</td>
							<td id="total_amount"></td>
						</tr>
					</table>
				</div>
			</div>
			<?php
		}
		else
		{
			?>
			<div id="<?=$arResult['TOTAL_WRAPPER_ID']?>" class="product-stores-amount-total-wrapper" style="display: none">
				<div class="product-stores-amount-total-container">
					<table class="product-stores-amount-total-table">
						<tr class="product-stores-amount-total-table-row">
							<td><?=Loc::getMessage('C_PSA_TOTAL_PRODUCT_STORED')?>:</td>
							<td id="total_quantity_common"></td>
						</tr>
						<tr class="product-stores-amount-total-table-row">
							<td><?=Loc::getMessage('C_PSA_TOTAL_PRODUCT_COMMON_PRICE')?>:</td>
							<td id="total_amount"></td>
						</tr>
					</table>
				</div>
			</div>
			<?php
		}
	}
?>
</div>

<script>
	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
	BX.ready(function () {
		BX.Catalog.ProductStoreGridManager.Instance = new BX.Catalog.ProductStoreGridManager(<?=CUtil::PhpToJSObject([
			'gridId' => $arResult['GRID']['GRID_ID'],
			'totalWrapperId' => $arResult['TOTAL_WRAPPER_ID'],
			'signedParameters' => $arResult['SIGNED_PARAMS'],
			'inventoryManagementLink' => $arResult['IM_LINK'],
			'productId' => $arResult['PRODUCT_ID'],
			'reservedDealsSliderLink' => $arResult['RESERVED_DEALS_SLIDER_LINK'],
		])?>);
	});
</script>
