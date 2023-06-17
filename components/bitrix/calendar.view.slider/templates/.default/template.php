<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use \Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
global $APPLICATION, $USER_FIELD_MANAGER;

$userId = CCalendar::GetCurUserId();
$id = $arParams['id'];
$event = $arParams['event'];
$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
$toTs = CCalendar::Timestamp($event['DATE_TO']);
$skipTime = $event['DT_SKIP_TIME'] == "Y";
$meetingCreator = false;

if (empty($event))
{
	?>
	<div class="ui-alert ui-alert-danger ui-alert-icon-danger ui-alert-text-center">
		<span class="ui-alert-message"><?= Loc::getMessage('EC_VIEW_SLIDER_EVENT_NOT_FOUND')?></span>
	</div>
	<?
	return;
}

if ($skipTime)
{
	$toTs += CCalendar::DAY_LENGTH;
}
else
{
	$fromTs -= $event['~USER_OFFSET_FROM'];
	$toTs -= $event['~USER_OFFSET_TO'];
}

// Timezone Hint
$timezoneHint = '';
if (
	!$skipTime &&
	(intval($event['~USER_OFFSET_FROM']) !== 0 ||
		intval($event['~USER_OFFSET_TO']) !== 0 ||
		$event['TZ_FROM'] != $event['TZ_TO'] ||
		$event['TZ_FROM'] !== CCalendar::GetUserTimezoneName($userId))
)
{
	if ($event['TZ_FROM'] == $event['TZ_TO'])
	{
		$timezoneHint = CCalendar::GetFromToHtml(CCalendar::Timestamp($event['DATE_FROM']), CCalendar::Timestamp($event['DATE_TO']), $skipTime, $event['DT_LENGTH']);
		if ($event['TZ_FROM'])
			$timezoneHint .= ' ('.$event['TZ_FROM'].')';
	}
	else
	{
		$timezoneHint = Loc::getMessage('EC_VIEW_DATE_FROM_TO', array('#DATE_FROM#' => $event['DATE_FROM'].' ('.$event['TZ_FROM'].')', '#DATE_TO#' => $event['DATE_TO'].' ('.$event['TZ_TO'].')'));
	}
}
// From - to html
$fromToHtml = CCalendar::GetFromToHtml($fromTs, $toTs, $skipTime, $event['DT_LENGTH']);
$location = CCalendar::GetTextLocation($event['LOCATION']);

$UF = CCalendarEvent::GetEventUserFields($event);

if (!is_null($event['UF_CRM_CAL_EVENT']))
{
	$event['UF_CRM_CAL_EVENT'] = $UF['UF_CRM_CAL_EVENT'];
	if (empty($event['UF_CRM_CAL_EVENT']['VALUE']))
		$event['UF_CRM_CAL_EVENT'] = false;
}

if (!is_null($event['UF_WEBDAV_CAL_EVENT']))
{
	$event['UF_WEBDAV_CAL_EVENT'] = $UF['UF_WEBDAV_CAL_EVENT'];
	if(empty($event['UF_WEBDAV_CAL_EVENT']['VALUE']))
		$event['UF_WEBDAV_CAL_EVENT'] = false;
}

$avatarSize = 34;
$event['REMIND'] = CCalendarReminder::GetTextReminders($event['REMIND']);

$curUserStatus = '';
$userId = CCalendar::GetCurUserId();

$accessController = new EventAccessController($userId);
$eventModel = CCalendarEvent::getEventModelForPermissionCheck($event['ID'], $event, $userId);
$viewComments = $accessController->check(ActionDictionary::ACTION_EVENT_VIEW_COMMENTS, $eventModel);

$codes = array();
$meetingHost = false;
if ($event['IS_MEETING'])
{
	$userIndex = CCalendarEvent::getUserIndex();
	$attendees = ['y' => [], 'n' => [], 'q' => [], 'i' => []];

	if (is_array($event['ATTENDEE_LIST']))
	{
		foreach ($event['ATTENDEE_LIST'] as $attendee)
		{
			$codes[] = 'U'.intval($attendee['id']);
			$userDetails = $userIndex[$attendee['id']];

			if ($userId == $attendee["id"])
			{
				$curUserStatus = $attendee['status'];
				$viewComments = true;
			}

			$status = (mb_strtolower($attendee['status']) == 'h' || $attendee['status'] == '') ? 'y' : $attendee['status'];
			$attendees[mb_strtolower($status)][] = $userIndex[$attendee['id']];
			if ($attendee['status'] == 'H')
			{
				$meetingHost = $userIndex[$attendee['id']];
				$meetingHost['ID'] = $attendee['id'];
			}
		}
	}
}

if ($event['CAL_TYPE'] == 'user')
{
	$codes[] = 'U'.intval($event['OWNER_ID']);
}
else
{
	$codes[] = 'U'.intval($event['CREATED_BY']);
}

$codes = array_unique($codes);

if (!isset($meetingHost) || !$meetingHost)
{
	$meetingHost = CCalendar::GetUser($event['CREATED_BY'], true);
	$meetingHost['DISPLAY_NAME'] = CCalendar::GetUserName($meetingHost);
	$meetingHost['AVATAR'] = CCalendar::GetUserAvatarSrc($meetingHost);
	$meetingHost['URL'] = CCalendar::GetUserUrl($meetingHost["ID"], $arParams["PATH_TO_USER"]);
}

if ($event['IS_MEETING'] && $event['MEETING']['MEETING_CREATOR'] && $event['MEETING']['MEETING_CREATOR'] !== $event['MEETING_HOST'])
{
	$meetingCreator = CCalendar::GetUser($event['MEETING']['MEETING_CREATOR'], true);
	$meetingCreator['DISPLAY_NAME'] = CCalendar::GetUserName($meetingCreator);
	$meetingCreator['URL'] = CCalendar::GetUserUrl($meetingCreator["ID"], $meetingCreator["PATH_TO_USER"]);
}

$arParams['event'] = $event;
$arParams['UF'] = $UF;

?>