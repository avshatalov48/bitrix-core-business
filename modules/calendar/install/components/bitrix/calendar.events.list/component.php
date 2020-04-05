<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["CALENDAR_TYPE"] = $arParams["CALENDAR_TYPE"];
$arParams['B_CUR_USER_LIST'] = $arParams['B_CUR_USER_LIST'] == 'Y';
$arParams["FUTURE_MONTH_COUNT"] = intVal($arParams["FUTURE_MONTH_COUNT"]);
if ($arParams["FUTURE_MONTH_COUNT"] <= 0)
	$arParams["FUTURE_MONTH_COUNT"] = 1;

$curUserId = $USER->IsAuthorized() ? $USER->GetID() : '';

if(!CModule::IncludeModule("calendar") || !class_exists("CCalendar"))
	return ShowError(GetMessage("EC_CALENDAR_MODULE_NOT_INSTALLED"));

// Limits
if (strlen($arParams["INIT_DATE"]) > 0 && strpos($arParams["INIT_DATE"], '.') !== false)
	$ts = CCalendar::Timestamp($arParams["INIT_DATE"]);
else
	$ts = time();

$fromLimit = CCalendar::Date($ts, false);
$ts = CCalendar::Timestamp($fromLimit);
$toLimit = CCalendar::Date(mktime(0, 0, 0, date("m", $ts) + $arParams["FUTURE_MONTH_COUNT"], date("d", $ts), date("Y", $ts)), false);

$arResult['ITEMS'] = array();
$arEvents = CCalendar::GetNearestEventsList(
	array(
		'bCurUserList' => $arParams['B_CUR_USER_LIST'],
		'fromLimit' => $fromLimit,
		'toLimit' => $toLimit,
		'type' => $arParams['CALENDAR_TYPE'],
		'sectionId' => $arParams['CALENDAR_SECTION_ID']
	));

if ($arEvents == 'access_denied')
{
	$arResult['ACCESS_DENIED'] = true;
}
elseif ($arEvents == 'inactive_feature')
{
	$arResult['INACTIVE_FEATURE'] = true;
}
elseif (is_array($arEvents))
{
	if (strpos($arParams['DETAIL_URL'], '?') !== FALSE)
		$arParams['DETAIL_URL'] = substr($arParams['DETAIL_URL'], 0, strpos($arParams['DETAIL_URL'], '?'));
	$arParams['DETAIL_URL'] = str_replace('#user_id#', $curUserId, strtolower($arParams['DETAIL_URL']));

	for ($i = 0, $l = count($arEvents); $i < $l; $i++)
	{
		$arEvents[$i]['_DETAIL_URL'] = $arParams['DETAIL_URL'].'?EVENT_ID='.$arEvents[$i]['ID'].'&EVENT_DATE='.$arEvents[$i]['DATE_FROM'];
		if ($arEvents[$i]['IS_MEETING'] && $arEvents[$i]['MEETING_STATUS'] == 'Q')
		{
			$arEvents[$i]['_ADD_CLASS'] = ' calendar-not-confirmed';
			$arEvents[$i]['_Q_ICON'] = '<span class="calendar-reminder" title="'.GetMessage('EC_NOT_CONFIRMED').'">[?]</span>';
		}
		else
		{
			$arEvents[$i]['_ADD_CLASS'] = '';
			$arEvents[$i]['_Q_ICON'] = '';
		}
		if ($arEvents[$i]['IMPORTANCE'] == 'high')
			$arEvents[$i]['_ADD_CLASS'] = ' imortant-event';

		$fromTs = CCalendar::Timestamp($arEvents[$i]['DATE_FROM']);
		$toTs = $fromTs + $arEvents[$i]['DT_LENGTH'];

		$arEvents[$i]['~FROM_TO_HTML'] = CCalendar::GetFromToHtml($fromTs, $toTs, $arEvents[$i]['DT_SKIP_TIME'] == 'Y', $arEvents[$i]['DT_LENGTH']);

		$arResult['ITEMS'][] = $arEvents[$i];
	}
	array_splice($arResult['ITEMS'], intVal($arParams['EVENTS_COUNT']));
}


if ($arParams['RETURN_ARRAY'] == 'Y')
	return $arResult;

$this->IncludeComponentTemplate();

?>