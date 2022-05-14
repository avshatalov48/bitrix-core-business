<?php
/**
 * @var $component
 * @var $this \CBitrixComponentTemplate
 * @var \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Toolbar::deleteFavoriteStar();

?>
<div class="store-amount-wrapper">
	<div class="store-amount-sku-container">
		<div class="store-amount-sku-data-container">
			<img
				class="store-amount-sku-image"
				src="<?=$arResult['STORE_AMOUNT_DATA']['SKU_DATA']['IMAGE']?>"
				alt="<?=$arResult['STORE_AMOUNT_DATA']['SKU_DATA']['NAME']?>"
			/>
			<div>
				<div>
					<a class="store-amount-sku-name" href="<?=$arResult['STORE_AMOUNT_DATA']['SKU_DATA']['LINK']?>">
						<?=$arResult['STORE_AMOUNT_DATA']['SKU_DATA']['NAME']?>
					</a>
				</div>
				<div class="store-amount-sku-price-and-properties-container">
					<span class="store-amount-sku-properties"><?=$arResult['STORE_AMOUNT_DATA']['SKU_DATA']['PROPERTIES']?></span>
					<span><?=$arResult['STORE_AMOUNT_DATA']['SKU_DATA']['PRICE']?></span>
				</div>
			</div>
		</div>
		<?php foreach ($arResult['STORE_AMOUNT_DATA']['STORES_DATA'] as $storeData) : ?>
		<div class="store-amount-store-data-container">
			<p class="store-amount-store-name"><?=$storeData['NAME']?></p>
			<div class="store-amount-store-quantities-container">
				<span
					class="store-amount-store-quantity"
				><?=Loc::getMessage('STORE_AMOUNT_DETAILS_SLIDER_QUANTITY_COMMON1', ['#QUANTITY#' => $storeData['QUANTITY_COMMON']])?></span>
				<?php if ($arResult['IS_SHOWED_STORE_RESERVE']) : ?>
				<span
					class="store-amount-store-quantity"
				><?=Loc::getMessage('STORE_AMOUNT_DETAILS_SLIDER_QUANTITY_RESERVED1', ['#QUANTITY#' => $storeData['QUANTITY_RESERVED']])?></span>
				<span
					class="store-amount-store-quantity"
				><?=Loc::getMessage('STORE_AMOUNT_DETAILS_SLIDER_QUANTITY_AVAILABLE', ['#QUANTITY#' => $storeData['QUANTITY_AVAILABLE']])?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
