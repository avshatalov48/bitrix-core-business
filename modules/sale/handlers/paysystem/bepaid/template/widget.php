<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$sum = round($params['sum'], 2);
?>

<div class="mb-4" >
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_WIDGET_DESCRIPTION') ?></p>
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_WIDGET_SUM',
			[
				'#SUM#' => SaleFormatCurrency($sum, $params['currency']),
			]
		) ?></p>
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" id="paysystem-bepaid-button-pay" href="#"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_WIDGET_BUTTON_PAID') ?></a>
		</div>
	</div>

	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_BEPAID_WIDGET_WARNING_RETURN') ?></div>
</div>

<script src="https://js.bepaid.by/widget/be_gateway.js"></script>
<script>
	<?php include_once 'widget.js' ?>
	BX.ready(function() {
		var params = {
			checkout_url: "<?= CUtil::JSEscape($params['checkout_url']) ?>",
			token: "<?= CUtil::JSEscape($params['token']) ?>",
			checkout: <?= CUtil::PhpToJSObject($params['checkout']) ?>,
			closeWidget: function(status) {}
		};

		new BeGateway(params).createWidget();

		BX.Sale.BePaid.init({
			params: params,
			buttonPayId: 'paysystem-bepaid-button-pay',
		});
	});
</script>
