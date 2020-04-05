<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use \Bitrix\Sale\Order;

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$orderId = intval($_POST['LMI_PAYMENT_NO']);
	$paymentId = intval($_POST['PAYMENT_ID']);
	$bCorrectPayment = true;

/** @var \Bitrix\Sale\Order $order */
	$order = Order::load($orderId);
	if (!$order)
		$bCorrectPayment = false;

	$payment = $order->getPaymentCollection()->getItemById($paymentId);
	if (!$payment)
		$bCorrectPayment = false;

	$arOrder = $order->getFieldValues();

	if ($bCorrectPayment)
		CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], '', array(), $payment);

	$CNST_SECRET_KEY = CSalePaySystemAction::GetParamValue("CNST_SECRET_KEY");

	if (strlen($CNST_SECRET_KEY) <= 0)
		$bCorrectPayment = false;

	$CNST_PAYEE_PURSE = CSalePaySystemAction::GetParamValue("SHOP_ACCT");
	$currency = CSalePaySystemAction::GetParamValue("CURRENCY");

	if (strlen($currency) <= 0 || $currency == "RUR")
		$currency = "RUB";

	if($_POST["LMI_PREREQUEST"] == "1" || $_POST["LMI_PREREQUEST"] == "2")
	{
	
		if(CSalePaySystemAction::GetParamValue("SHOULD_PAY") == DoubleVal($_POST["LMI_PAYMENT_AMOUNT"])
			&& $currency == DoubleVal($_POST["LMI_CURRENCY"])
			&& $CNST_PAYEE_PURSE == $_POST["LMI_MERCHANT_ID"])
		{
			$APPLICATION->RestartBuffer();
			echo "YES";
			die();
		}
		else
		{
			$APPLICATION->RestartBuffer();
			echo "Параметры платежа несовпадают.";
			die();
		}
	}
	else
	{
		$strCheck = base64_encode(pack("H*", md5($_POST["LMI_MERCHANT_ID"].";".$_POST["LMI_PAYMENT_NO"].";".$_POST["LMI_SYS_PAYMENT_ID"].";".$_POST["LMI_SYS_PAYMENT_DATE"].";".$_POST["LMI_PAYMENT_AMOUNT"].";".$_POST["LMI_CURRENCY"].";".$_POST["LMI_PAID_AMOUNT"].";".$_POST["LMI_PAID_CURRENCY"].";".$_POST["LMI_PAYMENT_SYSTEM"].";".$_POST["LMI_SIM_MODE"].";".$CNST_SECRET_KEY)));
		if ($bCorrectPayment && $_POST["LMI_HASH"] != $strCheck)
			$bCorrectPayment = false;

		if ($bCorrectPayment)
		{
			$strPS_STATUS_DESCRIPTION = "";
			if ($_POST["LMI_SIM_MODE"] != 0)
				$strPS_STATUS_DESCRIPTION .= "тестовый режим, реально деньги не переводились; ";
			$strPS_STATUS_DESCRIPTION .= "номер продавца - ".$_POST["LMI_MERCHANT_ID"]."; ";
			$strPS_STATUS_DESCRIPTION .= "номер счета - ".$_POST["LMI_SYS_INVS_NO"]."; ";
			$strPS_STATUS_DESCRIPTION .= "номер платежа - ".$_POST["LMI_SYS_PAYMENT_ID"]."; ";
			$strPS_STATUS_DESCRIPTION .= "дата платежа - ".$_POST["LMI_SYS_PAYMENT_DATE"]."";
			$strPS_STATUS_DESCRIPTION .= "платежная система - ".$_POST["LMI_PAYMENT_SYSTEM"]."";

			$strPS_STATUS_MESSAGE = "";
			if (isset($_POST["LMI_PAYER_PURSE"]) && strlen($_POST["LMI_PAYER_PURSE"])>0)
				$strPS_STATUS_MESSAGE .= "кошелек покупателя - ".$_POST["LMI_PAYER_PURSE"]."; ";
			if (isset($_POST["LMI_PAYER_WM"]) && strlen($_POST["LMI_PAYER_WM"])>0)
				$strPS_STATUS_MESSAGE .= "WMId покупателя - ".$_POST["LMI_PAYER_WM"]."; ";
			if (isset($_POST["LMI_PAYMER_NUMBER"]) && strlen($_POST["LMI_PAYMER_NUMBER"])>0)
				$strPS_STATUS_MESSAGE .= "номер ВМ-карты - ".$_POST["LMI_PAYMER_NUMBER"]."; ";
			if (isset($_POST["LMI_PAYMER_EMAIL"]) && strlen($_POST["LMI_PAYMER_EMAIL"])>0)
				$strPS_STATUS_MESSAGE .= "paymer.com e-mail покупателя - ".$_POST["LMI_PAYMER_EMAIL"]."; ";
			if (isset($_POST["LMI_TELEPAT_PHONENUMBER"]) && strlen($_POST["LMI_TELEPAT_PHONENUMBER"])>0)
				$strPS_STATUS_MESSAGE .= "телефон покупателя - ".$_POST["LMI_TELEPAT_PHONENUMBER"]."; ";
			if (isset($_POST["LMI_TELEPAT_ORDERID"]) && strlen($_POST["LMI_TELEPAT_ORDERID"])>0)
				$strPS_STATUS_MESSAGE .= "платеж в Телепате - ".$_POST["LMI_TELEPAT_ORDERID"]."";

			$arFields = array(
					"PS_STATUS" => "Y",
					"PS_STATUS_CODE" => "-",
					"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
					"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
					"PS_SUM" => $_POST["LMI_PAYMENT_AMOUNT"],
					"PS_CURRENCY" => $arOrder["CURRENCY"],
					"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime()
				);

			if (CSalePaySystemAction::GetParamValue("SHOULD_PAY") == $_POST["LMI_PAYMENT_AMOUNT"]
				&& $currency == DoubleVal($_POST["LMI_CURRENCY"])
				&& $CNST_PAYEE_PURSE == $_POST["LMI_MERCHANT_ID"]
				&& CSalePaySystemAction::GetParamValue("PAYED") != "Y"
				)
			{
				$resPayment = $payment->setField('PAID', 'Y');
			}

			$resPayment = $payment->setFields($arFields);
			if ($resPayment->isSuccess())
				$result = $order->save();
		}
	}
}
?>