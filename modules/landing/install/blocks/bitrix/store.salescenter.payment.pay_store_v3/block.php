<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<section class="landing-block u-block-border-none">
	<div class="landing-block-container container">
		<?$APPLICATION->IncludeComponent(
			'bitrix:salescenter.payment.pay',
			'payment_method_list',
			[]
		);?>
	</div>
</section>


