<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<?=Loc::getMessage("PAYMENT_DESCRIPTION_PS")?> <b>LiqPAY.com</b>.<br /><br />
<?=Loc::getMessage("PAYMENT_DESCRIPTION_SUM")?>: <b><?=CurrencyFormat($params["PAYMENT_SHOULD_PAY"], $params['PAYMENT_CURRENCY'])?></b><br /><br />
<?
if ($params['PAYMENT_CURRENCY'] == "RUB")
	$params['PAYMENT_CURRENCY'] = "RUR";
?>
<form action="<?= $params['URL']?>" method="post">
	<input type="hidden" name="operation_xml" value="<?=$params['OPERATION_XML']?>" />
	<input type="hidden" name="signature" value="<?=$params['SIGNATURE'];?>" />
	<input type="submit" value="<?= GetMessage("PAYMENT_PAY")?>" />
</form>
