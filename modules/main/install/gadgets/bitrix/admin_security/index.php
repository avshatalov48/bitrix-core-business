<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @global CMain $APPLICATION */
global $APPLICATION;
/** @global CUser $USER */
global $USER;

\Bitrix\Main\UI\Extension::load('ui.design-tokens');
$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/admin_security/styles.css');

$aGlobalOpt = CUserOptions::GetOption("global", "settings", array());
$bShowSecurity = (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/index.php") && ($aGlobalOpt['messages']['security'] ?? 'Y') <> 'N');

if (!$bShowSecurity)
	return false;

$bSecModuleInstalled = CModule::IncludeModule("security");

if($bSecModuleInstalled){
	$bSecurityFilter = CSecurityFilter::IsActive();
	if($bSecurityFilter){
		$lamp_class = " bx-gadgets-info";
		$text2_class = "green";
		$securityEventsCount = CSecurityFilter::GetEventsCount();
		if($securityEventsCount > 0){
			$text2 = GetMessage("GD_SECURITY_EVENT_COUNT");
		} else {
			$text2 = GetMessage("GD_SECURITY_EVENT_COUNT_EMPTY");
		}
		if($securityEventsCount > 999){
			$securityEventsCount = round($securityEventsCount/1000,1).'K';
		}
	} else {
		$lamp_class = " bx-gadgets-note";
		$text2_class = "red";
		$text2 = GetMessage("GD_SECURITY_FILTER_OFF_DESC");
		$securityEventsCount = 0;
	}
	$minSecurityVersionForScan = "12.5.0";
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/version.php");

	if(CheckVersion($arModuleVersion['VERSION'], $minSecurityVersionForScan)) {
		$lastResult = CSecuritySiteChecker::getLastTestingInfo();
		$isScanNeeded = CSecuritySiteChecker::isNewTestNeeded();

		$scannerMessage = "";
		$isShowScanButton = true;
	} else {
		$isScanNeeded = false;
		$isShowScanButton = false;
		$scannerMessage = GetMessage("GD_SECURITY_UPDATE_NEEDED", array("#MIN_VERSION#" => $minSecurityVersionForScan));
	}
} else {
	$lamp_class = "";
	$text2_class = "red";
	$text2 = GetMessage("GD_SECURITY_MODULE");
	$isScanNeeded = false;
	$scannerMessage = "";
	$bSecurityFilter = false;
	$securityEventsCount = 0;
}
if(!$bSecurityFilter || $isScanNeeded || !isset($lastResult["results"]) || !empty($lastResult["results"])) {
	$shieldClassColor = "red";
} else {
	$shieldClassColor = "blue";
}
$eventLogUrl = '/bitrix/admin/event_log.php?set_filter=Y&find_type=audit_type_id&find_audit_type[]=SECURITY_VIRUS&find_audit_type[]=SECURITY_FILTER_SQL&find_audit_type[]=SECURITY_FILTER_XSS&find_audit_type[]=SECURITY_FILTER_XSS2&find_audit_type[]=SECURITY_FILTER_PHP&find_audit_type[]=SECURITY_REDIRECT&find_audit_type[]=SECURITY_HOST_RESTRICTION&mod=security&lang='.LANGUAGE_ID;
$securityScannerUrl = '/bitrix/admin/security_scanner.php?lang='.LANGUAGE_ID;
?><table class="bx-gadgets-content-layout"><?
	?><tr><?
		?><td><div class="bx-gadgets-title"><?=GetMessage("GD_SECURITY_SCANNER_TITLE")?></div></td><?
		?><td><div class="bx-gadgets-title2">Web Application<br>Firewall</div></td><?
	?></tr><?
	?><tr class="bx-gadget-bottom-cont<?=((!$bSecModuleInstalled && $USER->CanDoOperation('edit_other_settings')) || ($bSecModuleInstalled && $APPLICATION->GetGroupRight("security") >= "W") ? " bx-gadget-bottom-button-cont" : "")?>"><?

		if (!$bSecModuleInstalled && $USER->CanDoOperation('edit_other_settings'))
		{
			?><td class="bx-gadgets-colourful-cell"><?
				?><a class="bx-gadget-button bx-gadget-button-clickable" href="/bitrix/admin/module_admin.php?id=security&install=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>">
					<div class="bx-gadget-button-lamp"></div>
					<div class="bx-gadget-button-text"><?=GetMessage("GD_SECURITY_MODULE_INSTALL")?></div>
				</a><?
			?></td><?
			?><td class="bx-gadgets-colourful-cell"><?
			?></td><?
		}
		elseif ($bSecModuleInstalled && $APPLICATION->GetGroupRight("security") >= "W")
		{
			?><td class="bx-gadgets-colourful-cell"><?
			if($isShowScanButton) {
			?>
				<a class="bx-gadget-button bx-gadget-button-clickable<?=(!$isScanNeeded ? " bx-gadget-button-active" : "")?>"
				   href="<?=($securityEventsCount ? $eventLogUrl : $securityScannerUrl)?>">
					<div class="bx-gadget-button-lamp"></div>
					<div class="bx-gadget-button-text"><?=($securityEventsCount ? GetMessage("GD_SECURITY_SCANNER_VIEW") : GetMessage("GD_SECURITY_SCANNER_RUN"))?></div>
				</a><?
			} else {
				?><div class="bx-gadget-desc"><?=$scannerMessage?></div><?
			}
			?></td><?
			?><td class="bx-gadgets-colourful-cell"><?
				if ($bSecurityFilter && $securityEventsCount > 0)
				{
					?><div class="bx-gadget-events"><?=$securityEventsCount?></div><?
				}
				?><div class="bx-gadget-desc"><?=$text2?></div><?
			?></td><?
	}

?></tr>
</table>
<div class="bx-gadget-shield bx-gadget-shield-<?=$shieldClassColor?>"></div>