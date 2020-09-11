<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var StoreOrderBlock $classBlock
 */
$iblockId = $classBlock->get('IBLOCK_ID') ?? '';
$skuIblockId = $classBlock->get('SKU_IBLOCK_ID') ?? '';
?>
<section class="landing-block g-pt-100 g-pb-100">
	<div class="container g-font-size-13">
		<?$APPLICATION->IncludeComponent(
			'bitrix:sale.order.ajax',
			'bootstrap_v4',
			array(
				'PAY_FROM_ACCOUNT' => 'Y',
				'ONLY_FULL_PAY_FROM_ACCOUNT' => 'N',
				'COUNT_DELIVERY_TAX' => 'N',
				'ALLOW_AUTO_REGISTER' => 'Y',
				'SEND_NEW_USER_NOTIFY' => 'Y',
				'DELIVERY_NO_AJAX' => 'N',
				'TEMPLATE_LOCATION' => 'popup',
				'PATH_TO_BASKET' => '#system_cart',
				'PATH_TO_PERSONAL' => '#system_personal',
				'PATH_TO_PAYMENT' => '#system_payment',
				'PATH_TO_ORDER' => '#system_personal?SECTION=orders',
				'SET_TITLE' => 'N' ,
				'DELIVERY_NO_SESSION' => 'Y',
				'COMPATIBLE_MODE' => 'N',
				'BASKET_POSITION' => 'before',
				'ADDITIONAL_PICT_PROP' => array(
					$iblockId => 'MORE_PHOTO',
					$skuIblockId => 'MORE_PHOTO',
				),
				'BASKET_IMAGES_SCALING' => 'adaptive',
				'SERVICES_IMAGES_SCALING' => 'adaptive',
				'DISABLE_BASKET_REDIRECT' => 'Y',
				'SHOW_DISCOUNT_PERCENT' => $classBlock->get('SHOW_DISCOUNT_PERCENT'),
				'USE_ENHANCED_ECOMMERCE' => $classBlock->get('USE_ENHANCED_ECOMMERCE'),
				'DATA_LAYER_NAME' => $classBlock->get('DATA_LAYER_NAME'),
				'BRAND_PROPERTY' => $classBlock->get('BRAND_PROPERTY'),
				'NO_PERSONAL' => $classBlock->get('NO_PERSONAL'),
				'USER_CONSENT' => $classBlock->get('USER_CONSENT'),
				'USER_CONSENT_ID' => $classBlock->get('AGREEMENT_ID'),
				'USER_CONSENT_IS_CHECKED' => 'Y',
				'USER_CONSENT_IS_LOADED' => 'N',
				'HIDE_DETAIL_PAGE_URL' => 'Y',
				'EMPTY_BASKET_HINT_PATH' => $classBlock->get('EMPTY_PATH'),
				'HIDE_ORDER_DESCRIPTION' => 'Y',
				'USE_CUSTOM_MAIN_MESSAGES' => 'Y',
				'MESS_REGION_BLOCK_NAME' => $classBlock->get('MESS_REGION_BLOCK_NAME'),
				'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID'),
				'SHOW_COUPONS' => 'Y',
				'IS_LANDING_SHOP' => 'Y',
			),
			false
		);?>
	</div>
</section>
