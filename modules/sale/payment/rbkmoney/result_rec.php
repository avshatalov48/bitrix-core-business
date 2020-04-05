<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	include(GetLangFileName(dirname(__FILE__)."/", "/result_rec.php"));

	$orderId = IntVal($_POST["orderId"]);
	$bCorrectPayment = True;
	$techMessage = "";
	if(!($arOrder = CSaleOrder::GetByID($orderId)))
	{
		$bCorrectPayment = False;
		$techMessage = GetMessage("SALE_RBK_REC_ORDER");
	}
	else
	{
		CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);

		$secretKeyB = CSalePaySystemAction::GetParamValue("SECRET_KEY");

		if ($bCorrectPayment && strlen($secretKeyB) > 0)
		{
			$statusPay = CSalePaySystemAction::GetParamValue("CHANGE_STATUS_PAY");
			$eshopIdB = CSalePaySystemAction::GetParamValue("ESHOP_ID");
			$recipientAmountB = number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, '.', '');
			$recipientCurrencyB = CSalePaySystemAction::GetParamValue("CURRENCY");

			if ($recipientCurrencyB == "RUB")
				$recipientCurrencyB = "RUR";

			$eshopId = trim($_POST["eshopId"]);
			$paymentId = trim($_POST["paymentId"]);
			$serviceName = trim($_POST["serviceName"]);
			$eshopAccount = trim($_POST["eshopAccount"]);
			$recipientAmount = trim($_POST["recipientAmount"]);
			$recipientCurrency = trim($_POST["recipientCurrency"]);
			$paymentStatus = trim($_POST["paymentStatus"]);
			$userName = trim($_POST["userName"]);
			$userEmail = trim($_POST["userEmail"]);
			$paymentData = trim($_POST["paymentData"]);
			$hash = trim($_POST["hash"]);
			$paymentAmount = trim($_POST["paymentAmount"]);
			$paymentCurrency = trim($_POST["paymentCurrency"]);

			if($eshopId == $eshopIdB)
			{
				$checkB = md5($eshopId."::".$orderId."::".$serviceName."::".$eshopAccount."::".$recipientAmount."::".$recipientCurrency."::".$paymentStatus."::".$userName."::".$userEmail."::".$paymentData."::".$secretKeyB);

				if($checkB == $hash)
				{
					if($paymentStatus == 5)
					{
						if($recipientAmountB == $recipientAmount && $recipientCurrencyB == $recipientCurrency)
						{
							if($arOrder["PAYED"] != "Y" && $statusPay == "Y")
								CSaleOrder::PayOrder($arOrder["ID"], "Y");

							$techMessage = GetMessage("SALE_RBK_PROCESS_OK");
						}
						else
							$techMessage = GetMessage("SALE_RBK_REC_SUMM");
					}
					elseif($paymentStatus == 3)
						$techMessage = GetMessage("SALE_RBK_PROCESS_PAY");
					else
						$techMessage = GetMessage("SALE_RBK_REC_TRANS");
				}
				else
					$techMessage = GetMessage("SALE_RBK_REC_SIGN");
			}
			else
				$techMessage = GetMessage("SALE_RBK_REC_PRODUCT");

			$strPS_STATUS_DESCRIPTION = GetMessage('SALE_RBK_CUSTOMER').": ".$userName." (".$userEmail."); ";
			$strPS_STATUS_DESCRIPTION .= GetMessage('SALE_RBK_PAYMENT').": ".$paymentId."; ";
			$strPS_STATUS_DESCRIPTION .= GetMessage('SALE_RBK_DATE').": ".$paymentData.";";

			$arFields = array(
					"PS_STATUS" => "Y",
					"PS_STATUS_CODE" => $paymentStatus,
					"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
					"PS_STATUS_MESSAGE" => $techMessage,
					"PS_SUM" => $recipientAmount,
					"PS_CURRENCY" => $recipientCurrency,
					"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				);

			CSaleOrder::Update($arOrder["ID"], $arFields);
		}
	}
}
?>