<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(dirname(__FILE__)."/common.php");

$strPaySysError = "";

if ($bDoPayAction)
{
	$PF_HOST = CSalePaySystemAction::GetParamValue("PAYFLOW_URL");
	$PF_PORT = CSalePaySystemAction::GetParamValue("PAYFLOW_PORT");
	$PF_USER = CSalePaySystemAction::GetParamValue("PAYFLOW_USER");
	$PF_PWD = CSalePaySystemAction::GetParamValue("PAYFLOW_PASSWORD");
	$PF_PARTNER = CSalePaySystemAction::GetParamValue("PAYFLOW_PARTNER");
	$strExePath = CSalePaySystemAction::GetParamValue("PAYFLOW_EXE_PATH");
	$PFPRO_CERT_PATH = CSalePaySystemAction::GetParamValue("PAYFLOW_CERT_PATH");

	$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);

	$cardnum = Trim($_REQUEST["cardnum"]);
	if (!isset($cardnum) || strlen($cardnum) <= 0)
		$strPaySysError .= "Please enter valid credit card number".". ";

	$cardnum = preg_replace("/[\D]+/", "", $cardnum);
	if (strlen($cardnum) <= 0)
		$strPaySysError .= "Please enter valid credit card number".". ";

	$cvv2 = Trim($_REQUEST["cvv2"]);
	if (!isset($cvv2) || strlen($cvv2) <= 0)
		$strPaySysError .= "Please enter valid credit card CVC2".". ";

	$cardexp1 = IntVal($_REQUEST["cardexp1"]);
	if ($cardexp1 < 1 || $cardexp1 > 12)
		$strPaySysError .= "Please enter valid credit card expiration month".". ";
	elseif (strlen($cardexp1) < 2)
		$cardexp1 = "0".$cardexp1;

	$cardexp2 = IntVal($_REQUEST["cardexp2"]);
	if ($cardexp2 < 5 || $cardexp2 > 50)
		$strPaySysError .= "Please enter valid credit card expiration year".". ";
	elseif (strlen($cardexp2) < 2)
		$cardexp2 = "0".$cardexp2;

	$noc = Trim($_REQUEST["noc"]);
	if (strlen($noc) <= 0)
		$strPaySysError.= "Please enter valid cardholder name".". ";

	$address1 = Trim($_REQUEST["address1"]);
	if (strlen($address1) <= 0)
		$strPaySysError.= "Please enter valid cardholder address".". ";

	$zipcode = Trim($_REQUEST["zipcode"]);
	if (strlen($zipcode) <= 0)
		$strPaySysError.= "Please enter valid cardholder zip".". ";

	if (strlen($strPaySysError) <= 0)
	{
		$ret_var = "";

		$AMT = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
		if ($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"] != "USD")
		{
			$AMT = CCurrencyRates::ConvertCurrency($AMT, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], "USD");

			$additor = 1;
			for ($i = 0; $i < SALE_VALUE_PRECISION; $i++)
				$additor = $additor / 10;

			$AMT_tmp = round($AMT, SALE_VALUE_PRECISION);
			while ($AMT_tmp < $AMT)
				$AMT_tmp = round($AMT_tmp + $additor, SALE_VALUE_PRECISION);

			$AMT = $AMT_tmp;
		}

		$AMT = str_replace(",", ".", $AMT);
		$cardExp = $cardexp1.$cardexp2;

		$parms  = "ACCT=".urlencode($cardnum);	// Credit card number
		$parms .= "&CVV2=".urlencode($cvv2);		// CVV2
		$parms .= "&AMT=".urlencode($AMT);						// Amount (US Dollars)
		$parms .= "&EXPDATE=".urlencode($cardExp);			// Expiration date
		$parms .= "&PARTNER=".urlencode($PF_PARTNER);		// Partner
		$parms .= "&PWD=".urlencode($PF_PWD);					// Password
		$parms .= "&TENDER=C";						// ...
		$parms .= "&TRXTYPE=S";						// Kind of transaction: Sale
		$parms .= "&USER=".urlencode($PF_USER);				// Login ID
		$parms .= "&VENDOR=".urlencode($PF_USER);			// Vendor ID
		$parms .= "&ZIP=".urlencode($zipcode);	// Zip
		$parms .= "&STREET=".urlencode($address1);	// Address
		$parms .= "&COMMENT1=".$ORDER_ID;
		$parms .= "&COMMENT2=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]);

		$ret_com = "$strExePath $PF_HOST $PF_PORT \"$parms\" 30";

		putenv("PFPRO_CERT_PATH=".$PFPRO_CERT_PATH);

		exec($ret_com, $arOutput, $ret_var);

		$strOutput = $arOutput[0];
		parse_str($strOutput, $arResult);

		if (is_array($arResult) && strlen($arResult["RESULT"])>0)
		{
			$arPaySysResult = array(
					"PS_STATUS" => (($arResult["RESULT"] == 0) ? "Y" : "N"),
					"PS_STATUS_CODE" => $arResult["RESULT"],
					"PS_STATUS_DESCRIPTION" => $arResult["RESPMSG"]." - ".$arResult["PREFPSMSG"],
					"PS_STATUS_MESSAGE" => $arResult["PNREF"],
					"PS_SUM" => $AMT,
					"PS_CURRENCY" => "USD",
					"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
					"USER_CARD_TYPE" => false,
					"USER_CARD_NUM" => $cardnum,
					"USER_CARD_EXP_MONTH" => $cardexp1,
					"USER_CARD_EXP_YEAR" => "20".$cardexp2,
					"USER_CARD_CODE" => $cvv2
				);

			$arResult["RESULT"] = IntVal($arResult["RESULT"]);
			if ($arResult["RESULT"] != 0)
			{
				if ($arResult["RESULT"] < 0)
					$strPaySysError .= "Communication Error: [".$arResult["RESULT"]."] ".$arResult["RESPMSG"]." - ".$arResult["PREFPSMSG"].". ";
				elseif ($arPaySysRes_tmp["RESULT"] == 125)
					$strPaySysError .= "Your payment is declined by Fraud Service. Please contact us to make payment".". ";
				elseif ($arResult["RESULT"] == 126)
					$strPaySysWarning .= "Your payment is under review by Fraud Service. We contact you in 48 hours to get more specific information".". ";
				elseif (is_set($arErrorCodes, $arResult["RESULT"]))
					$strPaySysError .= $arErrorCodes[$arResult["RESULT"]].". ";
				else
					$strPaySysError .= "Unknown error".". ";
			}
		}
		else
			$strPaySysError .= "Response error".". ";

/*
		$arPaySysResult = array(
				"PS_STATUS" => "Y",
				"PS_STATUS_CODE" => "AA35",
				"PS_STATUS_DESCRIPTION" => "Good test",
				"PS_STATUS_MESSAGE" => "Yes!!!",
				"PS_SUM" => $AMT,
				"PS_CURRENCY" => "USD",
				"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
				"USER_CARD_TYPE" => false,
				"USER_CARD_NUM" => $cardnum,
				"USER_CARD_EXP_MONTH" => $cardexp1,
				"USER_CARD_EXP_YEAR" => "20".$cardexp2,
				"USER_CARD_CODE" => $cvv2
			);
		$strPaySysError = "";
*/
	}
}
else
{
	$noc_def = CSalePaySystemAction::GetParamValue("NOC");
	$address1_def = CSalePaySystemAction::GetParamValue("ADDRESS");
	$zipcode_def = CSalePaySystemAction::GetParamValue("ZIP");
	?>
	<table border="0" width="100%" cellpadding="2" cellspacing="2">
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Credit Card Number</font>
			</td>
			<td class="tablebody" width="60%">
				<input class="inputtext" type="text" name="cardnum" value="<?= htmlspecialcharsbx($_REQUEST["cardnum"]) ?>" size="35">
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">CVV2</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" class="inputtext" name="cvv2" value="<?= htmlspecialcharsbx($_REQUEST["cvv2"]) ?>" size="5">
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Expiration Date&nbsp;&nbsp;(MM/YY)</font>
			</td>
			<td class="tablebody" width="60%">
				<select name="cardexp1" class="inputselect">
					<option value=""> </option>
					<?
					for ($i = 1; $i <= 12; $i++)
					{
						$val = (($i < 10) ? "0" : "").$i;
						?>
						<option value="<?= $val ?>" <?if ($_REQUEST["cardexp1"] == $val) echo "selected";?>><?= $val ?></option>
						<?
					}
					?>
				</select>
				<select name="cardexp2" class="inputselect">
					<option value=""> </option>
					<?
					for ($i = 4; $i <= 11; $i++)
					{
						$val = (($i < 10) ? "0" : "").$i;
						?>
						<option value="<?= $val ?>" <?if ($_REQUEST["cardexp2"] == $val) echo "selected";?>><?= $val ?></option>
						<?
					}
					?>
				</SELECT>
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Cardholder</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" class="inputtext" size="40" name="noc" value="<?= (strlen($_REQUEST["noc"]) > 0) ? htmlspecialcharsbx($_REQUEST["noc"]) : $noc_def ?>">
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Address</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" class="inputtext" size="40" name="address1" value="<?= (strlen($_REQUEST["address1"]) > 0) ? htmlspecialcharsbx($_REQUEST["address1"]) : $address1_def ?>">
			</td>
		</tr>
		<tr>
			<td align="right" class="tablebody" width="40%">
				<font class="tablebodytext">Zip</font>
			</td>
			<td class="tablebody" width="60%">
				<input type="text" class="inputtext" size="7" name="zipcode" value="<?= (strlen($_REQUEST["zipcode"]) > 0) ? htmlspecialcharsbx($_REQUEST["zipcode"]) : $zipcode_def ?>">
			</td>
		</tr>
	</table>
	<?
}
?>