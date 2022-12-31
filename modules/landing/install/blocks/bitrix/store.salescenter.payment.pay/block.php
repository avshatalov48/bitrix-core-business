<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<section class="landing-block u-block-border-none">
	<div class="landing-block-container">
		<?$APPLICATION->IncludeComponent(
			'bitrix:salescenter.payment.pay',
			'.default',
			[
				'ALLOW_SELECT_PAY_SYSTEM' => 'Y',
				'TEMPLATE_MODE' => 'lightmode',
				'ALLOW_PAYMENT_REDIRECT' => 'Y',
			]
		);?>
	</div>
</section>


