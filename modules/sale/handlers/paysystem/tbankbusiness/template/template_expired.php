<?php

use Bitrix\Main\Localization\Loc;

/** @var array $params */

?>
<div class="mb-4">
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_DESCRIPTION') ?></div>
	<div class="widget-payment-checkout-info">
		<?php
		echo Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TBANK_BUSINESS_INVOICE_EXPIRED');
		?>
	</div>
</div>
