<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("webservice"))
	return;
if(!CModule::IncludeModule("sale"))
	return;

class CSaleWS extends IWebService
{
	function CheckAuth()
	{
		$saleRight = $GLOBALS["APPLICATION"]->GetGroupRight("sale");
		if ($saleRight == "D")
		{
			$GLOBALS["USER"]->RequiredHTTPAuthBasic();
			return new CSOAPFault('Server Error', 'Unable to authorize user.');
		}

		return False;
	}

	function GetLiveFeedData($site_id = "", $lang = "en")
	{
		global $MESS;
		
		if (($r = CSaleWS::CheckAuth()) !== False)
			return $r;
			
		$saleRight = $GLOBALS["APPLICATION"]->GetGroupRight("sale");

		CComponentUtil::__IncludeLang("/bitrix/components/bitrix/webservice.sale/", "/component_1.php", $lang);

		$arFilter = array();
		$server_name = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
		$protocol = (CMain::IsHTTPS() ? "https" : "http");

		if ($site_id <> '')
		{
			$rsSites = CSite::GetByID($arFields["SITE_ID"]);
			if ($arSite = $rsSites->Fetch())
			{
				$arFilterLID = array("LID" => $site_id);
				if ($arSite["SERVER_NAME"] <> '')
					$server_name = $arSite["SERVER_NAME"];
			}
			$strSiteCurrency = CSaleLang::GetLangCurrency($site_id);			
		}
		else
		{
			$arFilterLID = array();
			$strSiteCurrency = CCurrency::GetBaseCurrency();
		}

		if ($saleRight != "W")
			$arFilterPerms = array(
				"STATUS_PERMS_GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray(),
				">=STATUS_PERMS_PERM_VIEW" => "Y"
			);
		else
			$arFilterPerms = array();

		$d = date("w");
		if($d < 1)
			$d = -6;
		elseif($d > 1)
			$d = $d-1;
		else
			$d = 0;

		$BeforeLastWeek_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".(14+$d)), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
		$BeforeLastWeek_maxDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".(7+$d), "SS" => -1),  mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

		$LastWeek_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".(7+$d)), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
		$LastWeek_maxDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".$d, "SS" => -1),  mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

		$ThisWeek_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".$d),  mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
		$ThisWeek_maxDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j")+1, date("Y")), "FULL");

		$BeforeYesterday_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-2"), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
		$BeforeYesterday_maxDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-1", "SS" => -1), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

		$Yesterday_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-1"), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
		$Yesterday_maxDate = ConvertTimeStamp(AddToTimeStamp(array("SS" => -1), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

		$Today_minDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j"), date("Y")), "FULL");
		$Today_maxDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j")+1, date("Y")), "FULL");

		$arDatePeriods = array(
			"BEFORE_LAST_WEEK" => array(
				"MIN_DATE" => $BeforeLastWeek_minDate,
				"MAX_DATE" => $BeforeLastWeek_maxDate
			),
			"LAST_WEEK" => array(
				"MIN_DATE" => $LastWeek_minDate,
				"MAX_DATE" => $LastWeek_maxDate
			),
			"THIS_WEEK" => array(
				"MIN_DATE" => $ThisWeek_minDate,
				"MAX_DATE" => $ThisWeek_maxDate
			),
			"BEFORE_YESTERDAY" => array(
				"MIN_DATE" => $BeforeYesterday_minDate,
				"MAX_DATE" => $BeforeYesterday_maxDate
			),
			"YESTERDAY" => array(
				"MIN_DATE" => $Yesterday_minDate,
				"MAX_DATE" => $Yesterday_maxDate
			),
			"TODAY" => array(
				"MIN_DATE" => $Today_minDate,
				"MAX_DATE" => $Today_maxDate
			)	
		);
		
		$arStatus1 = array(
			"CREATED" => array(
				"NAME" => GetMessage("BX_WS_SALE_STATUS_1_CREATED"),
				"DB_FIELD" => "DATE"
			),
			"PAID" => array(
				"NAME" => GetMessage("BX_WS_SALE_STATUS_1_PAID"),
				"DB_FIELD" => "DATE_PAYED"
			),
			"CANCELED" => array(
				"NAME" => GetMessage("BX_WS_SALE_STATUS_1_CANCELED"),
				"DB_FIELD" => "DATE_UPDATE",
				"Y_FIELD" => "CANCELED"
			),
			"ALLOW_DELIVERY" =>array(
				"NAME" => GetMessage("BX_WS_SALE_STATUS_1_ALLOW_DELIVERY"),
				"DB_FIELD" => "DATE_UPDATE",
				"Y_FIELD" => "ALLOW_DELIVERY"
			)
		);

		foreach($arDatePeriods as $key => $arPeriod)
		{
			foreach($arStatus1 as $status_code => $arStatus)
			{
				$arOrderStats[$key][$status_code] = 0;
				$arOrderStats[$key]["PRICE_".$status_code] = 0;
			}

			foreach($arStatus1 as $status_code => $arStatus)
			{
				if (
					!is_array($arGadgetParams["ORDERS_STATUS_1"])
					|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
				)
				{
					if (array_key_exists("Y_FIELD", $arStatus) && $arStatus["Y_FIELD"] <> '')
						$arFilterYField = array($arStatus["Y_FIELD"] => "Y");
					else
						$arFilterYField = array();

					$arFilter = array_merge(
						array(
							$arStatus["DB_FIELD"]."_FROM"=> $arPeriod["MIN_DATE"],
							$arStatus["DB_FIELD"]."_TO" => $arPeriod["MAX_DATE"]
						),
						$arFilterLID,
						$arFilterPerms,
						$arFilterYField
					);

					$dbOrder = CSaleOrder::GetList(Array(), $arFilter, array("SUM" => "PRICE", "COUNT" => "ID"));
					if($arOrder = $dbOrder->Fetch())
					{
						$arOrderStats[$key][$status_code] = $arOrder["ID"];
						$arOrderStats[$key]["PRICE_".$status_code] = $arOrder["PRICE"];
					}
				}
			}
		}

		$strSaleStat = '<table border="0">';
		$strSaleStat .= '<tr><td>&nbsp;</td>';
		foreach($arStatus1 as $status_code => $arStatus)
			$strSaleStat .= '<td><b>'.$arStatus["NAME"].'</b></td>';
		$strSaleStat .= '</tr>';
		$strSaleStat .= '<tr><td>'.GetMessage("BX_WS_SALE_TODAY").'</td>';
		foreach($arStatus1 as $status_code => $arStatus)
			$strSaleStat .= '<td align="center">'.intval($arOrderStats["TODAY"][$status_code]).'</td>';
		$strSaleStat .= '</tr>';
		$strSaleStat .= '<tr><td>'.GetMessage("BX_WS_SALE_YESTERDAY").'</td>';
		foreach($arStatus1 as $status_code => $arStatus)
			$strSaleStat .= '<td align="center">'.intval($arOrderStats["YESTERDAY"][$status_code]).'</td>';			
		$strSaleStat .= '</tr>';
		$strSaleStat .= '<tr><td>'.GetMessage("BX_WS_SALE_BEFOREYESTERDAY").'</td>';
		foreach($arStatus1 as $status_code => $arStatus)
			$strSaleStat .= '<td align="center">'.intval($arOrderStats["BEFORE_YESTERDAY"][$status_code]).'</td>';		
		$strSaleStat .= '</tr>';
		$strSaleStat .= '<tr><td>'.GetMessage("BX_WS_SALE_THISWEEK").'</td>';
		foreach($arStatus1 as $status_code => $arStatus)
			$strSaleStat .= '<td align="center">'.intval($arOrderStats["THIS_WEEK"][$status_code]).'</td>';		
		$strSaleStat .= '</tr>';
		$strSaleStat .= '<tr><td>'.GetMessage("BX_WS_SALE_LASTWEEK").'</td>';
		foreach($arStatus1 as $status_code => $arStatus)
			$strSaleStat .= '<td align="center">'.intval($arOrderStats["LAST_WEEK"][$status_code]).'</td>';
		$strSaleStat .= '</tr>';
		$strSaleStat .= '<tr><td>'.GetMessage("BX_WS_SALE_BEFORELASTWEEK").'</td>';
		foreach($arStatus1 as $status_code => $arStatus)
			$strSaleStat .= '<td align="center">'.intval($arOrderStats["BEFORE_LAST_WEEK"][$status_code]).'</td>';	
		$strSaleStat .= '</tr>';
		$strSaleStat .= '</table>';
		
		$strSaleStatText = '';
		foreach($arStatus1 as $status_code => $arStatus)
		{
			$strSaleStatText .= '#BR#'.$arStatus["NAME"].'#BR#';
			$strSaleStatText .= GetMessage("BX_WS_SALE_TODAY").' '.intval($arOrderStats["TODAY"][$status_code]).(intval($arOrderStats["TODAY"][$status_code]) > 0 ? ' ('.CurrencyFormat($arOrderStats["TODAY"]["PRICE_".$status_code], $strSiteCurrency).')' : '').'#BR#';
			$strSaleStatText .= GetMessage("BX_WS_SALE_YESTERDAY").' '.intval($arOrderStats["YESTERDAY"][$status_code]).(intval($arOrderStats["YESTERDAY"][$status_code]) > 0 ? ' ('.CurrencyFormat($arOrderStats["YESTERDAY"]["PRICE_".$status_code], $strSiteCurrency).')' : '').'#BR#';
			$strSaleStatText .= GetMessage("BX_WS_SALE_BEFOREYESTERDAY").' '.intval($arOrderStats["BEFORE_YESTERDAY"][$status_code]).(intval($arOrderStats["BEFORE_YESTERDAY"][$status_code]) > 0 ? ' ('.CurrencyFormat($arOrderStats["BEFORE_YESTERDAY"]["PRICE_".$status_code], $strSiteCurrency).')' : '').'#BR#';
			$strSaleStatText .= GetMessage("BX_WS_SALE_THISWEEK").' '.intval($arOrderStats["THIS_WEEK"][$status_code]).(intval($arOrderStats["THIS_WEEK"][$status_code]) > 0 ? ' ('.CurrencyFormat($arOrderStats["THIS_WEEK"]["PRICE_".$status_code], $strSiteCurrency).')' : '').'#BR#';
			$strSaleStatText .= GetMessage("BX_WS_SALE_LASTWEEK").' '.intval($arOrderStats["LAST_WEEK"][$status_code]).(intval($arOrderStats["LAST_WEEK"][$status_code]) > 0 ? ' ('.CurrencyFormat($arOrderStats["LAST_WEEK"]["PRICE_".$status_code], $strSiteCurrency).')' : '').'#BR#';
			$strSaleStatText .= GetMessage("BX_WS_SALE_BEFORELASTWEEK").' '.intval($arOrderStats["BEFORE_LAST_WEEK"][$status_code]).(intval($arOrderStats["BEFORE_LAST_WEEK"][$status_code]) > 0 ? ' ('.CurrencyFormat($arOrderStats["BEFORE_LAST_WEEK"]["PRICE_".$status_code], $strSiteCurrency).')' : '').'#BR#';
		}

		$arResult = array(
			"TITLE" =>  htmlspecialchars(GetMessage("BX_WS_SALE_LF_TITLE")),
			"MESSAGE" => htmlspecialchars($strSaleStat),
			"TEXT_MESSAGE" => htmlspecialchars($strSaleStatText),
			"URL" => htmlspecialchars($protocol."://".$server_name."/bitrix/admin/sale_stat.php?lang=".$lang)
		);

		return $arResult;
	}

	public static function GetWebServiceDesc()
	{
		$wsdesc = new CWebServiceDesc();
		$wsdesc->wsname = "bitrix.webservice.sale";
		$wsdesc->wsclassname = "CSaleWS";
		$wsdesc->wsdlauto = true;
		$wsdesc->wsendpoint = CWebService::GetDefaultEndpoint();
		$wsdesc->wstargetns = CWebService::GetDefaultTargetNS();

		$wsdesc->classTypes = array();

		$wsdesc->structTypes["LiveFeedData"] = Array(
			"TITLE"  => array("varType" => "string"),
			"MESSAGE"  => array("varType" => "string"),
			"TEXT_MESSAGE"  => array("varType" => "string"),
			"URL"  => array("varType" => "string")
		);

		$wsdesc->classes = array(
			"CSaleWS" => array(
				"GetLiveFeedData" => array(
					"type" => "public",
					"name" => "GetLiveFeedData",
					"input" => array(
						"site_id" => array("varType" => "string", "strict" => "no"),
						"lang" => array("varType" => "string", "strict" => "no")
					),
					"output" => array(
						"livefeeddata" => array("varType" => "LiveFeedData")
					)
				)
			)
		);
		return $wsdesc;
	}
}

$arParams["WEBSERVICE_NAME"] = "bitrix.webservice.sale";
$arParams["WEBSERVICE_CLASS"] = "CSaleWS";
$arParams["WEBSERVICE_MODULE"] = "";

$APPLICATION->IncludeComponent(
	"bitrix:webservice.server",
	"",
	$arParams
);

die();
?>