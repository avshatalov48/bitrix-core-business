<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage("SMOP_SALE_NOT_INSTALLED"));
	return;
}

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

$arStatusList = false;
$arFilter = array("LID" => LANG, "ID" => "N");
$arGroupByTmpSt = false;

$arUserGroups = $USER->GetUserGroupArray();
$userId = intval($USER->GetID());

if ($saleModulePermissions < "W")
{
	$arFilter["GROUP_ID"] = $arUserGroups;
	$arFilter["PERM_VIEW"] = "Y";
	$arGroupByTmpSt = array("ID", "NAME", "MAX" => "PERM_VIEW");
}
$dbStatusList = CSaleStatus::GetList(
	array(),
	$arFilter,
	$arGroupByTmpSt,
	false,
	array("ID", "NAME")
);
$arStatusList = $dbStatusList->Fetch();

if ($saleModulePermissions == "D" OR ($saleModulePermissions < "W" AND $arStatusList["PERM_VIEW"] != "Y"))
{
	ShowError(GetMessage("SMOP_ACCESS_DENIED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage("SMOP_MA_NOT_INSTALLED"));
	return;
}

$arResult = array(
	"CURRENT_PAGE" => $APPLICATION->GetCurPage(),
	"AJAX_URL" => $componentPath."/ajax.php",
	"EVENTS" => array()
);

$arEvents = CSaleMobileOrderPush::getEvents();
$arSubscribedEvents = CSaleMobileOrderPush::getSubscriptions($userId);
$bSubscribedEarlier = !empty($arSubscribedEvents) ? true : false;
$subscribedAll = 'Y';

foreach ($arEvents as $eventId)
{
	if($bSubscribedEarlier)
	{
		if(isset($arSubscribedEvents[$eventId]) && $arSubscribedEvents[$eventId] == 'Y')
			$subscribed = true;
		else
			$subscribed = false;

		if($subscribedAll == 'Y' && !$subscribed)
			$subscribedAll = 'N';
	}
	else
	{
		$subscribed = true;
	}

	$msg = GetMessage("SMOP_EVNT_".$eventId);

	if(strlen($msg) > 0)
		$arResult["EVENTS"][$eventId] = array(
			"TITLE" => $msg,
			"SUBSCRIBED" => $subscribed
		);
}

$arResult["SUBSCRIBED_ALL"] = $subscribedAll;

CJSCore::Init('ajax');

$this->IncludeComponentTemplate();
?>