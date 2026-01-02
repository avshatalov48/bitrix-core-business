<?php

use Bitrix\Main\Localization\Loc;

/** @var array $params */

$invoiceSum = $params['INVOICE_SUM_FORMATTED'] ?? '';
$invoiceStatus = $params['INVOICE_STATUS'] ?? '';

?>
<div class="mb-4">
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_DESCRIPTION') ?></div>
	<div class="widget-payment-checkout-info">
		<?php
		echo Loc::getMessage(
			'SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_INVOICE_SUM',
			[
				'#INVOICE_SUM#' => $invoiceSum,
			]
		);
		?>
	</div>
	<?php
	if ($invoiceStatus !== ''):
		?>
		<div class="widget-payment-checkout-info">
			<?php
			echo Loc::getMessage(
				'SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_INVOICE_STATUS',
				[
					'#INVOICE_STATUS#' => $invoiceStatus,
				]
			);
			?>
		</div>
	<?php
	endif;
	?>
</div>
