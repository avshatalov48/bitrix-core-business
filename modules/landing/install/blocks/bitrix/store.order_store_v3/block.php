<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var StoreOrderBlockStoreV3 $classBlock
 */
$detailUrl = '#system_catalogitem/#ELEMENT_CODE#/';

$agreements = $classBlock->get('AGREEMENTS');
if (!is_array($agreements))
{
	$agreementId = (int)$classBlock->get('AGREEMENT_ID');
	if ($agreementId > 0)
	{
		$agreements = [
			[
				'ID' => $agreementId,
				'CHECKED' => 'Y',
				'REQUIRED' => 'Y',
			],
		];
	}
	else
	{
		$agreements = [];
	}
}
?>
<section class="landing-block">
	<div class="landing-component">
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:sale.order.checkout',
			'.default',
			[
				'USER_CONSENT' => $classBlock->get('USER_CONSENT'),
				'USER_CONSENTS' => $agreements,
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