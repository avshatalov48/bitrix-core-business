<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$iTestTransaction = CSalePaySystemAction::GetParamValue("TEST_TRANSACTION");
$strYourInstId = CSalePaySystemAction::GetParamValue("SHOP_ID");
?>
<table border="0" cellspacing="0" cellpadding="1" width="100%"><tr><td class="tableborder">
<table border="0" cellpadding="3" cellspacing="0" width="100%">
	<form action="https://select.worldpay.com/wcc/purchase" method="post" target="_blank">
	<tr>
		<td align="center" class="tablebody" colspan="2">
			<font class="tablebodytext">
			<input type="hidden" name="instId" value="<?= $strYourInstId ?>">
			<input type="hidden" name="cartId" value="<?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?>">
			<input type="hidden" name="amount" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]) ?>">
			<input type="hidden" name="currency" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?>">
			<?if (IntVal($iTestTransaction) > 0):?>
				<input type="hidden" name="testMode" value="<?= $iTestTransaction ?>">
			<?endif;?>
			<input type="hidden" name="desc" value="Order #<?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?>">

			<!--  order properties codes ->  -->
			<?
			$arTmp = array("name" => "PAYER_NAME", "tel" => "PHONE", "email" => "EMAIL",
					"fax" => "FAX", "address" => "ADDRESS", "postcode" => "ZIP",
					"country" => "COUNTRY"
				);
			foreach ($arTmp as $key => $value)
			{
				if (($val = CSalePaySystemAction::GetParamValue($value)) !== False)
				{
					?><input type="hidden" name="<?= $key ?>" value="<?= htmlspecialcharsbx($val) ?>"><?
				}
			}
			?>

			<input type="hidden" name="MC_CurrentStep" value="<?= IntVal($GLOBALS["CurrentStep"]) ?>">
			<input type="submit" value="Submit to WorldPay for Payment Now" class="inputbutton">
			</font>
		</td>
	</tr>
	</form>
</table>
</td></tr></table>
