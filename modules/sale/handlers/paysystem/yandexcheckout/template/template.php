<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$sum = round($params['SUM'], 2);
?>
<style>
	<?php
		require 'style.css';
	?>
</style>
<div class="mb-4" >
	<p><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION')." ".SaleFormatCurrency($sum, $params['CURRENCY']);?></p>
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success" style="border-radius: 32px;" href="<?=$params['URL'];?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_PAID')?></a>
		</div>
		<div class="col pr-0 widget-payment-checkout-info"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_REDIRECT_MESS');?></div>
	</div>
	<div class="alert alert-info"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_WARNING_RETURN');?></div>
</div>