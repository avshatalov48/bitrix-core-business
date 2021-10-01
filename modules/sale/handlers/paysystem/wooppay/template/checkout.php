<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<div class="mb-4" >
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_CHECKOUT_WOOPPAY_DESCRIPTION'); ?></p>
	<p><?= Loc::getMessage(
		'SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_CHECKOUT_WOOPPAY_SUM',
		[
			'#SUM#' => SaleFormatCurrency($params['sum'], $params['currency']),
		]
	); ?></p>
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success" style="border-radius: 32px;" href="<?= $params['url']; ?>"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_CHECKOUT_WOOPPAY_BUTTON_PAID'); ?></a>
		</div>
		<div class="col pr-0"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_CHECKOUT_WOOPPAY_REDIRECT_MESS'); ?></div>
	</div>
	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_CHECKOUT_WOOPPAY_WARNING_RETURN'); ?></div>
</div>
