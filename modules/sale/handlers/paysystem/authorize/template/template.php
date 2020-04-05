<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<form action="" method="post">
	<table border="0" cellpadding="3" cellspacing="0" width="100%">
		<tr>
			<td align="right"><?=Loc::getMessage("AN_CC")?></td>
			<td><input type="text" name="ccard_num" size="30" value=""></td>
		</tr>
		<tr>
			<td align="right"><?=Loc::getMessage("AN_CC_DATE")?></td>
			<td>
				<input type="text" name="ccard_date1" size="5" value="">
				/
				<input type="text" name="ccard_date2" size="5" value="">
			</td>
		</tr>
		<tr>
			<td align="right"><?=Loc::getMessage("AN_CC_CVV2")?></td>
			<td>
				<input type="text" name="ccard_code" size="5" value="">
			</td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<input type="hidden" name="payment_id" value="<?=htmlspecialcharsbx($params['PAYMENT_ID']);?>">
				<input type="submit" value="<?=Loc::getMessage("AN_CC_BUTTON")?>" class="inputbutton">
			</td>
		</tr>
	</table>
</form>
