<?php

use Bitrix\Main\Localization\Loc;

/** @var array $params */

$invoiceSum = $params['INVOICE_SUM_FORMATTED'] ?? '';
$invoicePersonalAccountUrl = $params['INVOICE_PERSONAL_ACCOUNT_URL'] ?? '';
$invoicePdfUrl = $params['INVOICE_PDF_URL'] ?? '';
$dueDate = $params['INVOICE_DUE_DATE'] ?? '';

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
	if ($dueDate !== ''):
		?>
		<div class="widget-payment-checkout-info">
			<?php
			echo Loc::getMessage(
				'SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_INVOICE_DUE_DATE',
				[
					'#INVOICE_DUE_DATE#' => $dueDate,
				]
			);
			?>
		</div>
	<?php
	endif;
	if ($invoicePdfUrl !== '' && $invoicePersonalAccountUrl !== ''):
		?>
		<div class="widget-payment-checkout-info">
			<?php
			echo Loc::getMessage(
				'SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_INVOICE_ALL_SHOW',
				[
					'#INVOICE_PESONAL_ACCOUNT_URL#' => $invoicePersonalAccountUrl,
					'#INVOICE_PDF_URL#' => $invoicePdfUrl,
				]
			);
			?>
		</div>
	<?php
	elseif ($invoicePdfUrl !== ''):
		?>
		<div class="widget-payment-checkout-info">
			<?php
			echo Loc::getMessage(
				'SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_INVOICE_PDF_SHOW',
				[
					'#INVOICE_PDF_URL#' => $invoicePdfUrl,
				]
			);
			?>
		</div>
	<?php
	endif;
	?>
</div>
