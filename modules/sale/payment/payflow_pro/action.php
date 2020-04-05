<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
// Input:
// $SALE_INPUT_PARAMS - Array of payment parameters
// $INPUT_CARD_TYPE - Type of credit card
// $INPUT_CARD_NUM - Number of credit card
// $INPUT_CARD_EXP_MONTH - Expiration month of credit card
// $INPUT_CARD_EXP_YEAR - Expiration year of credit card
// $INPUT_CARD_CODE - CVC2 of credit card
// $INPUT_SUM - Payment sum
// $INPUT_CURRENCY - Currency of payment sum

// Output:
// $OUTPUT_ERROR_MESSAGE - Error message
// $OUTPUT_STATUS - Payment status
//	$OUTPUT_STATUS_CODE - Payment status code
//	$OUTPUT_STATUS_DESCRIPTION - Payment status description
//	$OUTPUT_STATUS_MESSAGE - Payment system message
//	$OUTPUT_SUM - Paid sum
//	$OUTPUT_CURRENCY - Currency of paid sum
//	$OUTPUT_RESPONSE_DATE - Date

include(dirname(__FILE__)."/common.php");

$strErrorMessage = "";

$PF_HOST = CSalePaySystemAction::GetParamValue("PAYFLOW_URL");
$PF_PORT = CSalePaySystemAction::GetParamValue("PAYFLOW_PORT");
$PF_USER = CSalePaySystemAction::GetParamValue("PAYFLOW_USER");
$PF_PWD = CSalePaySystemAction::GetParamValue("PAYFLOW_PASSWORD");
$PF_PARTNER = CSalePaySystemAction::GetParamValue("PAYFLOW_PARTNER");
$strExePath = CSalePaySystemAction::GetParamValue("PAYFLOW_EXE_PATH");
$PFPRO_CERT_PATH = CSalePaySystemAction::GetParamValue("PAYFLOW_CERT_PATH");

$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);

$INPUT_CARD_NUM = Trim($INPUT_CARD_NUM);
if (!isset($INPUT_CARD_NUM) || strlen($INPUT_CARD_NUM) <= 0)
	$strErrorMessage .= "Please enter valid credit card number".". ";

$INPUT_CARD_NUM = preg_replace("/[\D]+/", "", $INPUT_CARD_NUM);
if (strlen($INPUT_CARD_NUM) <= 0)
	$strErrorMessage .= "Please enter valid credit card number".". ";

$INPUT_CARD_CODE = Trim($INPUT_CARD_CODE);
if (!isset($INPUT_CARD_CODE) || strlen($INPUT_CARD_CODE) <= 0)
	$strErrorMessage .= "Please enter valid credit card CVC2".". ";

$INPUT_CARD_EXP_MONTH = IntVal($INPUT_CARD_EXP_MONTH);
if ($INPUT_CARD_EXP_MONTH < 1 || $INPUT_CARD_EXP_MONTH > 12)
	$strErrorMessage .= "Please enter valid credit card expiration month".". ";
elseif (strlen($INPUT_CARD_EXP_MONTH) < 2)
	$INPUT_CARD_EXP_MONTH = "0".$INPUT_CARD_EXP_MONTH;

$INPUT_CARD_EXP_YEAR = IntVal($INPUT_CARD_EXP_YEAR);
if ($INPUT_CARD_EXP_YEAR < 2005 || $INPUT_CARD_EXP_YEAR > 2099)
	$strErrorMessage .= "Please enter valid credit card expiration year".". ";
else
	$INPUT_CARD_EXP_YEAR = IntVal($INPUT_CARD_EXP_YEAR - 2000);

$INPUT_SUM = str_replace(",", ".", $INPUT_SUM);
$INPUT_SUM = DoubleVal($INPUT_SUM);
if ($INPUT_SUM <= 0)
	$strErrorMessage .= "Please enter valid sum. ";

$INPUT_CURRENCY = Trim($INPUT_CURRENCY);
if (strlen($INPUT_CURRENCY) <= 0)
	$strErrorMessage .= "Please enter valid currency. ";

$OUTPUT_ERROR_MESSAGE = $strErrorMessage;

if (strlen($strErrorMessage) <= 0)
{
	$ret_var = "";

	if ($INPUT_CURRENCY != "USD")
	{
		$INPUT_SUM = CCurrencyRates::ConvertCurrency($INPUT_SUM, $INPUT_CURRENCY, "USD");

		$additor = 1;
		for ($i = 0; $i < SALE_VALUE_PRECISION; $i++)
			$additor = $additor / 10;

		$INPUT_SUM_tmp = round($INPUT_SUM, SALE_VALUE_PRECISION);
		while ($INPUT_SUM_tmp < $INPUT_SUM)
			$INPUT_SUM_tmp = round($INPUT_SUM_tmp + $additor, SALE_VALUE_PRECISION);

		$INPUT_SUM = $INPUT_SUM_tmp;
	}

	$parms  = "ACCT=".urlencode($INPUT_CARD_NUM);	// Credit card number
	$parms .= "&CVV2=".urlencode($INPUT_CARD_CODE);		// CVV2
	$parms .= "&AMT=".urlencode($INPUT_SUM);						// Amount (US Dollars)
	$parms .= "&EXPDATE=".urlencode($INPUT_CARD_EXP_MONTH.$INPUT_CARD_EXP_YEAR);			// Expiration date
	$parms .= "&PARTNER=".urlencode($PF_PARTNER);		// Partner
	$parms .= "&PWD=".urlencode($PF_PWD);					// Password
	$parms .= "&TENDER=C";						// ...
	$parms .= "&TRXTYPE=S";						// Kind of transaction: Sale
	$parms .= "&USER=".urlencode($PF_USER);				// Login ID
	$parms .= "&VENDOR=".urlencode($PF_USER);			// Vendor ID
	$parms .= "&COMMENT1=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
	$parms .= "&COMMENT2=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]);

	$ret_com = "$strExePath $PF_HOST $PF_PORT \"$parms\" 30";

	putenv("PFPRO_CERT_PATH=".$PFPRO_CERT_PATH);

	exec($ret_com, $arOutput, $ret_var);

	$strOutput = $arOutput[0];
	parse_str($strOutput, $arResult);

	if (is_array($arResult) && strlen($arResult["RESULT"])>0)
	{
		$OUTPUT_STATUS = (($arResult["RESULT"] == 0) ? "Y" : "N");
		$OUTPUT_STATUS_CODE = $arResult["RESULT"];
		$OUTPUT_STATUS_DESCRIPTION = $arResult["RESPMSG"]." - ".$arResult["PREFPSMSG"];
		$OUTPUT_STATUS_MESSAGE = $arResult["PNREF"];
		$OUTPUT_SUM = $INPUT_SUM;
		$OUTPUT_CURRENCY = "USD";
		$OUTPUT_RESPONSE_DATE = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)));

		$arResult["RESULT"] = IntVal($arResult["RESULT"]);
		if ($arResult["RESULT"] != 0)
		{
			if ($arResult["RESULT"] < 0)
				$OUTPUT_STATUS_MESSAGE .= "Communication Error: [".$arResult["RESULT"]."] ".$arResult["RESPMSG"]." - ".$arResult["PREFPSMSG"].". ";
			elseif ($arPaySysRes_tmp["RESULT"] == 125)
				$OUTPUT_STATUS_MESSAGE .= "Your payment is declined by Fraud Service. Please contact us to make payment".". ";
			elseif ($arResult["RESULT"] == 126)
				$OUTPUT_STATUS_MESSAGE .= "Your payment is under review by Fraud Service. We contact you in 48 hours to get more specific information".". ";
			elseif (is_set($arErrorCodes, $arResult["RESULT"]))
				$OUTPUT_STATUS_MESSAGE .= $arErrorCodes[$arResult["RESULT"]].". ";
			else
				$OUTPUT_STATUS_MESSAGE .= "Unknown error".". ";
		}
	}
	else
		$OUTPUT_STATUS_MESSAGE .= "Response error".". ";

/*

	$OUTPUT_STATUS = "Y";
	$OUTPUT_STATUS_CODE = "44FRT";
	$OUTPUT_STATUS_DESCRIPTION = "Good test";
	$OUTPUT_STATUS_MESSAGE = "Yes";
	$OUTPUT_SUM = $INPUT_SUM;
	$OUTPUT_CURRENCY = "USD";
	$OUTPUT_RESPONSE_DATE = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)));
	$OUTPUT_STATUS_MESSAGE = "";
*/
}
?>