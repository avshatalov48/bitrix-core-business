<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$messages = Loc::loadLanguageFile(__FILE__);
?>
<div class="mb-4" id="paysystem-authorize">
	<form id="paysystem-authorize-form">
		<div class="form-group row">
			<label for="ccardNumber" class="col-sm-6 col-form-label text-sm-right"><?=Loc::getMessage("AN_CC")?></label>
			<div class="col-sm-6">
				<input type="text" id="ccardNumber" name="ccard_num" size="30" value="" class="form-control">
			</div>
		</div>

		<div class="form-group row">
			<label for="ccardDate1" class="col-sm-6 col-form-label text-sm-right"><?=Loc::getMessage("AN_CC_DATE")?></label>
			<div class="col-auto">
				<input type="text" name="ccard_date1" size="5" value="" class="form-control" id="ccardDate1">
			</div>
			<div class="col-auto col-form-label">/</div>
			<div class="col-auto">
				<input type="text" name="ccard_date2" size="5" value="" class="form-control">
			</div>
		</div>

		<div class="form-group row">
			<label for="ccardCode" class="col-sm-6 col-form-label text-sm-right"><?=Loc::getMessage("AN_CC_CVV2")?></label>
			<div class="col-auto">
				<input type="text" id="ccardCode" name="ccard_code" size="5" value="" class="form-control">
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-6"></div>
			<div class="col-auto">
				<input type="hidden" name="payment_id" value="<?=htmlspecialcharsbx($params['PAYMENT_ID']);?>">
				<input type="submit" value="<?=Loc::getMessage("AN_CC_BUTTON")?>" class="inputbutton btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;">
			</div>
		</div>
	</form>
</div>

<script>
	<?php
	include_once 'script.js';
	?>
	BX.message(<?= CUtil::PhpToJSObject($messages) ?>);
	BX.ready(function(){
		BX.Sale.Authorize.init({
			formId: 'paysystem-authorize-form',
			paysystemBlockId: 'paysystem-authorize',
			ajaxUrl: '/bitrix/tools/sale_ps_ajax.php',
			paymentId: '<?= CUtil::JSEscape($params['PAYMENT_ID']) ?>',
			paySystemId: '<?= CUtil::JSEscape($params['PAYSYSTEM_ID']) ?>',
		});
	});
</script>