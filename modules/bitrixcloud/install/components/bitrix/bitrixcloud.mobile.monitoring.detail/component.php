<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if (!$USER->CanDoOperation("bitrixcloud_monitoring"))
{
	ShowError(GetMessage("BCLMMD_ACCESS_DENIED"));
	return;
}
$arResult = array();
$arResult["DOMAIN"] = isset($_REQUEST["domain"]) ? $_REQUEST["domain"] : '';
$arResult["AJAX_PATH"] = $componentPath."/ajax.php";

if($arResult["DOMAIN"] === '')
{
	if(isset($arParams["LIST_URL"]))
	{
		LocalRedirect($arParams["LIST_URL"]);
	}
	else
	{
		echo GetMessage("BCLMMD_NO_DATA");
		return;
	}
}

if (!CModule::IncludeModule('bitrixcloud'))
{
	ShowError(GetMessage("BCLMMD_BC_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage("BCLMMD_MA_NOT_INSTALLED"));
	return;
}

CJSCore::Init("ajax");
CUtil::InitJSCore(array("mobile_monitoring"));

$monitoring = CBitrixCloudMonitoring::getInstance();
$monitoringResults = $monitoring->getMonitoringResults();

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

$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPage();

$arData = array();
$bProblem = false;

$converter = CBXPunycode::GetConverter();
$arResult["DOMAIN_DECODED"] = $converter->Decode($arResult["DOMAIN"]);
$domainResults = $monitoringResults[$arResult["DOMAIN_DECODED"]];

$test_http_response_time = $domainResults["test_http_response_time"];
if ($test_http_response_time)
{
	if ($test_http_response_time->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
		$arData["HTTP_RESPONSE_TIME"]["PROBLEM"] = $bProblem = true;

	$result = explode("/", $test_http_response_time->getUptime());

	if ($result[0] > 0 && $result[1] > 0)
		$resultText = round($result[0]/$result[1]*100, 2)."%";
	else
		$resultText = GetMessage("BCLMMD_MONITORING_NO_DATA");

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
		$resultText = GetMessage("BCLMMD_MONITORING_NO_DATA_AVAILABLE");
	elseif ($result === "-" || $result < 1)
		$resultText = GetMessage("BCLMMD_MONITORING_NO_DATA");
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
		$resultText = GetMessage("BCLMMD_MONITORING_NO_DATA");
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
		$resultText = GetMessage("BCLMMD_MONITORING_NO_DATA");
	else
		$resultText = FormatDate("ddiff", time(), $result)." (".FormatDate("SHORT", $result).")";

	$arData["MONITORING_SSL"]["DATA"] = $resultText;
}

if($bProblem)
	$arData["PROBLEM"] = true;

$arResult["DATA"] = $arData;

$this->IncludeComponentTemplate();
?>
