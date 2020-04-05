<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);
define("ADMIN_SECTION", true); // mantiss: 52059

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("calendar") || !class_exists("CCalendar"))
	return;

$event_feed_action = $_REQUEST["event_feed_action"];
if (!empty($event_feed_action) && check_bitrix_sessid())
{
	$GLOBALS["APPLICATION"]->ShowAjaxHead();
	$userId = $GLOBALS["USER"]->GetId();
	$eventId = intVal($_REQUEST['event_id']);

	if ($event_feed_action == 'delete_event')
	{
		$res = CCalendar::DeleteEvent($eventId);
		if ($res)
			echo '#EVENT_FEED_RESULT_OK#';
	}
	else
	{
		$status = false;
		if ($event_feed_action == 'decline')
			$status = 'N';
		elseif($event_feed_action == 'accept')
			$status = 'Y';

		if ($status && $eventId)
		{
			CCalendarEvent::SetMeetingStatusEx(array(
				'attendeeId' => $userId,
				'eventId' => $eventId,
				'parentId' => intVal($_REQUEST['parent_id']),
				'status' => $status,
				'reccurentMode' => in_array($_REQUEST['reccurent_mode'], array('this', 'next', 'all')) ? $_REQUEST['reccurent_mode'] : false,
				'currentDateFrom' => CCalendar::Date(CCalendar::Timestamp($_REQUEST['current_date_from']), false)
			));

			$events = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
				)
			);

			if ($events && is_array($events[0]) && $events[0]['IS_MEETING'])
			{
				$ajaxParams = $_REQUEST['ajax_params'];

				if ($ajaxParams["MOBILE"] == "Y")
				{
					$result = array(
						'ACCEPTED_ATTENDEES_COUNT' => 0,
						'DECLINED_ATTENDEES_COUNT' => 0
					);

					foreach ($events[0]['~ATTENDEES'] as $i => $att)
					{
						if ($att['STATUS'] == "Y")
							$result['ACCEPTED_ATTENDEES_COUNT']++;
						elseif($att['STATUS'] == "N")
							$result['DECLINED_ATTENDEES_COUNT']++;
					}

					if ($result['ACCEPTED_ATTENDEES_COUNT'] > 0)
						$result['ACCEPTED_ATTENDEES_MESSAGE'] = CCalendar::GetAttendeesMessage($result['ACCEPTED_ATTENDEES_COUNT']);
					if ($result['DECLINED_ATTENDEES_COUNT'] > 0)
						$result['DECLINED_ATTENDEES_MESSAGE'] = CCalendar::GetAttendeesMessage($result['DECLINED_ATTENDEES_COUNT']);
				}
				else
				{
					$result = array(
						'ACCEPTED_ATTENDEES' => array(),
						'DECLINED_ATTENDEES' => array(),
						'ACCEPTED_PARAMS' => array("prefix" => "y"),
						'DECLIINED_PARAMS' => array("prefix" => "n")
					);

					foreach ($events[0]['~ATTENDEES'] as $i => $att)
					{
						if ($att['STATUS'] != "Q")
						{
							$att['AVATAR_SRC'] = CCalendar::GetUserAvatar($att);
							$att['URL'] = CCalendar::GetUserUrl($att["USER_ID"], $arParams["PATH_TO_USER"]);
						}

						if ($att['STATUS'] == "Y")
							$result['ACCEPTED_ATTENDEES'][] = $att;
						elseif($att['STATUS'] == "N")
							$result['DECLINED_ATTENDEES'][] = $att;
					}
					$moreCountAcc = count($result['ACCEPTED_ATTENDEES']) - $ajaxParams['ATTENDEES_SHOWN_COUNT'];
					$moreCountDec = count($result['DECLINED_ATTENDEES']) - $ajaxParams['ATTENDEES_SHOWN_COUNT'];

					if ($moreCountAcc > 0)
						$result['ACCEPTED_PARAMS']['MORE_MESSAGE'] = CCalendar::GetMoreAttendeesMessage($moreCountAcc);
					if ($moreCountDec > 0)
						$result['DECLINED_PARAMS']['MORE_MESSAGE'] = CCalendar::GetMoreAttendeesMessage($moreCountDec);
				}

				?>
				<script>
					window.ViewEventManager.requestResult = <?=CUtil::PhpToJSObject($result)?>;
				</script>
				<?
			}
			echo '#EVENT_FEED_RESULT_OK#';
		}
	}
	die();
}
?>