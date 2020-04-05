<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include_once(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));
include(dirname(__FILE__)."/common.php");

$strPaySysError = "";

if ($bDoPayAction)
{
	$INPUT_CARD_NUM = Trim($_REQUEST["ccard_num"]);
	if (!isset($INPUT_CARD_NUM) || strlen($INPUT_CARD_NUM) <= 0)
		$strPaySysError .= GetMessage("AN_CC_NUM")."<br />";

	$INPUT_CARD_NUM = preg_replace("/[\D]+/", "", $INPUT_CARD_NUM);
	if (strlen($INPUT_CARD_NUM) <= 0)
		$strPaySysError .= GetMessage("AN_CC_NUM")."<br />";

	$INPUT_CARD_EXP_MONTH = IntVal($_REQUEST["ccard_date1"]);
	if ($INPUT_CARD_EXP_MONTH < 1 || $INPUT_CARD_EXP_MONTH > 12)
		$strPaySysError .= GetMessage("AN_CC_MONTH")."<br />";
	elseif (strlen($INPUT_CARD_EXP_MONTH) < 2)
		$INPUT_CARD_EXP_MONTH = "0".$INPUT_CARD_EXP_MONTH;

	$INPUT_CARD_EXP_YEAR = IntVal($_REQUEST["ccard_date2"]);
	if ($INPUT_CARD_EXP_YEAR < 2005)
		$strPaySysError .= GetMessage("AN_CC_YEAR")."<br />";

	$INPUT_CARD_CODE = Trim($_REQUEST["ccard_code"]);

	if (strlen($strPaySysError) <= 0)
	{
		$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);

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
		//echo $strPostQueryString;

		$strResult = QueryGetData("secure.authorize.net", 443, "/gateway/transact.dll", $strPostQueryString, $errno, $errstr, "POST", "ssl://");

		$mass = explode("|,|", "|,".$strResult);

		$strHashValue = CSalePaySystemAction::GetParamValue("HASH_VALUE");
		if (strlen($strHashValue) > 0)
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
		if ($strPS_STATUS == "Y")
			$strPS_STATUS_DESCRIPTION = "Approval Code: ".$mass[5].(!empty($mass[7]) ? "; Transaction ID: ".$mass[7] : "");
		else
		{
			$strPS_STATUS_DESCRIPTION = (IntVal($mass[1])==2 ? "Declined" : "Error").": ".$mass[4]." (Reason Code ".$mass[3]." / Sub ".$mass[2].")";
			$strPaySysError .= (IntVal($mass[1])==2 ? "Transaction was declined" : "Error while processing transaction").": ".$mass[4]." (".$mass[3]."/".$mass[2].")";
		}

		$strPS_STATUS_MESSAGE = "";
		if (!empty($mass[6]))
			$strPS_STATUS_MESSAGE .= "\nAVS Result: [".$mass[6]."] ".$arAVSErr[$mass[6]].";";

		if (!empty($mass[39]))
			$strPS_STATUS_MESSAGE .= "\nCard Code Result: [".$mass[39]."] ".$arCVVErr[$mass[39]].";";

		if (!empty($mass[40]))
			$strPS_STATUS_MESSAGE .= "\nCAVV: [".$mass[40]."] ".$arCAVVErr[$mass[40]].";";

		$strPS_SUM = $mass[10];

		$arPaySysResult = array(
				"PS_STATUS" => $strPS_STATUS,
				"PS_STATUS_CODE" => $strPS_STATUS_CODE,
				"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
				"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
				"PS_SUM" => $strPS_SUM,
				"PS_CURRENCY" => $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
				"USER_CARD_TYPE" => false,
				"USER_CARD_NUM" => $INPUT_CARD_NUM,
				"USER_CARD_EXP_MONTH" => $INPUT_CARD_EXP_MONTH,
				"USER_CARD_EXP_YEAR" => $INPUT_CARD_EXP_YEAR,
				"USER_CARD_CODE" => $INPUT_CARD_CODE
			);
	}
}
else
{
	?>
	<table border="0" cellpadding="3" cellspacing="0" width="100%">
		<tr>
			<td align="right" width="40%" class="tablebody">
				<font class="tablebodytext">
				<?=GetMessage("AN_CC")?>
				</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" class="inputtext" name="ccard_num" size="30" value="<?= htmlspecialcharsbx($_REQUEST["ccard_num"]) ?>">
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">
				<?=GetMessage("AN_CC_DATE")?>
				</font>
			</td>
			<td class="tablebody" width="60%">
				<select name="ccard_date1" class="inputselect">
					<?for ($i = 1; $i <= 12; $i++):?>
						<option value="<?= $i ?>"<?= (($i==$_REQUEST["ccard_date1"]) ? "selected" : "") ?>><?= $i ?></option>
					<?endfor;?>
				</select>
				/
				<select name="ccard_date2" class="inputselect">
					<?for ($i = 2005; $i <= 2010; $i++):?>
						<option value="<?= $i ?>"<?= (($i==$_REQUEST["ccard_date2"]) ? "selected" : "") ?>><?= $i ?></option>
					<?endfor;?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">
				<?=GetMessage("AN_CC_CVV2")?>
				</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" class="inputtext" name="ccard_code" size="5" value="<?= htmlspecialcharsbx($_REQUEST["ccard_code"]) ?>">
			</td>
		</tr>
	</table>
	<?
}
?>