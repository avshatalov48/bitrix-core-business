<?
define("ADMIN_MODULE_NAME", "bitrixcloud");
if (isset($_REQUEST["referer"]) && $_REQUEST["referer"] === "monitoring")
	define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

/* @global CMain $APPLICATION */
/* @global CUser $USER */

if (!CModule::IncludeModule("bitrixcloud"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if ($_REQUEST["referer"] === "monitoring")
{
	CBitrixCloudMonitoringResult::setExpirationTime(0);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
}

if (!$USER->CanDoOperation("bitrixcloud_monitoring"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$strError = "";
$arNotes = array();
$monitoringResults = null;
$APPLICATION->SetTitle(GetMessage("BCL_MONITORING_TITLE"));
$converter = CBXPunycode::GetConverter();
$monitoring = CBitrixCloudMonitoring::getInstance();

$sTableID = "t_bitrixcloud_monitoring";
$lAdmin = new CAdminList($sTableID);

$arHeaders = array(
	array(
		"id" => "DOMAIN",
		"content" => GetMessage("BCL_MONITORING_DOMAIN"),
		"default" => true,
	),
	array(
		"id" => "RESULT",
		"content" => GetMessage("BCL_MONITORING_RESULT"),
		"default" => true,
	),
);

try
{
	if($arID = $lAdmin->GroupAction())
	{
		foreach($arID as $ID)
		{
			if(strlen($ID)<=0)
				continue;
			switch($_REQUEST['action'])
			{
				case "delete":
					$strError = $monitoring->stopMonitoring($ID);
					if ($strError !== "")
						$lAdmin->AddUpdateError($strError, $ID);
					break;
			}
		}
	}

	$monitoringResults = $monitoring->getMonitoringResults();
	if (is_string($monitoringResults))
	{
		throw new CBitrixCloudException($monitoringResults);
	}

	if ($_REQUEST["referer"] === "gadget")
	{
		$monitoringAlertsCurrent = $monitoring->getAlertsCurrentResult();
		$monitoringAlertsStored = $monitoring->getAlertsStored();
		if ($monitoringAlertsStored != $monitoringAlertsCurrent)
		{
			$monitoring->storeAlertsCurrentResult();
		}
	}
}
catch (Exception $e)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$strError = $e->getMessage();
}

$lAdmin->AddHeaders($arHeaders);
$rsData = new CDBResult;
$arResult = array();
if (is_object($monitoringResults))
{
	foreach($monitoringResults as $domainName => $tmp)
	{	$arResult[] = array(
			"DOMAIN" => $domainName,
		);
	}
}
$rsData->InitFromArray($arResult);
$rsData = new CAdminResult($rsData, $sTableID);

while($arRes = $rsData->GetNext())
{
	$row = $lAdmin->AddRow($arRes["DOMAIN"], $arRes);
	$isOK = true;
	/** @var CBitrixCloudMonitoringDomainResult $domainResults */
	$domainResults = $monitoringResults[$arRes["DOMAIN"]];
	$html = '<table width="100%">';
	/** @var CBitrixCloudMonitoringTest $test_http_response_time */
	$test_http_response_time = $domainResults["test_http_response_time"];
	if ($test_http_response_time)
	{
		if ($test_http_response_time->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
		{
			$isOK = false;
			$indicatorStyle = 'style="color:red"';
		}
		else
		{
			$indicatorStyle = '';
		}

		$result = explode("/", $test_http_response_time->getUptime());
		if ($result[0] > 0 && $result[1] > 0)
			$resultText = round($result[0]/$result[1]*100, 2)."%";
		else
			$resultText = GetMessage("BCL_MONITORING_NO_DATA");

		$html .= '<tr>';
		$html .= '<td width="50%" align="right">'.GetMessage("BCL_MONITORING_RESPONSE_TIME").':</td>';
		$html .= '<td align="left" '.$indicatorStyle.'>'.$resultText.'</td>';
		$html .= '</tr>';

		if ($result[1] > 0)
		{
			$failTime = ($result[1] - $result[0]);
			$resultText = FormatDate(array(
				"s" => "sdiff",
				"i" => "idiff",
				"H" => "Hdiff",
			), time() - $failTime);
			$html .= '<tr>';
			$html .= '<td width="50%" align="right">'.GetMessage("BCL_MONITORING_FAILED_PERIOD").'</td>';
			if ($failTime > 0)
				$html .= '<td align="left" style="color:red">'.$resultText.'</td>';
			else
				$html .= '<td align="left">'.GetMessage("MAIN_NO").'</td>';
			$html .= '</tr>';

			$resultText = FormatDate(array(
				"s" => "sdiff",
				"i" => "idiff",
				"H" => "Hdiff",
				"-" => "ddiff",
			), time() - $result[1]);
			$html .= '<tr>';
			$html .= '<td width="50%" align="right">'.GetMessage("BCL_MONITORING_PERIOD").'</td>';
			$html .= '<td align="left">'.$resultText.'</td>';
			$html .= '</tr>';
		}
	}
	/** @var CBitrixCloudMonitoringTest $test_domain_registration */
	$test_domain_registration = $domainResults["test_domain_registration"];
	if ($test_domain_registration)
	{
		if ($test_domain_registration->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
		{
			$isOK = false;
			$indicatorStyle = 'style="color:red"';
		}
		else
		{
			$indicatorStyle = '';
		}

		$result = $test_domain_registration->getResult();
		if ($result === "n/a")
		{
			$c = array_search(GetMessage("BCL_MONITORING_DOMAIN_REGISTRATION_NOTE"), $arNotes);
			if ($c === false)
			{
				$c = count($arNotes);
				$arNotes[] = GetMessage("BCL_MONITORING_DOMAIN_REGISTRATION_NOTE");
			}
			$resultText = GetMessage("BCL_MONITORING_NO_DATA_AVAILABLE").'<span class="required"><sup>'.($c + 1).'</sup></span>';
		}
		elseif ($result === "-" || $result < 1)
		{
			$resultText = GetMessage("BCL_MONITORING_NO_DATA");
		}
		else
		{
			$resultText = FormatDate("ddiff", time(), $result)." (".FormatDate("SHORT", $result).")";
		}

		$html .= '<tr>';
		$html .= '<td width="50%" align="right">'.GetMessage("BCL_MONITORING_DOMAIN_REGISTRATION").'</td>';
		$html .= '<td align="left" '.$indicatorStyle.'>'.$resultText.'</td>';
		$html .= '</tr>';
	}
	/** @var CBitrixCloudMonitoringTest $test_lic */
	$test_lic = $domainResults["test_lic"];
	if ($test_lic)
	{
		if ($test_lic->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
		{
			$isOK = false;
			$indicatorStyle = 'style="color:red"';
		}
		else
		{
			$indicatorStyle = '';
		}

		$result = $test_lic->getResult();
		if ($result === "-" || $result < 1)
			$resultText = GetMessage("BCL_MONITORING_NO_DATA");
		else
			$resultText = FormatDate("ddiff", time(), $result)." (".FormatDate("SHORT", $result).")";

		$html .= '<tr>';
		$html .= '<td width="50%" align="right">'.GetMessage("BCL_MONITORING_LICENSE").'</td>';
		$html .= '<td align="left" '.$indicatorStyle.'>'.$resultText.'</td>';
		$html .= '</tr>';
	}
	/** @var CBitrixCloudMonitoringTest $test_ssl_cert_validity */
	$test_ssl_cert_validity = $domainResults["test_ssl_cert_validity"];
	if ($test_ssl_cert_validity)
	{
		if ($test_ssl_cert_validity->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
		{
			$isOK = false;
			$indicatorStyle = 'style="color:red"';
		}
		else
		{
			$indicatorStyle = '';
		}

		$result = $test_ssl_cert_validity->getResult();
		if ($result === "-" || $result < 1)
			$resultText = GetMessage("BCL_MONITORING_NO_DATA");
		else
			$resultText = FormatDate("ddiff", time(), $result)." (".FormatDate("SHORT", $result).")";

		$html .= '<tr>';
		$html .= '<td width="50%" align="right">'.GetMessage("BCL_MONITORING_SSL").'</td>';
		$html .= '<td align="left" '.$indicatorStyle.'>'.$resultText.'</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
	$row->AddViewField("RESULT", $html);

	$lamp = '<span class="adm-lamp adm-lamp-in-list adm-lamp-'.($isOK? "green": "red").'"></span>';
	$row->AddViewField("DOMAIN", $lamp." ".$converter->Decode($arRes["DOMAIN"]));

	$arActions = array(
		array(
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => GetMessage("BCL_MONITORING_EDIT"),
			"ACTION" => $lAdmin->ActionRedirect("bitrixcloud_monitoring_edit.php?domain=".urlencode($arRes["DOMAIN"])),
		),
		array(
			"SEPARATOR" => "Y",
		),
		array(
			"ICON" => "delete",
			"TEXT" => GetMessage("BCL_MONITORING_DELETE"),
			"ACTION" => "if(confirm('".GetMessage("BCL_MONITORING_DELETE_CONF")."')) ".$lAdmin->ActionDoGroup($arRes["DOMAIN"], "delete"),
		),
	);
	$row->AddActions($arActions);
}

$localDomains = $monitoring->getConfiguredDomains();

if(empty($arResult) && empty($localDomains))
{
	$strError = GetMessage("BCL_MONITORING_NO_DOMAINS_CONFIGURED");
}

foreach ($arResult as $arRes)
{
	unset($localDomains[$arRes["DOMAIN"]]);
}

if (!empty($localDomains))
{
	$aContext = array();
	foreach ($localDomains as $punyName => $domainName)
	{
		$aContext[] = array(
			"TEXT" => htmlspecialcharsEx($domainName),
			"LINK" => "bitrixcloud_monitoring_edit.php?lang=".LANGUAGE_ID."&domain=".urlencode($punyName),
			"TITLE" => "",
		);
	}
	$aContext = array(
		array(
			"TEXT" => GetMessage("BCL_MONITORING_START"),
			"ICON" => "btn_new",
			"TITLE" => "",
			"MENU" => $aContext,
		),
	);
	$lAdmin->AddAdminContextMenu($aContext, /*$bShowExcel=*/false);
}

$lAdmin->CheckListMode();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if ($strError)
	CAdminMessage::ShowMessage($strError);

$lAdmin->DisplayList();
if (!empty($arNotes))
{
	echo BeginNote();
	foreach ($arNotes as $i => $note)
		echo '<span class="required"><sup>'.($i+1).'</sup></span>', $note, '<br>';
	echo EndNote();
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
