<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("calendar"))
	return;

$adv_mode = ($arCurrentValues["ADVANCED_MODE_SETTINGS"] == 'Y');
$eventListMode = ($arCurrentValues["EVENT_LIST_MODE"] == 'Y');
$bSocNet = CModule::IncludeModule("socialnetwork");

if($bSocNet)
	$bSocNet = class_exists('CSocNetUserToGroup') && CBXFeatures::IsFeatureEnabled("Calendar");

$arTypes = array();
$types = CCalendarType::GetList();
if (is_array($types))
{
	foreach($types as $type)
		$arTypes[$type["XML_ID"]] = "[".$type["XML_ID"]."] ".$type["NAME"];
}

// * * * * * * * * * * * *  Groups * * * * * * * * * * * *
$arComponentParameters = array();
// $arComponentParameters["GROUPS"] = array(
	// "BASE_SETTINGS" => array("NAME" => GetMessage("EC_GROUP_BASE_SETTINGS"), "SORT" => "100")
// );

//* * * * * * * * * * * Parameters  * * * * * * * * * * *
$arComponentParameters["PARAMETERS"] = [];
?>
