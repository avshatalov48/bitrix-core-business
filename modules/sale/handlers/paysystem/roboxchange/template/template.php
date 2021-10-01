<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
/**
 * @var array $params
 */
?>
<div class="mb-4" >
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_CHECKOUT_DESCRIPTION') ?></p>
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_CHECKOUT_SUM',
			[
				'#SUM#' => SaleFormatCurrency($params['SUM'], $params['CURRENCY']),
			]
		) ?></p>
	<div class="d-flex align-items-center">
		<form action="<?=$params['URL']?>" method="post" class="mb-4" class="p-0" style="display: inline-block">
				<input type="hidden" name="MerchantLogin" value="<?=htmlspecialcharsbx($params['ROBOXCHANGE_SHOPLOGIN']);?>">
				<input type="hidden" name="OutSum" value="<?=htmlspecialcharsbx($params['SUM']);?>">
				<?php if (!empty($params['OUT_SUM_CURRENCY'])):?>
					<input type="hidden" name="OutSumCurrency" value="<?=htmlspecialcharsbx($params['OUT_SUM_CURRENCY']);?>">
				<?php endif;?>
				<input type="hidden" name="InvId" value="<?=htmlspecialcharsbx($params['PAYMENT_ID']);?>">
				<input type="hidden" name="Description" value="<?=htmlspecialcharsbx($params['ROBOXCHANGE_ORDERDESCR']);?>">
				<input type="hidden" name="SignatureValue" value="<?=$params['SIGNATURE_VALUE'];?>">
				<input type="hidden" name="Email" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_EMAIL'])?>">

				<?php if ($params['RECEIPT']):?>
					<input type="hidden" name="Receipt" value="<?=htmlspecialcharsbx($params['RECEIPT'])?>">
				<?php endif;?>

				<?php foreach ($params['ADDITIONAL_USER_FIELDS'] as $fieldName => $fieldsValue):?>
					<input type="hidden" name="<?=$fieldName?>" value="<?=htmlspecialcharsbx($fieldsValue);?>">
				<?php endforeach; ?>

				<?php if ($params['PS_IS_TEST'] === 'Y'):?>
					<input type="hidden" name="IsTest" value="1">
				<?php endif;?>

				<?php if ($params['PS_MODE']):?>
					<input type="hidden" name="IncCurrLabel" value="<?=htmlspecialcharsbx($params['PS_MODE']);?>">
				<?php endif;?>

				<input type="submit" name="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" value="<?=Loc::getMessage("SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_CHECKOUT_BUTTON_PAID")?>">
			</form>
	</div>

	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_ROBOXCHANGE_CHECKOUT_WARNING_RETURN') ?></div>
</div>