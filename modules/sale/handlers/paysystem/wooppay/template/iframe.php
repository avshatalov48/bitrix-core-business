<?php
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$url = new Uri($params['url']);
$url->addParams([
	'view_format' => 'frame',
]);
?>
<style>
	.wooppay-iframe-payment {
		border: none;
		width: 100%;
		height: 310px;
	}
</style>

<div class="mb-4" >
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_IFRAME_WOOPPAY_DESCRIPTION'); ?></p>
	<p><?= Loc::getMessage(
		'SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_IFRAME_WOOPPAY_SUM',
		[
			'#SUM#' => SaleFormatCurrency($params['sum'], $params['currency']),
		]
	); ?></p>
	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_IFRAME_WOOPPAY_WARNING_RETURN'); ?></div>
	<div class="d-flex align-items-center mb-3">
		<iframe src="<?= $url->getLocator(); ?>" class="wooppay-iframe-payment" id="wooppay-iframe-payment"></iframe>
	</div>
</div>

<script>
	BX.ready(function() {
		var wooppayIframePayment = document.querySelector('#wooppay-iframe-payment');
		if (wooppayIframePayment)
		{
			if (BX.browser.IsMobile())
			{
				wooppayIframePayment.style.height = '500px'
			}
		}
	});
</script>
