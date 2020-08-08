<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
set_time_limit(0);

$issuer_email = CSalePaySystemAction::GetParamValue("ShopEmail");
$pass = CSalePaySystemAction::GetParamValue("PASS");
$ORDER_ID = intval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);


$sHost = "www.moneymail.ru";
$sUrl = "/";
$sVars = "check_bill:issuer_email=".$issuer_email."&invoice_number=&issuer_id=".$ORDER_ID."&hash=".md5($pass.$issuer_email.$ORDER_ID);

$sResult = QueryGetData($sHost, 443, $sUrl, $sVars, $errno, $errstr, "GET", "ssl://");

if($sResult <> "")
{
	$aResult = explode("\n", $sResult);
	if($aResult[0] == "OK")
	{
		$bWasSuccess = false;
		foreach($aResult as $val)
		{
			if($val == "OK")
			{
				//new result within results set 
				//only success result is nedded
				if($bWasSuccess == true)
					break;
				$aFields = array();
			}
			$aVal = explode("=", $val);
			if(count($aVal) == 2)
			{
				$aFields[$aVal[0]] = $aVal[1];
				if($aVal[0] == "status" && $aFields["status"] == "PAID")
					$bWasSuccess = true;
			}
		}

		if(intval($aFields["issuer_id"]) == $ORDER_ID)
		{
			$str_PS_STATUS_CODE = $aFields["status"];
			$str_PS_STATUS = ($aFields["status"] == "PAID"? "Y":"N");
			if($aFields["status"] == "PAID")
				$str_PS_STATUS_DESCRIPTION = "Счет оплачен";
			elseif($aFields["status"] == "NEW")
				$str_PS_STATUS_DESCRIPTION = "Новый счет";
			elseif($aFields["status"] == "REJECTED")
				$str_PS_STATUS_DESCRIPTION = "Отказ от оплаты счета";
			elseif($aFields["status"] == "EXPIRED")
				$str_PS_STATUS_DESCRIPTION = "Истек срок оплаты счета";
			else
				$str_PS_STATUS_DESCRIPTION = "";
			$str_PS_STATUS_MESSAGE = "";
			$str_PS_SUM = doubleval(mb_substr($aFields["value"], 3));
			$str_PS_CURRENCY = mb_substr($aFields["value"], 0, 3);
			if($aFields["status"] == "PAID")
				$str_PS_DATE_STATUS = mb_substr($aFields["paid_date"], 0, 19);
			else
				$str_PS_DATE_STATUS = "";
			$str_PS_RESPONSE_FULL = $sResult;

			$str_PS_RESPONSE_FORMATTED = 
				'<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableborder">'."\n".
				'<tr><td><table width="100%" border="0" cellspacing="1" cellpadding="3">'."\n";
			foreach($aFields as $key=>$val)
				$str_PS_RESPONSE_FORMATTED .= '<tr valign="top" class="tablebody"><td><font class="tablebodytext">'.htmlspecialcharsbx($key).'</font></td><td><font class="tablebodytext">'.htmlspecialcharsbx($val).'</font></td></tr>'."\n";
			$str_PS_RESPONSE_FORMATTED .= '</table></td></tr></table>'."\n";

			$arFields = array(
					"PS_STATUS" => $str_PS_STATUS,
					"PS_STATUS_CODE" => $str_PS_STATUS_CODE,
					"PS_STATUS_DESCRIPTION" => $str_PS_STATUS_DESCRIPTION,
					"PS_STATUS_MESSAGE" => $str_PS_STATUS_MESSAGE,
					"PS_SUM" => $str_PS_SUM,
					"PS_CURRENCY" => $str_PS_CURRENCY,
					"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
				);

			CSaleOrder::Update($ORDER_ID, $arFields);

			return true;
		}
	}
}
return false;
?>