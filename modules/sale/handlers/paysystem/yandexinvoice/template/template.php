<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$post = array();
foreach ($_POST as $key => $value)
{
	if (!is_array($value))
	{
		$post[htmlspecialcharsbx($key)] = htmlspecialcharsbx($value);
	}
	else
	{
		foreach ($value as $k1 => $v1)
		{
			if (is_array($v1))
			{
				foreach ($v1 as $k2 => $v2)
				{
					if (!is_array($v2))
						$post[htmlspecialcharsbx($key)."[".htmlspecialcharsbx($k1)."][".htmlspecialcharsbx($k2)."]"] = htmlspecialcharsbx($v2);
				}
			}
			else
			{
				$post[htmlspecialcharsbx($key)."[".htmlspecialcharsbx($k1)."]"] = htmlspecialcharsbx($v1);
			}
		}
	}
}
?>
<div>
	<span><?=Loc::getMessage('SALE_HPS_YANDEX_INVOICE_MESSAGE');?></span>
	<form action="" method="post">
		<?bitrix_sessid_post()?>
		<?foreach ($post as $key => $value):?>
			<input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
		<?endforeach;?>

		<input type="hidden" name="payment_id" value="<?=$params['PAYMENT_ID']?>">
		<input type="hidden" name="accountNumber" value="<?=$params['ACCOUNT_NUMBER']?>">
		<input type="hidden" name="paySystemId" value="<?=$params['PAYSYSTEM_ID']?>">
		<input type="hidden" name="initiate_pay" value="Y">

		<input type="text" name="phone" value="<?=$params['BUYER_PERSON_COMPANY_PHONE']?>" style="margin-top: 5px;">
		<input type="submit" style="margin-top: 5px;" name="send" value="<?=Loc::getMessage('SALE_HPS_YANDEX_INVOICE_SEND');?>">
	</form>
</div>