<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(
	\Bitrix\Main\Application::getDocumentRoot()
	. '/bitrix/blocks/bitrix/store.salescenter.order.details/.description.php'
);
/**
 * @var StoreSalesCenterOrderDetails $classBlock
 */
?>
<section class="landing-block">
	<div class="container g-font-size-13">
		<?php if (\Bitrix\Landing\Landing::getEditMode()): ?>

			<div class="g-min-height-200 g-flex-centered">
				<div class="g-landing-alert">
					MESS[LANDING_BLOCK_STORE_SALESCENTER_ORDER_DETAIL_ALERT]
				</div>
			</div>

		<?php else: ?>
			<?php
			$APPLICATION->IncludeComponent(
				'bitrix:salescenter.order.details',
				'.default',
				[
					'ID' => $classBlock->get('ORDER_ID'),
					'PAYMENT_ID' => $classBlock->get('PAYMENT_ID'),
					'TEMPLATE_MODE' => 'lightmode',
					'ACTIVE_DATE_FORMAT' => 'd F Y',
					'ALLOW_SELECT_PAYMENT_PAY_SYSTEM' => 'Y',
					'SHOW_HEADER' => 'Y',
				],
				false
			);
			?>
		<?php endif; ?>
	</div>
</section>
