<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!empty($arParams['URL_PATH_TO_EMPTY_BASKET']))
{
	?>
	<div class="checkout-clear-page">
		<div class="checkout-clear-page-image-container">
			<img src="/bitrix/js/sale/checkout/images/empty_cart.svg?v=2" alt="">
		</div>
		<div class="checkout-clear-page-description">
			<?= Loc::getMessage("SOC_T_EMPTY_BASKET_TITLE") ?>
		</div>
		<div class="checkout-clear-page-btn-container">
			<a class="btn border border-dark btn-md rounded-pill pl-4 pr-4 w-100" id="" href="<?=$arParams['URL_PATH_TO_EMPTY_BASKET']?>">
				<?= Loc::getMessage("SOC_T_EMPTY_CART_START") ?>
			</a>
		</div>
	</div>
	<?php
}
?>