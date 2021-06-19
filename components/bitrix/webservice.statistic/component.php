<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("webservice"))
	return;
if(!CModule::IncludeModule("statistic"))
	return;

class CStatisticWS extends IWebService
{
	function CheckAuth()
	{
		$statRight = $GLOBALS["APPLICATION"]->GetGroupRight("statistic");
		if ($statRight == "D")
		{
			$GLOBALS["USER"]->RequiredHTTPAuthBasic();
			return new CSOAPFault('Server Error', 'Unable to authorize user.');
		}

		return False;
	}

	function UsersOnline()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$dbresult = CUserOnline::GetList($guest_count, $session_count, Array("s_session_time"=>"desc"));
		$result = Array("GUEST_COUNT"=>$guest_count, "SESSIONS"=>Array());
		$i=0;
		while ($ar = $dbresult->Fetch())
		{
			foreach ($ar as &$v) $v = htmlspecialchars($v);

			$strTmp = "";
			$rsUser = CUser::GetByID($ar["LAST_USER_ID"]);
			if ($ar1 = $rsUser->GetNext())
				$strTmp = "[".$ar1["ID"]."] ".$ar1["NAME"]." ".$ar1["LAST_NAME"]." (".$ar1["LOGIN"].") ";
			else
				$strTmp = "[".$ar["LAST_USER_ID"]."]";
			$ar["USER_NAME"] = $strTmp;
			$result["SESSIONS"][($i++).':SESSION'] = $ar;
		}

		return $result;
	}

	function GetCommonValues()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$result = CTraffic::GetCommonValues(array(),true);
		$result["ONLINE_LIST"] = CStatisticWS::UsersOnline();

		return $result;
	}

	function GetAdv()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CAdv::GetList('', '', array("DATE1_PERIOD" => "", "DATE2_PERIOD" => ""), $is_filtered, "", $arrGROUP_DAYS, $v);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => $arAdv["ID"],
				"name" => $arAdv["REFERER1"]."/".$arAdv["REFERER2"],
				"today" => $arAdv["SESSIONS_TODAY"],
				"yesterday" => $arAdv["SESSIONS_YESTERDAY"],
				"bef_yesterday" => $arAdv["SESSIONS_BEF_YESTERDAY"],
				"all" => $arAdv["SESSIONS"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetEvents()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CStatEventType::GetList("s_stat", "desc", array("DATE1_PERIOD" => "", "DATE2_PERIOD" => ""), $is_filtered);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => $arAdv["ID"],
				"name" => $arAdv["EVENT"],
				"today" => $arAdv["TODAY_COUNTER"],
				"yesterday" => $arAdv["YESTERDAY_COUNTER"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_COUNTER"],
				"all" => $arAdv["TOTAL_COUNTER"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetPhrases()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CTraffic::GetPhraseList($s_by, $s_order, array(), $is_filtered, false);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => "0",
				"name" => TruncateText($arAdv["PHRASE"], 50),
				"today" => $arAdv["TODAY_PHRASES"],
				"yesterday" => $arAdv["YESTERDAY_PHRASES"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_PHRASES"],
				"all" => $arAdv["TOTAL_PHRASES"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetRefSites()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CTraffic::GetRefererList('', '', array(), $is_filtered, false);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => "0",
				"name" => $arAdv["SITE_NAME"],
				"today" => $arAdv["TODAY_REFERERS"],
				"yesterday" => $arAdv["YESTERDAY_REFERERS"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_REFERERS"],
				"all" => $arAdv["TOTAL_REFERERS"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetSearchers()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CSearcher::GetList("s_stat", "desc", array("DATE1_PERIOD" => "", "DATE2_PERIOD" => ""));
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => $arAdv["ID"],
				"name" => $arAdv["NAME"],
				"today" => $arAdv["TODAY_HITS"],
				"yesterday" => $arAdv["YESTERDAY_HITS"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_HITS"],
				"all" => $arAdv["TOTAL_HITS"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetLiveFeedData($site_id = "", $lang = "en")
	{
		global $MESS;

		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		CComponentUtil::__IncludeLang("/bitrix/components/bitrix/webservice.statistic/", "/component_1.php", $lang);

		$arFilter = array();
		$server_name = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
		$protocol = (CMain::IsHTTPS() ? "https" : "http");

		if ($site_id <> '')
		{
			$rsSites = CSite::GetByID($arFields["SITE_ID"]);
			if ($arSite = $rsSites->Fetch())
			{
				$arFilter = array("SITE_ID" => $site_id);
				if ($arSite["SERVER_NAME"] <> '')
					$server_name = $arSite["SERVER_NAME"];
			}
		}

		$traffic = CTraffic::GetCommonValues($arFilter, true);

		$strStat = '<table border="0">'.
			'<tr>'.
				'<td> </td>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_TODAY").'</b></td>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_YESTERDAY").'</b></td>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_B_YESTERDAY").'</b></td>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_TOTAL").'</b></td>'.
			'</tr>'.
			'<tr>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_HITS").'</b></td>'.
				'<td align="center">'.$traffic["TODAY_HITS"].'</td>'.
				'<td align="center">'.$traffic["YESTERDAY_HITS"].'</td>'.
				'<td align="center">'.$traffic["B_YESTERDAY_HITS"].'</td>'.
				'<td align="center">'.$traffic["TOTAL_HITS"].'</td>'.
			'</tr>'.
			'<tr>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_SESSIONS").'</b></td>'.
				'<td align="center">'.$traffic["TODAY_SESSIONS"].'</td>'.
				'<td align="center">'.$traffic["YESTERDAY_SESSIONS"].'</td>'.
				'<td align="center">'.$traffic["B_YESTERDAY_SESSIONS"].'</td>'.
				'<td align="center">'.$traffic["TOTAL_SESSIONS"].'</td>'.
			'</tr>'.
			'<tr>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_HOSTS").'</b></td>'.
				'<td align="center">'.$traffic["TODAY_HOSTS"].'</td>'.
				'<td align="center">'.$traffic["YESTERDAY_HOSTS"].'</td>'.
				'<td align="center">'.$traffic["B_YESTERDAY_HOSTS"].'</td>'.
				'<td align="center">'.$traffic["TOTAL_HOSTS"].'</td>'.
			'</tr>'.
			'<tr>'.
				'<td><b>'.GetMessage("BX_WS_STAT_LF_EVENTS").'</b></td>'.
				'<td align="center">'.$traffic["TODAY_EVENTS"].'</td>'.
				'<td align="center">'.$traffic["YESTERDAY_EVENTS"].'</td>'.
				'<td align="center">'.$traffic["B_YESTERDAY_EVENTS"].'</td>'.
				'<td align="center">'.$traffic["TOTAL_EVENTS"].'</td>'.
			'</tr>'.
			(!array_key_exists("SITE_ID", $arFilter)
				?
				'<tr>'.
					'<td><b>'.GetMessage("BX_WS_STAT_LF_GUESTS").'</b></td>'.
					'<td align="center">'.$traffic["TODAY_GUESTS"].'</td>'.
					'<td align="center">'.$traffic["YESTERDAY_GUESTS"].'</td>'.
					'<td align="center">'.$traffic["B_YESTERDAY_GUESTS"].'</td>'.
					'<td align="center">'.$traffic["TOTAL_GUESTS"].'</td>'.
				'</tr>'.
				'<tr>'.
					'<td><b>'.GetMessage("BX_WS_STAT_LF_NEW_GUESTS").'</b></td>'.
					'<td align="center">'.$traffic["TODAY_NEW_GUESTS"].'</td>'.
					'<td align="center">'.$traffic["YESTERDAY_NEW_GUESTS"].'</td>'.
					'<td align="center">'.$traffic["B_YESTERDAY_NEW_GUESTS"].'</td>'.
					'<td align="center"> </td>'.
				'</tr>'
				: ''
			).
			'</table>';

		$strStatText = GetMessage("BX_WS_STAT_LF_HITS").'#BR#'.
				GetMessage("BX_WS_STAT_LF_TODAY").': '.$traffic["TODAY_HITS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_YESTERDAY").': '.$traffic["YESTERDAY_HITS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_B_YESTERDAY").': '.$traffic["B_YESTERDAY_HITS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_TOTAL").': '.$traffic["TOTAL_HITS"].'#BR#'.
			'#BR#'.GetMessage("BX_WS_STAT_LF_SESSIONS").'#BR#'.
				GetMessage("BX_WS_STAT_LF_TODAY").': '.$traffic["TODAY_SESSIONS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_YESTERDAY").': '.$traffic["YESTERDAY_SESSIONS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_B_YESTERDAY").': '.$traffic["B_YESTERDAY_SESSIONS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_TOTAL").': '.$traffic["TOTAL_SESSIONS"].'#BR#'.
			'#BR#'.GetMessage("BX_WS_STAT_LF_HOSTS").'#BR#'.
				GetMessage("BX_WS_STAT_LF_TODAY").': '.$traffic["TODAY_HOSTS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_YESTERDAY").': '.$traffic["YESTERDAY_HOSTS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_B_YESTERDAY").': '.$traffic["B_YESTERDAY_HOSTS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_TOTAL").': '.$traffic["TOTAL_HOSTS"].'#BR#'.
			'#BR#'.GetMessage("BX_WS_STAT_LF_EVENTS").'#BR#'.
				GetMessage("BX_WS_STAT_LF_TODAY").': '.$traffic["TODAY_EVENTS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_YESTERDAY").': '.$traffic["YESTERDAY_EVENTS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_B_YESTERDAY").': '.$traffic["B_YESTERDAY_EVENTS"].'#BR#'.
				GetMessage("BX_WS_STAT_LF_TOTAL").': '.$traffic["TOTAL_EVENTS"].'#BR#'.
			(!array_key_exists("SITE_ID", $arFilter)
				?
				'#BR#'.GetMessage("BX_WS_STAT_LF_GUESTS").'#BR#'.
					GetMessage("BX_WS_STAT_LF_TODAY").': '.$traffic["TODAY_GUESTS"].'#BR#'.
					GetMessage("BX_WS_STAT_LF_YESTERDAY").': '.$traffic["YESTERDAY_GUESTS"].'#BR#'.
					GetMessage("BX_WS_STAT_LF_B_YESTERDAY").': '.$traffic["B_YESTERDAY_GUESTS"].'#BR#'.
					GetMessage("BX_WS_STAT_LF_TOTAL").': '.$traffic["TOTAL_GUESTS"].'#BR#'.
				'#BR#'.GetMessage("BX_WS_STAT_LF_NEW_GUESTS").'#BR#'.
					GetMessage("BX_WS_STAT_LF_TODAY").': '.$traffic["TODAY_NEW_GUESTS"].'#BR#'.
					GetMessage("BX_WS_STAT_LF_YESTERDAY").': '.$traffic["YESTERDAY_NEW_GUESTS"].'#BR#'.
					GetMessage("BX_WS_STAT_LF_B_YESTERDAY").': '.$traffic["B_YESTERDAY_NEW_GUESTS"].'#BR#'
				: ''
			);

		$arResult = array(
			"TITLE" =>  htmlspecialchars(GetMessage("BX_WS_STAT_LF_TITLE")),
			"MESSAGE" => htmlspecialchars($strStat),
			"TEXT_MESSAGE" => htmlspecialchars($strStatText),
			"URL" => htmlspecialchars($protocol."://".$server_name."/bitrix/admin/stat_list.php?lang=".$lang)
		);

		return $arResult;
	}

	public static function GetWebServiceDesc()
	{
		$wsdesc = new CWebServiceDesc();
		$wsdesc->wsname = "bitrix.webservice.statistic";
		$wsdesc->wsclassname = "CStatisticWS";
		$wsdesc->wsdlauto = true;
		$wsdesc->wsendpoint = CWebService::GetDefaultEndpoint();
		$wsdesc->wstargetns = CWebService::GetDefaultTargetNS();

		$wsdesc->classTypes = array();
		$wsdesc->structTypes["Session"] =
			array(
				"ID" => array("varType" => "integer"),
				"ADV_ID" => array("varType" => "integer"),
				"REFERER1" => array("varType" => "string"),
				"REFERER2" => array("varType" => "string"),
				"REFERER3" => array("varType" => "string"),
				"ADV_BACK" => array("varType" => "string"),
				"LAST_SITE_ID" => array("varType" => "string"),
				"URL_LAST" => array("varType" => "string"),
				"URL_LAST_404" => array("varType" => "string"),
				"IP_LAST" => array("varType" => "string"),
				"HITS" => array("varType" => "integer"),
				"USER_AUTH" => array("varType" => "string"),
				"STOP_LIST_ID" => array("varType" => "integer"),
				"GUEST_ID" => array("varType" => "integer"),
				"FAVORITES" => array("varType" => "string"),
				"LAST_USER_ID" => array("varType" => "string"),
				"SESSION_TIME" => array("varType" => "string"),
				"DATE_LAST" => array("varType" => "string"),
				"NEW_GUEST" => array("varType" => "string"),
				"FIRST_URL_FROM" => array("varType" => "string"),
				"FIRST_SITE_ID" => array("varType" => "string"),
				"URL_FROM" => array("varType" => "string"),
				"COUNTRY_ID" => array("varType" => "string"),
				"COUNTRY_NAME" => array("varType" => "string"),
			);

		$wsdesc->structTypes["Top"] =
			array(
				"id" => array("varType" => "int"),
				"name" => array("varType" => "string"),
				"today" => array("varType" => "integer"),
				"yesterday" => array("varType" => "integer"),
				"bef_yesterday" => array("varType" => "integer"),
				"all" => array("varType" => "integer"),
			);

		$wsdesc->structTypes["UsersOnlineList"] = Array(
			"GUEST_COUNT"  => array("varType" => "integer"),
			"SESSION_COUNT"  => array("varType" => "integer"),
			"SESSIONS" => array("varType" => "ArrayOfSession", "arrType"=>"Session")
			);

		$wsdesc->structTypes["CommonValues"] =
			array(
				"TOTAL_HITS" => array("varType" => "integer"),
				"TODAY_HITS" => array("varType" => "integer"),
				"YESTERDAY_HITS" => array("varType" => "integer"),
				"B_YESTERDAY_HITS" => array("varType" => "integer"),
				"TOTAL_SESSIONS" => array("varType" => "integer"),
				"TODAY_SESSIONS" => array("varType" => "integer"),
				"YESTERDAY_SESSIONS" => array("varType" => "integer"),
				"B_YESTERDAY_SESSIONS" => array("varType" => "integer"),
				"TOTAL_EVENTS" => array("varType" => "integer"),
				"TODAY_EVENTS" => array("varType" => "integer"),
				"YESTERDAY_EVENTS" => array("varType" => "integer"),
				"B_YESTERDAY_EVENTS" => array("varType" => "integer"),
				"TOTAL_HOSTS" => array("varType" => "integer"),
				"TODAY_HOSTS" => array("varType" => "integer"),
				"YESTERDAY_HOSTS" => array("varType" => "integer"),
				"B_YESTERDAY_HOSTS" => array("varType" => "integer"),
				"TOTAL_GUESTS" => array("varType" => "integer"),
				"TODAY_GUESTS" => array("varType" => "integer"),
				"YESTERDAY_GUESTS" => array("varType" => "integer"),
				"B_YESTERDAY_GUESTS" => array("varType" => "integer"),
				"TODAY_NEW_GUESTS" => array("varType" => "integer"),
				"YESTERDAY_NEW_GUESTS" => array("varType" => "integer"),
				"B_YESTERDAY_NEW_GUESTS" => array("varType" => "integer"),
				"TOTAL_FAVORITES" => array("varType" => "integer"),
				"TODAY_FAVORITES" => array("varType" => "integer"),
				"YESTERDAY_FAVORITES" => array("varType" => "integer"),
				"B_YESTERDAY_FAVORITES" => array("varType" => "integer"),
				"ONLINE_GUESTS" => array("varType" => "integer"),
				"ONLINE_LIST" => array("varType" => "UsersOnlineList"),
			);

		$wsdesc->structTypes["LiveFeedData"] = Array(
			"TITLE"  => array("varType" => "string"),
			"MESSAGE"  => array("varType" => "string"),
			"TEXT_MESSAGE"  => array("varType" => "string"),
			"URL"  => array("varType" => "string")
		);

		$wsdesc->classes = array(
			"CStatisticWS" => array(
				"UsersOnline" => array(
					"type"		=> "public",
					"name"		=> "UsersOnline",
					"input"		=> array(),
					"output"	=> array(
						"user" => array("varType" => "UsersOnlineList")
					),
				),
				"GetCommonValues" => array(
					"type"		=> "public",
					"name"		=> "GetCommonValues",
					"input"		=> array(),
					"output"	=> array(
						"user" => array("varType" => "CommonValues")
					),
				),
				"GetAdv" => array(
					"type"		=> "public",
					"name"		=> "GetAdv",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "ArrayOfTop", "arrType"=>"Top")
					),
				),
				"GetEvents" => array(
					"type"		=> "public",
					"name"		=> "GetEvents",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "ArrayOfTop", "arrType"=>"Top")
					),
				),
				"GetPhrases" => array(
					"type"		=> "public",
					"name"		=> "GetPhrases",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "ArrayOfTop", "arrType"=>"Top")
					),
				),
				"GetRefSites" => array(
					"type"		=> "public",
					"name"		=> "GetRefSites",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "ArrayOfTop", "arrType"=>"Top")
					),
				),
				"GetSearchers" => array(
					"type"		=> "public",
					"name"		=> "GetSearchers",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "ArrayOfTop", "arrType"=>"Top")
					),
				),
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

$arParams["WEBSERVICE_NAME"] = "bitrix.webservice.statistic";
$arParams["WEBSERVICE_CLASS"] = "CStatisticWS";
$arParams["WEBSERVICE_MODULE"] = "";

$APPLICATION->IncludeComponent(
	"bitrix:webservice.server",
	"",
	$arParams
);

die();
?>