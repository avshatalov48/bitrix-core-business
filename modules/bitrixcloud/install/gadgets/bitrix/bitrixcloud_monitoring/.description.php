<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bAlertColored = false;
if (CModule::IncludeModule('bitrixcloud'))
{
	$monitoring = CBitrixCloudMonitoring::getInstance();
	$monitoringResults = $monitoring->getMonitoringResults();
	if (!is_string($monitoringResults))
	{
		if ($monitoringResults->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
		{
			$monitoringAlertsCurrent = $monitoring->getAlertsCurrentResult();
			$monitoringAlertsStored = $monitoring->getAlertsStored();
			if ($monitoringAlertsStored != $monitoringAlertsCurrent)
				$bAlertColored = true;
		}
	}
}

$arDescription = Array(
		"NAME" => GetMessage("GD_BITRIXCLOUD_MONITOR_NAME"),
		"DESCRIPTION" => GetMessage("GD_BITRIXCLOUD_MONITOR_DESC"),
		"ICON" => "",
		"TITLE_ICON_CLASS" => $bAlertColored? "bx-gadgets-inspector bx-gadgets-inspector-alert": "bx-gadgets-inspector",
		"GROUP" => array("ID"=>"admin_settings"),
		"NOPARAMS" => "Y",
		"AI_ONLY" => true,
		"COLOURFUL" => true
	);
?>
