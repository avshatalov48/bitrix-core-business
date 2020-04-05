<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use \Bitrix\Sale\Order;

include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));
$inv_id = IntVal($_REQUEST["InvId"]);
$paymentId = intval($_REQUEST["shp_payment_id"]);

if($inv_id > 0 && $paymentId > 0)
{
	$bCorrectPayment = True;

	$out_summ = trim($_REQUEST["OutSum"]);
	$crc = trim($_REQUEST["SignatureValue"]);

	/** @var \Bitrix\Sale\Order $order */
	$order = Order::load($inv_id);
	if (!$order)
		$bCorrectPayment = false;

	$arOrder = $order->getFieldValues();

	$payment = $order->getPaymentcollection()->getItemById($paymentId);
	if (!$payment)
		$bCorrectPayment = false;

	if ($bCorrectPayment)
		CSalePaySystemAction::InitParamArrays($arOrder, $inv_id, '', array(), $payment->getFieldValues());

	$changePayStatus =  trim(CSalePaySystemAction::GetParamValue("CHANGE_STATUS_PAY"));
	$mrh_pass2 =  CSalePaySystemAction::GetParamValue("ShopPassword2");

	if(strlen($mrh_pass2) <= 0)
		$bCorrectPayment = False;

	$strCheck = md5($out_summ.":".$inv_id.":".$mrh_pass2.':shp_payment_id='.$paymentId);

	if ($bCorrectPayment && ToUpper($crc) != ToUpper($strCheck))
		$bCorrectPayment = False;
	
	if($bCorrectPayment)
	{
		$strPS_STATUS_DESCRIPTION = GetMessage('SALE_RES_NUMBER').": ".$inv_id;
		$strPS_STATUS_DESCRIPTION .= "; ".GetMessage('SALE_RES_DATEPAY').": ".date("d.m.Y H:i:s");
		if (isset($_REQUEST["IncCurrLabel"]) && strlen($_REQUEST["IncCurrLabel"]) > 0)
			$strPS_STATUS_DESCRIPTION .= "; ".GetMessage('SASP_RES_PAY_TYPE').": ".$_REQUEST["IncCurrLabel"];
		
		$strPS_STATUS_MESSAGE = GetMessage('SASP_RES_PAYED');
		
		$arFields = array(
			"PS_STATUS" => "Y",
			"PS_STATUS_CODE" => "-",
			"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
			"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
			"PS_SUM" => $out_summ,
			"PS_CURRENCY" => $arOrder["CURRENCY"],
			"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime(),
		);

		if (roundEx(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2) == roundEx($out_summ, 2) && $changePayStatus == "Y")
		{
			$result = $payment->setField('PAID', 'Y');

			$APPLICATION->RestartBuffer();

			if ($result->isSuccess())
			{
				$result = $payment->setFields($arFields);
				if ($result->isSuccess())
				{
					$result = $order->save();
					if ($result->isSuccess())
						echo "OK".$arOrder["ID"];
				}
			}
		}
	}
}
?>