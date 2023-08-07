<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);
Loc::loadMessages(__DIR__ . '/template.php');

$messages = Loc::loadLanguageFile(__FILE__);
?>

<div class="mb-4" id="paysystem-qiwi">
	<?php if ($params['BUYER_PERSON_PHONE']):?>
		<div class="alert alert-danger mb-3"><?=Loc::getMessage("SALE_HPS_QIWI_INCORRECT_PHONE_NUMBER")?></div>
	<?php endif;?>
	<?=Loc::getMessage("SALE_HPS_QIWI_SUMM_TO_PAY")?>:
	<?php if (Loader::includeModule("currency")):?>
		<strong class="mb-3 strong-value"><?=CCurrencyLang::CurrencyFormat($params['PAYMENT_SHOULD_PAY'], $params['PAYMENT_CURRENCY'], true);?></strong>
	<?php else:?>
		<strong><?=htmlspecialcharsbx($params['SHOULD_PAY']);?> <?=htmlspecialcharsbx($params['CURRENCY'])?></strong>
	<?php endif;?>
	<div class="mb-3"><?=htmlspecialcharsbx(Loc::getMessage("SALE_HPS_QIWI_INPUT_PHONE"))?></div>
	<form id="paysystem-qiwi-form">
		<fieldset class="form-group">
			<input type="text" name="NEW_PHONE" style="max-width: 300px;" value="+7" placeholder="+7" class="form-control js-paysystem-qiwi-input-phone"/>
		</fieldset>
		<input type="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" name="SET_NEW_PHONE" value="<?= Loc::getMessage("SALE_HPS_QIWI_SEND_PHONE")?>" />
	</form>
</div>

<script>
	<?php
	$documentRoot = Application::getDocumentRoot();
	include_once $documentRoot.'/bitrix/js/sale/masked.js';
	include_once 'script.js';
	?>
	BX.message(<?= CUtil::PhpToJSObject($messages) ?>);
	BX.ready(function(){
		BX.PaymentPhoneForm = new BX.Sale.Qiwi.PaymentPhoneForm(<?= CUtil::PhpToJSObject(
			[
				'form' => 'paysystem-qiwi-form',
				'phoneFormatDataUrl' => '/bitrix/js/sale/phone_mask',
			]
		) ?>);

		BX.Sale.Qiwi.init({
			formId: 'paysystem-qiwi-form',
			paysystemBlockId: 'paysystem-qiwi',
			ajaxUrl: '/bitrix/tools/sale_ps_ajax.php',
			paymentId: '<?= CUtil::JSEscape($params['PAYMENT_ID']) ?>',
			paySystemId: '<?= CUtil::JSEscape($params['PAYSYSTEM_ID']) ?>',
			returnUrl: '<?= CUtil::JSEscape($params['RETURN_URL']) ?>',
		});
	});
</script>
