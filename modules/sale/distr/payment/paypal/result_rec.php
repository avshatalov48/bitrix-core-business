<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Sale\Order;

include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

if(!isset($GLOBALS["SALE_INPUT_PARAMS"]))
	$GLOBALS["SALE_INPUT_PARAMS"] = array();

$req = "";
if(strlen($_REQUEST['tx']) > 0) // PDT
{
	$req = 'cmd=_notify-synch';
	$tx_token = $_REQUEST['tx'];
	$auth_token = CSalePaySystemAction::GetParamValue("IDENTITY_TOKEN");
	$req .= "&tx=".$tx_token."&at=".$auth_token;

	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n";
	$header .= "User-Agent: 1C-Bitrix\r\n\r\n";
}
elseif(strlen($_POST['txn_id']) > 0 && $_SERVER["REQUEST_METHOD"] == "POST") // IPN
{
	$tx = trim($_POST["txn_id"]);
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value)
	{
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}

	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n";
	$header .= "User-Agent: 1C-Bitrix\r\n\r\n";
}
if(strlen($req) > 0)
{
	$domain = "";

	if(CSalePaySystemAction::GetParamValue("TEST") == "Y")
		$domain = "sandbox.";

	if(CSalePaySystemAction::GetParamValue("SSL_ENABLE") == "Y")
		$fp = fsockopen ("ssl://www.".$domain."paypal.com", 443, $errno, $errstr, 30);
	else
		$fp = fsockopen ("www.".$domain."paypal.com", 80, $errno, $errstr, 30);

	if($fp)
	{
		fputs ($fp, $header . $req);
		$res = "";
		$headerdone = false;
		while(!feof($fp))
		{
			$line = fgets ($fp, 1024);
			if(strcmp($line, "\r\n") == 0)
				$headerdone = true;
			elseif($headerdone)
				$res .= $line;
		}

		// parse the data
		$lines = explode("\n", $res);
		$keyarray = array();
		if(strcmp ($lines[0], "SUCCESS") == 0)
		{
			for ($i=1, $cnt = count($lines); $i < $cnt; $i++)
			{
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}

			$strPS_STATUS_MESSAGE = "";
			$strPS_STATUS_MESSAGE .= "Name: ".$keyarray["first_name"]." ".$keyarray["last_name"]."; ";
			$strPS_STATUS_MESSAGE .= "Email: ".$keyarray["payer_email"]."; ";
			$strPS_STATUS_MESSAGE .= "Item: ".$keyarray["item_name"]."; ";
			$strPS_STATUS_MESSAGE .= "Amount: ".$keyarray["mc_gross"]."; ";

			$strPS_STATUS_DESCRIPTION = "";
			$strPS_STATUS_DESCRIPTION .= "Payment status - ".$keyarray["payment_status"]."; ";
			$strPS_STATUS_DESCRIPTION .= "Payment sate - ".$keyarray["payment_date"]."; ";

			/** @var \Bitrix\Sale\Order $order */
			$order = Order::load($keyarray["custom"]);

			$payment = $order->getPaymentCollection()->getItemById($keyarray["item_number"]);

			$arOrder = $order->getFieldValues();

			$arFields = array(
					"PS_STATUS" => "Y",
					"PS_STATUS_CODE" => "-",
					"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
					"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
					"PS_SUM" => $keyarray["mc_gross"],
					"PS_CURRENCY" => $keyarray["mc_currency"],
					"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime()
				);
			$arFields["PAY_VOUCHER_NUM"] = $tx_token;
			$arFields["PAY_VOUCHER_DATE"] = new \Bitrix\Main\Type\Date();

			$result = new \Bitrix\Sale\Result();
			if (intval($payment->getField('SUM')) == IntVal($keyarray["mc_gross"])
				&& ToLower($keyarray["receiver_email"]) == ToLower(CSalePaySystemAction::GetParamValue("BUSINESS"))
				&& $keyarray["payment_status"] == "Completed"
				)
			{
				$result = $payment->setField('PAID', 'Y');
			}

			if ($result->isSuccess())
			{
				$result = $payment->setFields($arFields);
				if ($result->isSuccess())
					$order->save();
			}

			$firstname = $keyarray['first_name'];
			$lastname = $keyarray['last_name'];
			$itemname = $keyarray['item_name'];
			$amount = $keyarray['mc_gross'];

			echo "<p><h3>".GetMessage("PPL_T1")."</h3></p>";

			echo "<b>".GetMessage("PPL_T2")."</b><br>\n";
			echo "<li>".GetMessage("PPL_T3").": $firstname $lastname</li>\n";
			echo "<li>".GetMessage("PPL_T4").": $itemname</li>\n";
			echo "<li>".GetMessage("PPL_T5").": $amount</li>\n";
		}
		elseif(strcmp ($res, "VERIFIED") == 0)
		{
			$strPS_STATUS_MESSAGE = "";
			$strPS_STATUS_MESSAGE .= GetMessage("PPL_T3").": ".$_POST["first_name"]." ".$_POST["last_name"]."; ";
			$strPS_STATUS_MESSAGE .= "Email: ".$_POST["payer_email"]."; ";
			$strPS_STATUS_MESSAGE .= GetMessage("PPL_T4").": ".$_POST["item_name"]."; ";
			$strPS_STATUS_MESSAGE .= GetMessage("PPL_T5").": ".$_POST["mc_gross"]."; ";

			$strPS_STATUS_DESCRIPTION = "";
			$strPS_STATUS_DESCRIPTION .= "Payment status - ".$_POST["payment_status"]."; ";
			$strPS_STATUS_DESCRIPTION .= "Payment sate - ".$_POST["payment_date"]."; ";

			/** @var \Bitrix\Sale\Order $order */
			$order = Order::load($_POST["custom"]);

			$payment = $order->getPaymentCollection()->getItemById($_POST["item_number"]);

			$arOrder = $order->getFieldValues();

			$arFields = array(
					"PS_STATUS" => "Y",
					"PS_STATUS_CODE" => "-",
					"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
					"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
					"PS_SUM" => $_POST["mc_gross"],
					"PS_CURRENCY" => $_POST["mc_currency"],
					"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime(),
					"USER_ID" => $arOrder["USER_ID"],
				);
			$arFields["PAY_VOUCHER_NUM"] = $tx;
			$arFields["PAY_VOUCHER_DATE"] = new \Bitrix\Main\Type\Date();

			if (intval($payment->getField('SUM')) == IntVal($_POST["mc_gross"])
				&& ToLower($_POST["receiver_email"]) == ToLower(CSalePaySystemAction::GetParamValue("BUSINESS"))
				&& $_POST["payment_status"] == "Completed"
				&& strlen($payment->getField("PAY_VOUCHER_NUM")) <= 0
				&& $payment->getField("PAY_VOUCHER_NUM") != $tx
				)
			{
				$result = $payment->setField('PAID', 'Y');
			}

			if(strlen($payment->getField("PAY_VOUCHER_NUM")) <= 0 || $payment->getField("PAY_VOUCHER_NUM") != $tx)
			{
				$result = $payment->setFields($arFields);
				if ($result->isSuccess())
					$order->save();
			}
		}
		else
			echo "<p>".GetMessage("PPL_I1")."</p>";
	}
	else
		echo "<p>".GetMessage("PPL_I2")."</p>";

	fclose ($fp);
}
?>

<?=GetMessage("PPL_I3")?><br /><br /><?=GetMessage("PPL_I4")?>