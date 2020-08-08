<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	include(GetLangFileName(dirname(__FILE__)."/", "/result_rec.php"));

	$cs1 = intval($_POST["order_id"]);
	if($cs1 <= 0)
		$cs1 = intval($_POST["cs1"]);
	$bCorrectPayment = True;
	$techMessage = "";
	if(!($arOrder = CSaleOrder::GetByID($cs1)))
	{
		$bCorrectPayment = False;
		$techMessage = GetMessage("SALE_CHR_REC_ORDER");
	}

	if ($bCorrectPayment)
		CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);

	$sharedsecB = CSalePaySystemAction::GetParamValue("SHARED");

	if($sharedsecB == '')
		$bCorrectPayment = False;

	if ($bCorrectPayment)
	{
		$productIdB = CSalePaySystemAction::GetParamValue("PRODUCT_ID");
		$orderIdB = CSalePaySystemAction::GetParamValue("ORDER_ID");
		$product_priceB = number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, '.', '');

		$product_id = trim($_POST["product_id"]);
		$customer_id = trim($_POST["customer_id"]);
		$transaction_id = trim($_POST["transaction_id"]);
		$transaction_type = trim($_POST["transaction_type"]);
		$total = trim($_POST["total"]);
		$currency = trim($_POST["currency"]);
		$date = trim($_POST["date"]);
		$sign = trim($_POST["sign"]);
		
		if($product_id == $productIdB)
		{
			$checkB = md5($sharedsecB.$customer_id.$transaction_id.$transaction_type.$total);
			if($checkB == $sign)
			{
				if($transaction_type == "onetime" || $transaction_type == "Purchase")
				{
					if($product_priceB == $total)
					{

						if($arOrder["PAYED"] != "Y")
							CSaleOrder::PayOrder($arOrder["ID"], "Y");
					}
					else
						$techMessage = GetMessage("SALE_CHR_REC_SUMM");
				}
				else
					$techMessage = GetMessage("SALE_CHR_REC_TRANS");
			}
			else
				$techMessage = GetMessage("SALE_CHR_REC_SIGN");
		}
		else
			$techMessage = GetMessage("SALE_CHR_REC_PRODUCT");
		
		$strPS_STATUS_DESCRIPTION = "Customer id: ".$customer_id.";<br />";
		$strPS_STATUS_DESCRIPTION .= "Transaction id: ".$transaction_id.";<br />";
		$strPS_STATUS_DESCRIPTION .= "Date payed: ".$date.";<br />";
		$strPS_STATUS_DESCRIPTION .= "Payment type: ".$_POST["payment_type"].";<br />".
			"Buyer: ".$_POST["name"].";<br />".
			"Email: ".$_POST["email"].";<br />".
			"Country: ".$_POST["country"].";<br />".
			"City: ".$APPLICATION->ConvertCharset($_POST["city"], "utf-8", SITE_CHARSET).";<br />".
			"Street: ".$APPLICATION->ConvertCharset($_POST["street"], "utf-8", SITE_CHARSET).";<br />".
			"Phone: ".$_POST["phone"].";<br />".
			"Index: ".$_POST["zip"].";<br />";

		$arFields = array(
				"PS_STATUS" => ($transaction_type == "onetime" || $transaction_type == "Purchase") ? "Y" : "N",
				"PS_STATUS_CODE" => $transaction_type,
				"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
				"PS_STATUS_MESSAGE" => $techMessage,
				"PS_SUM" => $total,
				"PS_CURRENCY" => $currency,
				"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
			);

		CSaleOrder::Update($arOrder["ID"], $arFields);
	}
}
?>