<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include_once(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));
include(dirname(__FILE__)."/common.php");

$strErrorMessage = "";

$bCanProcess = False;
$bSuccessProcess = False;
$year = date('Y');
if ($_REQUEST["pay_this_order"] == "Y")
{
	$bCanProcess = True;

	$INPUT_CARD_NUM = Trim($_REQUEST["ccard_num"]);
	if (!isset($INPUT_CARD_NUM) || strlen($INPUT_CARD_NUM) <= 0)
		$strErrorMessage .= GetMessage("AN_CC_NUM")." ";

	$INPUT_CARD_NUM = preg_replace("/[\D]+/", "", $INPUT_CARD_NUM);
	if (strlen($INPUT_CARD_NUM) <= 0)
		$strErrorMessage .= GetMessage("AN_CC_NUM")." ";

	$INPUT_CARD_EXP_MONTH = IntVal($_REQUEST["ccard_date1"]);
	if ($INPUT_CARD_EXP_MONTH < 1 || $INPUT_CARD_EXP_MONTH > 12)
		$strErrorMessage .= GetMessage("AN_CC_MONTH")." ";
	elseif (strlen($INPUT_CARD_EXP_MONTH) < 2)
		$INPUT_CARD_EXP_MONTH = "0".$INPUT_CARD_EXP_MONTH;

	$INPUT_CARD_EXP_YEAR = IntVal($_REQUEST["ccard_date2"]);
	if ($INPUT_CARD_EXP_YEAR < $year)
		$strErrorMessage .= GetMessage("AN_CC_YEAR")." ";

	$INPUT_CARD_CODE = Trim($_REQUEST["ccard_code"]);

	if (strlen($strErrorMessage) > 0)
		$bCanProcess = False;
}

$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);

if ($bCanProcess)
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
	$strPostQueryString .= "&x_customer_ip=".urlencode($_SERVER["REMOTE_ADDR"]);

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
	$strPostQueryString .= "&x_amount=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]);
	$strPostQueryString .= "&x_currency_code=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]);
	$strPostQueryString .= "&x_method=CC";
	$strPostQueryString .= "&x_type=AUTH_CAPTURE";
	$strPostQueryString .= "&x_recurring_billing=NO";
	$strPostQueryString .= "&x_card_num=".urlencode($INPUT_CARD_NUM);
	$strPostQueryString .= "&x_exp_date=".urlencode($INPUT_CARD_EXP_MONTH.$INPUT_CARD_EXP_YEAR);	// MMYYYY
	$strPostQueryString .= "&x_card_code=".urlencode($INPUT_CARD_CODE);

	// Level 2 Data
	$strPostQueryString .= "&x_tax=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["TAX_VALUE"]);
	$strPostQueryString .= "&x_freight=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]);

	$strResult = QueryGetData("secure.authorize.net", 443, "/gateway/transact.dll", $strPostQueryString, $errno, $errstr, "POST", "ssl://");

	$mass = explode("|,|", "|,".$strResult);

	$strHashValue = CSalePaySystemAction::GetParamValue("HASH_VALUE");
	if (strlen($strHashValue)>0)
	{
		if (md5($strHashValue.(CSalePaySystemAction::GetParamValue("PS_LOGIN")).$mass[7].sprintf("%.2f",$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"])) != strtolower($mass[38]))
		{
			$mass = array();
			$mass[1] = 3;
			$mass[4] = "MD5 transaction signature is incorrect!";
			$mass[3] = 0;
			$mass[2] = 0;
		}
	}

	$strPS_STATUS = ((IntVal($mass[1])==1) ? "Y" : "N");
	$strPS_STATUS_CODE = $mass[3];
	if ($strPS_STATUS=="Y")
		$strPS_STATUS_DESCRIPTION = "Approval Code: ".$mass[5].(!empty($mass[7]) ? "; Transaction ID: ".$mass[7] : "");
	else
	{
		$strPS_STATUS_DESCRIPTION = (IntVal($mass[1])==2 ? "Declined" : "Error").": ".$mass[4]." (Reason Code ".$mass[3]." / Sub ".$mass[2].")";
		$strErrorMessage .= (IntVal($mass[1])==2 ? "Transaction was declined" : "Error while processing transaction").": ".$mass[4]." (".$mass[3]."/".$mass[2].")";
	}

	$strPS_STATUS_MESSAGE = "";
	if (!empty($mass[6]))
		$strPS_STATUS_MESSAGE .= "\nAVS Result: [".$mass[6]."] ".$arAVSErr[$mass[6]].";";

	if (!empty($mass[39]))
		$strPS_STATUS_MESSAGE .= "\nCard Code Result: [".$mass[39]."] ".$arCVVErr[$mass[39]].";";

	if (!empty($mass[40]))
		$strPS_STATUS_MESSAGE .= "\nCAVV: [".$mass[40]."] ".$arCAVVErr[$mass[40]].";";

	$strPS_SUM = $mass[10];

	$arFields = array(
			"PS_STATUS" => $strPS_STATUS,
			"PS_STATUS_CODE" => $strPS_STATUS_CODE,
			"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
			"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
			"PS_SUM" => $strPS_SUM,
			"PS_CURRENCY" => $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
			"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
		);

	if (CSalePaySystemAction::GetParamValue("AUTO_PAY") === 'Y')
	{
		$arOrder = CSaleOrder::GetByID($ORDER_ID);
		if ($arOrder["PRICE"] == $arFields["PS_SUM"] && $arFields["PS_STATUS"] == "Y")
		{
			CSaleOrder::PayOrder($arOrder["ID"], "Y");
		}
	}

	CSaleOrder::Update($ORDER_ID, $arFields);

	if (strlen($strErrorMessage)<=0)
		$bSuccessProcess = True;
}

if ($bSuccessProcess)
{
	?><div class="alert alert-success" role="alert"><?=GetMessage("AN_SUCC")?></div><?
}
else
{
	if (strlen($strErrorMessage)>0)
	{
		?><div class="alert alert-danger" role="alert"><?= $strErrorMessage ?></div><?
	}
	?>
		<form action="" method="post">
			<div class="form-group row">
				<label for="ccardNumber" class="col-sm-6 col-form-label text-sm-right"><?=GetMessage("AN_CC")?></label>
				<div class="col-sm-6">
					<input type="text" id="ccardNumber" name="ccard_num" size="30" value="<?= htmlspecialcharsbx($_REQUEST["ccard_num"]) ?>" class="form-control inputtext">
				</div>
			</div>

			<div class="form-group row">
				<label for="ccardDate1" class="col-sm-6 col-form-label text-sm-right"><?=GetMessage("AN_CC_DATE")?></label>
				<div class="col-auto">
					<select name="ccard_date1" class="inputselect form-control" id="ccardDate1">
						<?for ($i = 1; $i <= 12; $i++):?>
							<option value="<?= $i ?>"<?= (($i==$_REQUEST["ccard_date1"]) ? "selected" : "") ?>><?= $i ?></option>
						<?endfor;?>
					</select>
				</div>
				<div class="col-auto col-form-label">/</div>
				<div class="col-auto">
					<select name="ccard_date2" class="inputselect form-control">
						<?for ($i = $year; $i <= $year+5; $i++):?>
							<option value="<?= $i ?>"<?= (($i==$_REQUEST["ccard_date2"]) ? "selected" : "") ?>><?= $i ?></option>
						<?endfor;?>
					</select>
				</div>
			</div>

			<div class="form-group row">
				<label for="ccardCode" class="col-sm-6 col-form-label text-sm-right"><?=GetMessage("AN_CC_CVV2")?></label>
				<div class="col-auto">
					<input type="text" id="ccardCode" name="ccard_code" size="5" value="<?= htmlspecialcharsbx($_REQUEST["ccard_code"]) ?>" class="inputtext form-control">
				</div>
			</div>

			<div class="form-group row">
				<div class="col-sm-6 col-form-label text-sm-right"></div>
				<div class="col-auto">
					<input type="hidden" name="CurrentStep" value="<?= IntVal($GLOBALS["CurrentStep"]) ?>">
					<input type="hidden" name="ORDER_ID" value="<?= $ORDER_ID ?>">
					<input type="hidden" name="pay_this_order" value="Y">
					<input type="submit" value="<?=GetMessage("AN_CC_BUTTON")?>" class="inputbutton btn btn-primary">
				</div>
			</div>
		</form>
	<?
}
?>