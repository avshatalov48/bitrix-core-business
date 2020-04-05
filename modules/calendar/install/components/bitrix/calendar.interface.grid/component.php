<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("calendar"))
	return ShowError("EC_CALENDAR_MODULE_NOT_INSTALLED");

CModule::IncludeModule("socialnetwork");

//$APPLICATION->ResetException();
//$APPLICATION->SetPageProperty("BodyClass", trim($APPLICATION->GetPageProperty("BodyClass")." no-paddings"));

//$arParams["FILTER_ID"] = "CALENDAR_GRID_FILTER_".$arParams["CALENDAR_TYPE"]."_".$arParams["OWNER_ID"]."_".CCalendar::GetCurUserId();

$arParams["USER_ID"] = CCalendar::GetCurUserId();
$arParams["SHOW_TOP_VIEW_SWITCHER"] = isset($arParams["SHOW_TOP_VIEW_SWITCHER"]) ? $arParams["SHOW_TOP_VIEW_SWITCHER"] : true;
$arParams["SHOW_SECTION_SELECTOR"] = isset($arParams["SHOW_SECTION_SELECTOR"]) ? $arParams["SHOW_SECTION_SELECTOR"] : true;

//if (isset($arParams["SIDEBAR_DOM_ID"]))
//	$params['SectionControlsDOMId'] = $arParams["SIDEBAR_DOM_ID"];

// Create new instance of Event Calendar object
//$EC = new CCalendar;
//$EC->Init($params);
//$arResult['ID'] = $EC->GetId();
//$arResult['CALENDAR'] = $EC;

//$request = \Bitrix\Main\Context::getCurrent()->getRequest();
//if (isset($request['action']))
//{
//	$arResult['IFRAME'] = $request['IFRAME'] == 'Y';
//	CCalendarRequest::Process($request['action'], $EC);
//}
//else
//{
	$this->IncludeComponentTemplate();
//}
?>