<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/assist.php"));

$assist_Shop_IDP = CSalePaySystemAction::GetParamValue("SHOP_IDP");
$assist_LOGIN = CSalePaySystemAction::GetParamValue("SHOP_LOGIN");
$assist_PASSWORD = CSalePaySystemAction::GetParamValue("SHOP_PASSWORD");
$password = CSalePaySystemAction::GetParamValue("SHOP_SECRET_WORLD");

$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);

set_time_limit(0);

$sHost = "payments.paysecure.ru";
$sUrl = "/orderstate/orderstate.cfm";
$dtm = AddToTimeStamp(Array("MM" => -1), false);
$sVars = "Ordernumber=".$ORDER_ID."&Merchant_ID=".$assist_Shop_IDP."&login=".$assist_LOGIN."&password=".$assist_PASSWORD."&FORMAT=3&StartYear=".date('Y', $dtm)."&StartMonth=".date('n', $dtm)."&StartYDay=".date('j', $dtm)."";

$aDesc = array(
	"In Process" => array(GetMessage("SASP_IP"), GetMessage("SASPD_IP")),
	"Delayed" => array(GetMessage("SASP_D"), GetMessage("SASPD_D")),
	"Approved" => array(GetMessage("SASP_A"), GetMessage("SASPD_A")),
	"PartialApproved" => array(GetMessage("SASP_PA"), GetMessage("SASPD_PA")),
	"PartialDelayed" => array(GetMessage("SASP_PD"), GetMessage("SASPD_PD")),
	"Canceled" => array(GetMessage("SASP_C"), GetMessage("SASPD_C")),
	"PartialCanceled" => array(GetMessage("SASP_PC"), GetMessage("SASPD_PC")),
	"Declined" => array(GetMessage("SASP_DEC"), GetMessage("SASPD_DEC")),
	"Timeout" => array(GetMessage("SASP_T"), GetMessage("SASPD_T")),
);

$sResult = QueryGetData($sHost, 80, $sUrl, $sVars, $errno, $errstr, "POST");
if ($sResult <> "")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
	$objXML = new CDataXML();
	$objXML->LoadString($sResult);
	$arResult = $objXML->GetArray();
	if (count($arResult)>0 && $arResult["result"]["@"]["firstcode"] == "0")
	{
		$aRes = $arResult["result"]["#"]["order"][0]["#"];
		if (IntVal($aRes["ordernumber"][0]["#"]) == $ORDER_ID)
		{
			$arFields = Array();
			$check = ToUpper(md5(toUpper(md5($password).md5($assist_Shop_IDP.$aRes["ordernumber"][0]["#"].$aRes["orderamount"][0]["#"].$aRes["ordercurrency"][0]["#"].$aRes["orderstate"][0]["#"]))));
			if($aRes["checkvalue"][0]["#"] == $check)
			{
				$arOrder = CSaleOrder::GetByID($ORDER_ID);
				$arFields = array(
						"PS_STATUS" => ($aRes["orderstate"][0]["#"] == "Approved"?"Y":"N"),
						"PS_STATUS_CODE" => substr($aRes["orderstate"][0]["#"],0,5),
						"PS_STATUS_DESCRIPTION" => $aDesc[$aRes["orderstate"][0]["#"]][0],
						"PS_STATUS_MESSAGE" => $aDesc[$aRes["orderstate"][0]["#"]][1],
						"PS_SUM" => DoubleVal($aRes["orderamount"][0]["#"]),
						"PS_CURRENCY" => $aRes["ordercurrency"][0]["#"],
						"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
					);

				if ($arOrder["PAYED"] != "Y" && CSalePaySystemAction::GetParamValue("AUTOPAY") == "Y" && $arFields["PS_STATUS"] == "Y" && Doubleval($arOrder["PRICE"]) == DoubleVal($arFields["PS_SUM"]))
				{
					CSaleOrder::PayOrder($arOrder["ID"], "Y");
				}
			}
			if(!empty($arFields))
				CSaleOrder::Update($ORDER_ID, $arFields);

			return true;
		}
	}
}

return false;
?>