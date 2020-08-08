<?php
	use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);
?>
<form action="<?=$params['URL']?>" method="post" class="mb-4">
	<p><?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_TITLE")?></p>
	<p><?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_ORDER");?> <?=htmlspecialcharsbx($params['PAYMENT_ID']."  ".$params["PAYMENT_DATE_INSERT"])?></p>
	<p><?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_TO_PAY");?> <strong><?=SaleFormatCurrency($params['PAYMENT_SHOULD_PAY'], $params["PAYMENT_CURRENCY"])?></strong></p>

	<input type="hidden" name="FinalStep" value="1">
	<input type="hidden" name="MrchLogin" value="<?=htmlspecialcharsbx($params['ROBOXCHANGE_SHOPLOGIN']);?>">
	<input type="hidden" name="OutSum" value="<?=htmlspecialcharsbx($params['PAYMENT_SHOULD_PAY']);?>">
	<input type="hidden" name="InvId" value="<?=htmlspecialcharsbx($params['PAYMENT_ID']);?>">
	<input type="hidden" name="Desc" value="<?=htmlspecialcharsbx($params['ROBOXCHANGE_ORDERDESCR']);?>">
	<input type="hidden" name="SignatureValue" value="<?=$params['SIGNATURE_VALUE'];?>">
	<input type="hidden" name="Email" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_EMAIL'])?>">
	<input type="hidden" name="SHP_HANDLER" value="ROBOXCHANGE">
	<input type="hidden" name="SHP_BX_PAYSYSTEM_CODE" value="<?=$params['BX_PAYSYSTEM_CODE'];?>">
	<?php if ($params['PS_IS_TEST'] == 'Y'):?>
		<input type="hidden" name="IsTest" value="1">
	<?php endif;?>
	<?php if ($params['PS_MODE'] != "0"):?>
		<input type="hidden" name="IncCurrLabel" value="<?=htmlspecialcharsbx($params['PS_MODE']);?>">
	<?php endif;?>

	<input type="submit" name="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" value="<?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_BUTTON")?>">
</form>