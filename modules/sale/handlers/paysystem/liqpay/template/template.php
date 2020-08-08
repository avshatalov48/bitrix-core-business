<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<div class="mb-4">
	<p><?=Loc::getMessage("PAYMENT_DESCRIPTION_PS")?> <strong>LiqPAY.com</strong>.</p>
	<p><?=Loc::getMessage("PAYMENT_DESCRIPTION_SUM")?>: <strong><?=CurrencyFormat($params["PAYMENT_SHOULD_PAY"], $params['PAYMENT_CURRENCY'])?></strong></p>
	<?
	if ($params['PAYMENT_CURRENCY'] == "RUB")
		$params['PAYMENT_CURRENCY'] = "RUR";
	?>
	<form action="<?= $params['URL']?>" method="post">
		<input type="hidden" name="operation_xml" value="<?=$params['OPERATION_XML']?>" />
		<input type="hidden" name="signature" value="<?=$params['SIGNATURE'];?>" />
		<input type="submit" value="<?= GetMessage("PAYMENT_PAY")?>" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;"/>
	</form>
</div>
