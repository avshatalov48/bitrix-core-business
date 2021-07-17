<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var StoreOrderBlockStoreV3 $classBlock
 */
$detailUrl = '#system_catalogitem/#ELEMENT_CODE#/';
?>
<section class="landing-block">
	<div class="landing-component">
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:sale.order.checkout',
			'.default',
			[
				'USER_CONSENT' => $classBlock->get('USER_CONSENT'),
				'USER_CONSENT_ID' => $classBlock->get('AGREEMENT_ID'),
				'USER_CONSENT_IS_CHECKED' => 'Y',
				'USER_CONSENT_IS_LOADED' => 'N',
				'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID'),
				'IS_LANDING_SHOP' => 'Y',
				'URL_PATH_TO_DETAIL_PRODUCT' => $detailUrl,
				'URL_PATH_TO_EMPTY_BASKET' => $classBlock->get('EMPTY_PATH'),
				'URL_PATH_TO_MAIN_PAGE' => '#system_mainpage',
			],
			false
		); ?>
	</div>
</section>