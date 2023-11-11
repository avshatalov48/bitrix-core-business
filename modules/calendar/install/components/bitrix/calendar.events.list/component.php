<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams['B_CUR_USER_LIST'] = ($arParams['B_CUR_USER_LIST'] ?? null) === 'Y';
$arParams["FUTURE_MONTH_COUNT"] = (int)$arParams["FUTURE_MONTH_COUNT"];
if ($arParams["FUTURE_MONTH_COUNT"] <= 0)
{
	$arParams["FUTURE_MONTH_COUNT"] = 1;
}

$curUserId = $USER->IsAuthorized() ? $USER->GetID() : '';

if(!CModule::IncludeModule("calendar") || !class_exists("CCalendar"))
{
	return ShowError(GetMessage("EC_CALENDAR_MODULE_NOT_INSTALLED"));
}

// Limits
if ($arParams["INIT_DATE"] <> '' && mb_strpos($arParams["INIT_DATE"], '.') !== false)
{
	$ts = CCalendar::Timestamp($arParams["INIT_DATE"]);
}
else
{
	$ts = time();
}

$fromLimit = CCalendar::Date($ts, false);
$ts = CCalendar::Timestamp($fromLimit);
$toLimit = CCalendar::Date(mktime(0, 0, 0, date("m", $ts) + $arParams["FUTURE_MONTH_COUNT"], date("d", $ts), date("Y", $ts)), false);

$arResult['ITEMS'] = [];
$eventsList = CCalendar::GetNearestEventsList(
	[
		'bCurUserList' => $arParams['B_CUR_USER_LIST'],
		'fromLimit' => $fromLimit,
		'toLimit' => $toLimit,
		'type' => $arParams['CALENDAR_TYPE'],
		'sectionId' => $arParams['CALENDAR_SECTION_ID'] ?? null,
	]);

if ($eventsList === 'access_denied')
{
	$arResult['ACCESS_DENIED'] = true;
}
elseif ($eventsList === 'inactive_feature')
{
	$arResult['INACTIVE_FEATURE'] = true;
}
elseif (is_array($eventsList))
{
	if (mb_strpos($arParams['DETAIL_URL'], '?') !== FALSE)
	{
		$arParams['DETAIL_URL'] = mb_substr($arParams['DETAIL_URL'], 0, mb_strpos($arParams['DETAIL_URL'], '?'));
	}
	$arParams['DETAIL_URL'] = str_replace('#user_id#', $curUserId, mb_strtolower($arParams['DETAIL_URL']));

	$eventCount = count($eventsList);
	for ($i = 0, $l = $eventCount; $i < $l; $i++)
	{
		$eventsList[$i]['_DETAIL_URL'] = CHTTP::urlAddParams($arParams['DETAIL_URL'], array(
			'EVENT_ID' => $eventsList[$i]['ID'],
			'EVENT_DATE' => CCalendar::Date(CCalendar::Timestamp($eventsList[$i]['DATE_FROM']), false)
		));

		if ($eventsList[$i]['IS_MEETING'] && $eventsList[$i]['MEETING_STATUS'] === 'Q')
		{
			$eventsList[$i]['_ADD_CLASS'] = ' calendar-not-confirmed';
			$eventsList[$i]['_Q_ICON'] = '<span class="calendar-reminder" title="'.GetMessage('EC_NOT_CONFIRMED').'">[?]</span>';
		}
		else
		{
			$eventsList[$i]['_ADD_CLASS'] = '';
			$eventsList[$i]['_Q_ICON'] = '';
		}
		if ($eventsList[$i]['IMPORTANCE'] === 'high')
		{
			$eventsList[$i]['_ADD_CLASS'] = ' imortant-event';
		}

		$fromTs = CCalendar::Timestamp($eventsList[$i]['DATE_FROM']);
		$toTs = $fromTs + $eventsList[$i]['DT_LENGTH'];

		$eventsList[$i]['~FROM_TO_HTML'] = CCalendar::GetFromToHtml(
			$fromTs,
			$toTs,
			$eventsList[$i]['DT_SKIP_TIME'] === 'Y',
			$eventsList[$i]['DT_LENGTH']
		);

		$arResult['ITEMS'][] = $eventsList[$i];

		if ($i > (int)$arParams['EVENTS_COUNT'])
		{
			break;
		}
	}
}

if (($arParams['RETURN_ARRAY'] ?? null) === 'Y')
{
	return $arResult;
}

$this->IncludeComponentTemplate();

?>