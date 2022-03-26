<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/**
 * @var \CCrmEntityProductListComponent $component
 * @var \CBitrixComponentTemplate $this
 * @var \CMain $APPLICATION
 */

$settings = $arResult['SETTINGS'];
$currency = $settings['CURRENCY'];

?>
<div class="catalog-document-product-list-wrapper" id="<?=$arResult['GRID_EDITOR_CONFIG']['containerId']?>">
	<?php
	if (!$settings['IS_READ_ONLY'])
	{
		$panelStatus = ($settings['NEW_ROW_POSITION'] === 'bottom') ? 'hidden' : 'active';
		$buttonTopPanelClasses = [
			'catalog-document-product-list-add-block',
			'catalog-document-product-list-add-block-top',
			'catalog-document-product-list-add-block-' . $panelStatus,
		];

		$createUrl =
			(preg_match('#^(?:/|https?://)#', $settings['CREATE_PRODUCT_PATH'])
				? $settings['CREATE_PRODUCT_PATH']
				: '')
		;

		$buttonTopPanelClasses = implode(' ', $buttonTopPanelClasses);
		?>
		<div class="<?=$buttonTopPanelClasses?>">
			<div>
				<a class="ui-btn ui-btn-primary"
						data-role="product-list-add-button"
						title="<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ADD_PRODUCT_TITLE')?>"
						tabindex="-1">
					<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ADD_PRODUCT')?>
				</a>
				<?php
				if (!empty($createUrl))
				{
					?>
					<a class="ui-btn ui-btn-light-border"
						target="_blank"
						href="<?=htmlspecialcharsbx($createUrl)?>"
						data-role="product-list-create-button"
						title="<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_CREATE_PRODUCT_TITLE')?>"
						tabindex="-1"
					>
						<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_CREATE_PRODUCT')?>
					</a>
					<?php
				}
				?>
			</div>
			<div>
<!--				<a class="ui-btn ui-btn-light-border"-->
<!--				   data-role="product-list-barcode-settings-button"-->
<!--				   title="--><?//=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_BARCODE_SETTING_PRODUCT_TITLE')?><!--"-->
<!--				   tabindex="-1">-->
<!--					--><?//=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_BARCODE_SETTING_PRODUCT')?>
<!--				</a>-->
				<button class="ui-btn ui-btn-light-border ui-btn-icon-setting"
						data-role="product-list-settings-button"></button>
			</div>
		</div>
		<?php
	}

	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		$arResult['GRID'],
		$component
	);

	if (!$settings['IS_READ_ONLY'])
	{
		$panelStatus = ($settings['NEW_ROW_POSITION'] !== 'bottom') ? 'hidden' : 'active';
		$buttonBottomPanelClasses = [
			'catalog-document-product-list-add-block',
			'catalog-document-product-list-add-block-bottom',
			'catalog-document-product-list-add-block-' . $panelStatus,
		];

		$buttonBottomPanelClasses = implode(' ', $buttonBottomPanelClasses);
		?>
		<div class="<?=$buttonBottomPanelClasses?>">
			<div>
				<a class="ui-btn ui-btn-primary"
					data-role="product-list-add-button"
					title="<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ADD_PRODUCT_TITLE')?>"
					tabindex="-1">
					<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ADD_PRODUCT')?>
				</a>
				<?php
				if (!empty($createUrl))
				{
					?>
					<a class="ui-btn ui-btn-light-border"
						target="_blank"
						href="<?=htmlspecialcharsbx($createUrl)?>"
						data-role="product-list-create-button"
						title="<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_CREATE_PRODUCT_TITLE')?>"
						tabindex="-1"
					>
						<?=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_CREATE_PRODUCT')?>
					</a>
					<?php
				}
				?>
			</div>
			<div>
<!--				<a class="ui-btn ui-btn-light-border"-->
<!--				   data-role="product-list-barcode-settings-button"-->
<!--				   title="--><?//=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_BARCODE_SETTING_PRODUCT_TITLE')?><!--"-->
<!--				   tabindex="-1">-->
<!--					--><?//=Loc::getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_BARCODE_SETTING_PRODUCT')?>
<!--				</a>-->
				<button class="ui-btn ui-btn-light-border ui-btn-icon-setting"
						data-role="product-list-settings-button"></button>
			</div>
		</div>
		<?php
	}
	?>
	<div class="catalog-document-total-wrapper catalog-document-product-list-page-content">
		<div class="catalog-document-product-list-result-container" id="<?=$settings['TOTAL_SUM_CONTAINER_ID']?>">
			<table class="catalog-document-product-list-payment-side-table">
				<tr class="catalog-document-product-list-payment-side-table-row">
					<td class="catalog-document-product-list-result-grid-total-big">
						<?=Loc::getMessage('CATALOG_PRODUCT_SUM_TOTAL')?>:
					</td>
					<td class="catalog-document-product-list-result-grid-total-big">
						<span data-total="totalCost">
							<?=\CCurrencyLang::CurrencyFormat($arResult['TOTAL_SUM'], $currency['ID'], false)?>
						</span>
						<span data-role="currency-wrapper" class="catalog-document-product-list-result-grid-item-currency-symbol"><?=$currency['TEXT']?></span>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<input type="hidden" name="<?=htmlspecialcharsbx($arResult['PRODUCT_DATA_FIELD_NAME'])?>" value="" />
	<input type="hidden"
			name="<?=htmlspecialcharsbx($arResult['PRODUCT_DATA_FIELD_NAME'].'_SETTINGS')?>"
			value="" />
</div>
<script>
	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);

	BX.Currency.setCurrencyFormat(
		"<?= $settings['CURRENCY']['ID']?>",
		<?= \CUtil::PhpToJSObject($settings['CURRENCY']['FORMAT'])?>
	);

	BX.ready(function() {
		if (!BX.Reflection.getClass('BX.Catalog.Store.ProductList.Instance'))
		{
			BX.Catalog.Store.ProductList.Instance = new BX.Catalog.Store.ProductList.Editor('<?=$arResult['ID']?>');
		}

		BX.Catalog.Store.ProductList.Instance.init(<?=Json::encode($arResult['GRID_EDITOR_CONFIG'])?>);
		BX.Catalog["<?=$arResult['GRID_EDITOR_CONFIG']['jsEventsManagerId']?>"] = BX.Catalog.Store.ProductList.Instance.getPageEventsManager();
	});
</script>
