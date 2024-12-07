<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
/**
 * @var array $params
 */
?>
<div class="mb-4" >
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_IFRAME_DESCRIPTION') ?></p>
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_IFRAME_SUM',
			[
				'#SUM#' => SaleFormatCurrency($params['SUM'], $params['CURRENCY']),
			]
		) ?></p>
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" id="paysystem-roboxchange-button-pay" href="#"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_IFRAME_BUTTON_PAID') ?></a>
		</div>
	</div>
	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_IFRAME_WARNING_RETURN') ?></div>
</div>

<script src="https://auth.robokassa.ru/Merchant/bundle/robokassa_iframe.js"></script>
<script>
	function showPaymentFrame(params)
	{
		Robokassa.StartPayment(params);
	}

	BX.ready(function(){
		var params = {
			MerchantLogin: "<?= CUtil::JSEscape($params['ROBOXCHANGE_SHOPLOGIN']) ?>",
			OutSum: "<?= CUtil::JSEscape($params['SUM']) ?>",
			InvId: "<?= CUtil::JSEscape($params['PAYMENT_ID']) ?>",
			Description: "<?= CUtil::JSEscape($params['ROBOXCHANGE_ORDERDESCR']) ?>",
			Culture: "<?= LANGUAGE_ID ?>",
			Encoding: "utf-8",
			SignatureValue: "<?= CUtil::JSEscape($params['SIGNATURE_VALUE']) ?>",
		}

		<?php if (!empty($params['OUT_SUM_CURRENCY'])):?>
			params.OutSumCurrency = "<?= CUtil::JSEscape($params['OUT_SUM_CURRENCY']) ?>";
		<?php endif; ?>

		<?php if ($params['PS_MODE']):?>
			params.IncCurrLabel = "<?= CUtil::JSEscape($params['PS_MODE']) ?>";
		<?php endif; ?>

		<?php if ($params['RECEIPT']):?>
			params.Receipt = "<?= CUtil::JSEscape($params['RECEIPT']) ?>";
		<?php endif; ?>

		var email = "<?= CUtil::JSEscape($params['BUYER_PERSON_EMAIL']) ?>";
		if (email)
		{
			params.Email = email;
		}

		var additionalUserFields = <?= CUtil::PhpToJSObject($params['ADDITIONAL_USER_FIELDS']) ?>;
		params = Object.assign(params, additionalUserFields);

		var isTest = "<?= CUtil::JSEscape($params['PS_IS_TEST']) ?>";
		if (isTest === 'Y')
		{
			params.IsTest = 1;
		}

		showPaymentFrame(params);

		BX.bind(BX('paysystem-roboxchange-button-pay'), 'click', BX.proxy(function () {
			showPaymentFrame(params);
		}, this));
	});
</script>
