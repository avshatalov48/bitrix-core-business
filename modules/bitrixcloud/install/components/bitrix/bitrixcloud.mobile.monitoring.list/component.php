<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if (!$USER->CanDoOperation("bitrixcloud_monitoring"))
{
	ShowError(GetMessage("BCLMMSL_ACCESS_DENIED"));
	return;
}

if (!CModule::IncludeModule('bitrixcloud'))
{
	ShowError(GetMessage("BCLMMSL_BC_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage("BCLMMSL_MA_NOT_INSTALLED"));
	return;
}

$monitoring = CBitrixCloudMonitoring::getInstance();
$monitoringResults = $monitoring->getMonitoringResults();
$localDomains = $monitoring->getConfiguredDomains();

try
{
	if (is_string($monitoringResults))
		throw new CBitrixCloudException($monitoringResults);
}
catch (Exception $e)
{
	ShowError($e->getMessage());
	return;
}

CUtil::InitJSCore(array("mobile_monitoring"));

$arResult = array(
	"CURRENT_PAGE" => $APPLICATION->GetCurPage()
);

$intervalLang = array(
	"sale" => array(
		7 => GetMessage("BCLMMSL_MONITORING_MESS_ALERT1_WEEK"),
		30 => GetMessage("BCLMMSL_MONITORING_MESS_ALERT1_MONTH"),
		90 => GetMessage("BCLMMSL_MONITORING_MESS_ALERT1_QUARTER"),
		365 => GetMessage("BCLMMSL_MONITORING_MESS_ALERT1_YEAR"),
	)
);

if ($monitoringResults->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
{
	$arResult["HAVE_PROBLEM"] = true;
	$sum = 0;
	$sumHtml = "";
	$uptimeRate = 1;
	$alertIntervalText = "";

	$uptime = $monitoring->getWorstUptime("test_http_response_time");
	if ($uptime !== "")
	{
		$uptime = explode("/", $uptime);
		if ($uptime[0] > 0 && $uptime[1] > 0)
			$uptimeRate = $uptime[0]/$uptime[1];
	}

	if ($uptimeRate < 1 && CModule::IncludeModule('sale'))
	{
		$base = CCurrency::GetBaseCurrency();
		$r = CSaleOrder::GetList(array(), array(
			">=DATE_INSERT" => ConvertTimeStamp(time() - $monitoring->getInterval()*24*3400, "SHORT"),
		), array("LID", "CURRENCY", "SUM" => "PRICE"));

		while ($a = $r->fetch())
			$sum += CCurrencyRates::ConvertCurrency($a["PRICE"], $a["CURRENCY"], $base);

		$sum *= (1 - $uptimeRate);

		if ($sum < 0.0)
			$sumHtml = number_format((1 - $uptimeRate)*100, 2, '.', ' ')."%";
		else
			$sumHtml = CurrencyFormat($sum, $base);
	}
	else
	{
		$sumHtml = number_format((1 - $uptimeRate)*100, 2, '.', ' ')."%";
	}

	$arResult["LOST_SUMM"] = $sumHtml;
	$arResult["COUNT_INTERVAL"] = $intervalLang["sale"][$monitoring->getInterval()];

}
else
	$arResult["HAVE_PROBLEM"] = false;

foreach($monitoringResults as $domainName => $tmp)
{
	$arData = array();
	$domainResults = $monitoringResults[$domainName];
	$bProblem = false;

	$test_http_response_time = $domainResults["test_http_response_time"];
	if ($test_http_response_time)
	{
		if ($test_http_response_time->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
			$arData["HTTP_RESPONSE_TIME"]["PROBLEM"] = $bProblem = true;

		$result = explode("/", $test_http_response_time->getUptime());

		if ($result[0] > 0 && $result[1] > 0)
			$resultText = round($result[0]/$result[1]*100, 2)."%";
		else
			$resultText = GetMessage("BCLMMSL_MONITORING_NO_DATA");

		$arData["HTTP_RESPONSE_TIME"]["DATA"] = $resultText;

		if ($result[1] > 0)
		{
			$failTime = ($result[1] - $result[0]);

			if ($failTime > 0)
			{
				$resultText = FormatDate(array(
					"s" => "sdiff",
					"i" => "idiff",
					"H" => "Hdiff",
				), time() - $failTime);

				$arData["FAILED_PERIOD"]["PROBLEM"] = true;
			}
			else
				$resultText = GetMessage("MAIN_NO");

			$arData["FAILED_PERIOD"]["DATA"] = $resultText;


			$resultText = FormatDate(array(
				"s" => "sdiff",
				"i" => "idiff",
				"H" => "Hdiff",
				"-" => "ddiff",
			), time() - $result[1]);

			$arData["MONITORING_PERIOD"]["DATA"] = $resultText;
		}
	}

	$test_domain_registration = $domainResults["test_domain_registration"];

	if ($test_domain_registration)
	{
		if ($test_domain_registration->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
			$arData["DOMAIN_REGISTRATION"]["PROBLEM"] = $bProblem = true;

		$result = $test_domain_registration->getResult();

		if ($result === "n/a")
			$resultText = GetMessage("BCLMMSL_MONITORING_NO_DATA_AVAILABLE");
		elseif ($result === "-" || $result < 1)
			$resultText = GetMessage("BCLMMSL_MONITORING_NO_DATA");
		else
			$resultText = FormatDate("ddiff", time(), $result)." (".FormatDate("SHORT", $result).")";

		$arData["DOMAIN_REGISTRATION"]["DATA"] = $resultText;
	}

	$test_lic = $domainResults["test_lic"];
	if ($test_lic)
	{
		if ($test_lic->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
			$arData["LICENSE"]["PROBLEM"] = $bProblem = true;

		$result = $test_lic->getResult();
		if ($result === "-" || $result < 1)
			$resultText = GetMessage("BCLMMSL_MONITORING_NO_DATA");
		else
			$resultText = FormatDate("ddiff", time(), $result)." (".FormatDate("SHORT", $result).")";

		$arData["LICENSE"]["DATA"] = $resultText;
	}

	$test_ssl_cert_validity = $domainResults["test_ssl_cert_validity"];

	if ($test_ssl_cert_validity)
	{
		if ($test_ssl_cert_validity->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
			$arData["MONITORING_SSL"]["PROBLEM"] = $bProblem = true;

		$result = $test_ssl_cert_validity->getResult();

		if ($result === "-" || $result < 1)
			$resultText = GetMessage("BCLMMSL_MONITORING_NO_DATA");
		else
			$resultText = FormatDate("ddiff", time(), $result)." (".FormatDate("SHORT", $result).")";

		$arData["MONITORING_SSL"]["DATA"] = $resultText;
	}

	if($bProblem)
		$arData["PROBLEM"] = true;

	if(isset($arParams["DETAIL_URL"]))
	{
		$arData['DETAIL_LINK'] = CHTTP::urlAddParams(
			$arParams["DETAIL_URL"],
			array("domain"=>urlencode($domainName))
		);
	}


	$arResult["ITEMS"][$domainName] = $arData;

	unset($localDomains[$domainName]);
}

if(count($arResult["ITEMS"]) <= 0 && isset($arParams["LIST_URL"]))
	LocalRedirect($arParams["LIST_URL"]);

$arResult["DOMAINS_TO_ADD"] = $localDomains;

$this->IncludeComponentTemplate();
?>
