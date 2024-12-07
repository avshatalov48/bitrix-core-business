<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Bitrix\Main\Page\Asset::getInstance()->addCss("/bitrix/themes/.default/sale.css");

Loc::loadMessages(__FILE__);
$messages = Loc::loadLanguageFile(__FILE__);

$sum = round($params['SUM'], 2);
$documentRoot = Application::getDocumentRoot();
?>
<style>
	<?php
		require 'style.css';
	?>
</style>
<div class="paysystem-yandex mb-4" id="paysystem-yandex">
	<form id="paysystem-yandex-form">
		<p class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_MSGVER_1') ?></p>
		<p class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_SUM_MSGVER_1', ['#SUM#' => SaleFormatCurrency($params['SUM'], $params['CURRENCY'])]) ?></p>
		<?php if (isset($params['FIELDS'])): ?>
			<fieldset class="form-group">
				<?php foreach ($params['FIELDS'] as $field): ?>
					<?php if (in_array($field, $params['PHONE_FIELDS'])): ?>
						<label for="<?= $field ?>"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.mb_strtoupper($params['PAYMENT_METHOD']).'_'.mb_strtoupper($field)); ?>:</label>
						<input
							type="text" style="max-width: 300px;"
							class="form-control js-paysystem-yandex-input-phone"
							id="<?= $field; ?>"
							name="<?= $field; ?>"
							value="<?= htmlspecialcharsbx($params["PHONE_NUMBER"]) ?>"
							autocomplete="off"
							placeholder=""
						>
					<?php else: ?>
						<label for="<?= $field ;?>"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.mb_strtoupper($params['PAYMENT_METHOD']).'_'.mb_strtoupper($field)); ?></label>
						<input
							type="text"
							name="<?= $field; ?>"
							class="form-control"
							id="<?= $field; ?>"
							style="max-width: 300px;"
							placeholder="<?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'
								.mb_strtoupper($params['PAYMENT_METHOD'])
								.'_'
								.mb_strtoupper($field));
							?>"
						>
					<?php endif; ?>
				<?php endforeach; ?>
			</fieldset>
		<?php endif;?>
		<input
			type="submit"
			name="BuyButton"
			class="btn btn-lg btn-success pl-4 pr-4"
			style="border-radius: 32px;"
			value="<?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_NEXT') ?>"
		>
	</form>
</div>

<script>
	<?php
	include_once $documentRoot.'/bitrix/js/sale/masked.js';
	include_once 'script.js';
	?>
	BX.message(<?= CUtil::PhpToJSObject($messages) ?>);
	BX.ready(function(){
		BX.PaymentPhoneForm = new BX.Sale.Yandexcheckout.PaymentPhoneForm(<?= CUtil::PhpToJSObject(
			[
				'form' => 'paysystem-yandex-form',
				'phoneFormatDataUrl' => '/bitrix/js/sale/phone_mask',
			]
		) ?>);

		BX.Sale.Yandexcheckout.init({
			formId: 'paysystem-yandex-form',
			paysystemBlockId: 'paysystem-yandex',
			ajaxUrl: '/bitrix/tools/sale_ps_ajax.php',
			paymentId: '<?= CUtil::JSEscape($params['PAYMENT_ID']) ?>',
			paySystemId: '<?= CUtil::JSEscape($params['PAYSYSTEM_ID']) ?>',
			returnUrl: '<?= CUtil::JSEscape($params['RETURN_URL']) ?>',
		});
	});
</script>