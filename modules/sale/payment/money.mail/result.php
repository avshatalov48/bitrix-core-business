<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/money_mail.php"));


$ORDER_ID = intval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
$CURRENCY = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"];
$invoice_number = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PAY_VOUCHER_NUM"];
$access_key = rawurlencode((CSalePaySystemAction::GetParamValue("KEY") <> '') ? CSalePaySystemAction::GetParamValue("KEY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["KEY"]);
$status_description=Array(
		"PAID"=>GetMessage('MM_PAID'),
		"NEW"=>GetMessage('MM_NEW'),
		"EXPIRED"=>GetMessage('MM_EXPIRED'),
		"DELIVERED"=>GetMessage('MM_DELIVERED'),
		"REJECTED"=>GetMessage('MM_REJECTED')
	);
set_time_limit(0);

$sHost = "merchant.money.mail.ru";
$sUrl = "/api/invoice/item/";
$sVars ="key=".$access_key."&invoice_number=".$invoice_number;
$sResult = QueryGetData($sHost, 443, $sUrl, $sVars, $errno, $errstr, "GET", "ssl://");
if ($sResult)
{
	parse_str(str_replace(Array("\r\n","\n","\r"), "&", 'success='.$sResult), $aFields);
	if ($aFields['success'] != 'OK') 
		return false;
		
	$arFields = array(
			"PS_STATUS" => (($aFields['status']=='PAID')?"Y":"N"),
			"PS_STATUS_CODE" => $aFields['status'],
			"PS_STATUS_MESSAGE" => base64_decode(str_replace(' ', '+', $aFields["reason"])),
			"PS_STATUS_DESCRIPTION" => $status_description[$aFields['status']],
			"PS_SUM" => DoubleVal(str_replace('RUR', '', $aFields["paid_total"])),
			"PS_CURRENCY" => $CURRENCY,
			"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
		);
	if (ToUpper(SITE_CHARSET) != ToUpper('windows-1251'))
		$arFields["PS_STATUS_MESSAGE"] = $APPLICATION->ConvertCharset($arFields["PS_STATUS_MESSAGE"], 'windows-1251', SITE_CHARSET);

	$arOrder = CSaleOrder::GetByID($ORDER_ID);
	if ($arOrder["PRICE"] == $arFields["PS_SUM"] && $arFields["PS_STATUS"] == "Y")
		CSaleOrder::PayOrder($arOrder["ID"], "Y");
	CSaleOrder::Update($ORDER_ID, $arFields);
	return true;
}
return false;
?>