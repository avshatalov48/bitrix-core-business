<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$sum = round($params['sum'], 2);
?>

<div class="mb-4" >
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_CHECKOUT_DESCRIPTION') ?></p>
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_CHECKOUT_SUM',
		[
			'#SUM#' => SaleFormatCurrency($sum, $params['currency']),
		]
	) ?></p>
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" href="<?= $params['url'] ?>"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_CHECKOUT_BUTTON_PAID') ?></a>
		</div>
		<div class="col pr-0"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_CHECKOUT_REDIRECT_MESS') ?></div>
	</div>

	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_CHECKOUT_WARNING_RETURN') ?></p>
</div>
