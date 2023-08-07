<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<div class="mb-4">
	<form action="<?=$params['URL']?>" method="post">
		<p>
			<?=Loc::getMessage("SALE_HPS_QIWI_SUMM_TO_PAY")?>:
			<?php if (Loader::includeModule("currency")):?>
				<strong class="strong-value"><?=CCurrencyLang::CurrencyFormat($params['PAYMENT_SHOULD_PAY'], $params['PAYMENT_CURRENCY'], true);?></strong>
			<?php else:?>
				<strong class="strong-value"><?=htmlspecialcharsbx($params['SHOULD_PAY']);?> <?=htmlspecialcharsbx($params['CURRENCY'])?></strong>
			<?php endif;?>
		</p>
		<input type="hidden" name="to" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_PHONE']);?>"/>
		<input type="hidden" name="from" value="<?=htmlspecialcharsbx($params['QIWI_SHOP_ID']);?>"/>
		<input type="hidden" name="summ" value="<?=htmlspecialcharsbx($params['PAYMENT_SHOULD_PAY']);?>"/>
		<input type="hidden" name="currency" value="<?=htmlspecialcharsbx($params['PAYMENT_CURRENCY']);?>"/>
		<input type="hidden" name="comm" value="<?=htmlspecialcharsbx(Loc::getMessage("SALE_HPS_QIWI_COMMENT", array("#ID#" => $params['PAYMENT_ID'])))?>"/>
		<input type="hidden" name="txn_id" value="<?=htmlspecialcharsbx($params['PAYMENT_ID']);?>"/>
		<input type="hidden" name="successUrl" value="<?=htmlspecialcharsbx($params['QIWI_SUCCESS_URL']);?>"/>
		<input type="hidden" name="failUrl" value="<?=htmlspecialcharsbx($params['QIWI_FAIL_URL']);?>"/>
		<input type="hidden" name="lifetime" value="<?=htmlspecialcharsbx($params['QIWI_BILL_LIFETIME']);?>"/>
		<input type="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" value="<?=Loc::getMessage("SALE_HPS_QIWI_DO_BILL");?>" />
	</form>
</div>
