<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include_once(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));
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

$INPUT_CARD_NUM = preg_replace("/[\D]+/", "", $INPUT_CARD_NUM);
if (strlen($INPUT_CARD_NUM) <= 0)
	$strErrorMessage .= GetMessage("AN_CC_NUM")." ";

$INPUT_CARD_CODE = preg_replace("[\D]+", "", $INPUT_CARD_CODE);

$INPUT_CARD_EXP_MONTH = IntVal($INPUT_CARD_EXP_MONTH);
if ($INPUT_CARD_EXP_MONTH < 1 || $INPUT_CARD_EXP_MONTH > 12)
	$strErrorMessage .= GetMessage("AN_CC_MONTH")." ";
elseif (strlen($INPUT_CARD_EXP_MONTH) < 2)
	$INPUT_CARD_EXP_MONTH = "0".$INPUT_CARD_EXP_MONTH;

$INPUT_CARD_EXP_YEAR = IntVal($INPUT_CARD_EXP_YEAR);
if ($INPUT_CARD_EXP_YEAR < 2005)
	$strErrorMessage .= GetMessage("AN_CC_YEAR")." ";

$INPUT_SUM = str_replace(",", ".", $INPUT_SUM);
$INPUT_SUM = DoubleVal($INPUT_SUM);
if ($INPUT_SUM <= 0)
	$strErrorMessage .= GetMessage("AN_CC_SUM")." ";

$INPUT_CURRENCY = Trim($INPUT_CURRENCY);
if (strlen($INPUT_CURRENCY) <= 0)
	$strErrorMessage .= GetMessage("AN_CC_CURRENCY")." ";

$OUTPUT_ERROR_MESSAGE = $strErrorMessage;

if (strlen($strErrorMessage) <= 0)
{
	// Merchant Account Information
	$strPostQueryString  = "x_version=3.1";
	$strPostQueryString .= "&x_login=".urlencode(CSalePaySystemAction::GetParamValue("PS_LOGIN"));
	$strPostQueryString .= "&x_tran_key=".urlencode(CSalePaySystemAction::GetParamValue("PS_TRANSACTION_KEY"));
	$strPostQueryString .= "&x_test_request=".(CSalePaySystemAction::GetParamValue("TEST_TRANSACTION") ? "TRUE" : "FALSE")."";

	// Gateway Response Configuration
	$strPostQueryString .= "&x_delim_data=True";
	$strPostQueryString .= "&x_relay_response=False";
	$strPostQueryString .= "&x_delim_char=,";
	$strPostQueryString .= "&x_encap_char=|";

	$arTmp = array("x_first_name" => "FIRST_NAME",	"x_last_name" => "LAST_NAME",
			"x_company" => "COMPANY",	"x_address" => "ADDRESS",	"x_city" => "CITY",
			"x_state" => "STATE",	"x_zip" => "ZIP",	"x_country" => "COUNTRY",
			"x_phone" => "PHONE",	"x_fax" => "FAX"
		);
	foreach ($arTmp as $key => $value)
	{
		if (($val = CSalePaySystemAction::GetParamValue($value)) !== False)
			$strPostQueryString .= "&".$key."=".urlencode($val);
	}

	// Additional Customer Data
	$strPostQueryString .= "&x_cust_id=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["USER_ID"]);
	if (($val = CSalePaySystemAction::GetParamValue("REMOTE_ADDR")) !== False)
		$strPostQueryString .= "&x_customer_ip=".urlencode($val);

	// Email Settings
	if (($val = CSalePaySystemAction::GetParamValue("EMAIL")) !== False)
		$strPostQueryString .= "&x_email=".urlencode($val);

	$strPostQueryString .= "&x_email_customer=FALSE";
	$strPostQueryString .= "&x_merchant_email=".urlencode(COption::GetOptionString("sale", "order_email", ""));

	// Invoice Information
	$strPostQueryString .= "&x_invoice_num=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
	$strPostQueryString .= "&x_description=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]);

	// Customer Shipping Address
	$arTmp = array("x_ship_to_first_name" => "SHIP_FIRST_NAME",
			"x_ship_to_last_name" => "SHIP_LAST_NAME",	"x_ship_to_company" => "SHIP_COMPANY",
			"x_ship_to_address" => "SHIP_ADDRESS",	"x_ship_to_city" => "SHIP_CITY",
			"x_ship_to_state" => "SHIP_STATE",	"x_ship_to_zip" => "SHIP_ZIP",
			"x_ship_to_country" => "SHIP_COUNTRY"
		);
	foreach ($arTmp as $key => $value)
	{
		if (($val = CSalePaySystemAction::GetParamValue($value)) !== False)
			$strPostQueryString .= "&".$key."=".urlencode($val);
	}

	// Transaction Data
	$strPostQueryString .= "&x_amount=".$INPUT_SUM;
	$strPostQueryString .= "&x_currency_code=".$INPUT_CURRENCY;
	$strPostQueryString .= "&x_method=CC";
	$strPostQueryString .= "&x_type=AUTH_CAPTURE";
	$strPostQueryString .= "&x_recurring_billing=NO";
	$strPostQueryString .= "&x_card_num=".urlencode($INPUT_CARD_NUM);
	$strPostQueryString .= "&x_exp_date=".urlencode($INPUT_CARD_EXP_MONTH.$INPUT_CARD_EXP_YEAR);	// MMYYYY
	$strPostQueryString .= "&x_card_code=".urlencode($INPUT_CARD_CODE);

	$strResult = QueryGetData("secure.authorize.net", 443, "/gateway/transact.dll", $strPostQueryString, $errno, $errstr, "POST", "ssl://");

	$mass = explode("\|,\|", "|,".$strResult);

	$hashValue = CSalePaySystemAction::GetParamValue("HASH_VALUE");
	if (strlen($hashValue)>0)
	{
		if (md5($hashValue.(CSalePaySystemAction::GetParamValue("PS_LOGIN")).$mass[7].$INPUT_SUM) != strtolower($mass[38]))
		{
			$mass = array();
			$mass[1] = 3;
			$mass[4] = "MD5 transaction signature is incorrect!";
			$mass[3] = 0;
			$mass[2] = 0;
		}
	}

	$OUTPUT_STATUS = ((IntVal($mass[1])==1) ? "Y" : "N");
	$OUTPUT_STATUS_CODE = $mass[3];

	if ($OUTPUT_STATUS=="Y")
		$OUTPUT_STATUS_DESCRIPTION = "Approval Code: ".$mass[5].(!empty($mass[7]) ? "; Transaction ID: ".$mass[7] : "");
	else
		$OUTPUT_STATUS_DESCRIPTION = (IntVal($mass[1])==2 ? "Declined" : "Error").": ".$mass[4]." (Reason Code ".$mass[3]." / Sub ".$mass[2].")";

	$OUTPUT_STATUS_MESSAGE = "";
	if (!empty($mass[6]))
		$OUTPUT_STATUS_MESSAGE .= "\nAVS Result: [".$mass[6]."] ".$arAVSErr[$mass[6]].";";

	if (!empty($mass[39]))
		$OUTPUT_STATUS_MESSAGE .= "\nCard Code Result: [".$mass[39]."] ".$arCVVErr[$mass[39]].";";

	if (!empty($mass[40]))
		$OUTPUT_STATUS_MESSAGE .= "\nCAVV: [".$mass[40]."] ".$arCAVVErr[$mass[40]].";";

	$OUTPUT_SUM = $mass[10];
	$OUTPUT_CURRENCY = $INPUT_CURRENCY;
	$OUTPUT_RESPONSE_DATE = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)));
}
?>