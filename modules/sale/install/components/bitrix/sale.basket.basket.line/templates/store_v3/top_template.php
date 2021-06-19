<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $cartId
 */

$compositeStub = ($arResult['COMPOSITE_STUB'] ?? 'N') === 'Y';
if ($arResult['NUM_PRODUCTS'] > 0)
{
	?>
	<a href="<?=$arParams['PATH_TO_BASKET']?>" class="catalog-cart-block">
		<?php
		if (!$compositeStub)
		{
			if (
				$arParams['SHOW_NUM_PRODUCTS'] === 'Y'
				&& ($arResult['NUM_PRODUCTS'] > 0 || $arParams['SHOW_EMPTY_VALUES'] === 'Y')
			)
			{
				?><span class="catalog-cart-counter"><?=$arResult['NUM_PRODUCTS']?></span><?php
			}
		}
		?>
	</a>
	<?php
}