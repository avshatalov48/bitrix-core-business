<?

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Main\Localization\Loc;

class CCalendarRequest
{
	private static
		$request,
		$reqId,
		$calendar;

	public static function Process(CCalendar $calendar, $action = '')
	{
		global $APPLICATION;

		self::$request = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

		self::$calendar = $calendar;

		// Export calendar
		if ($action === 'export')
		{
			// We don't need to check access  couse we will check security SIGN from the URL
			$sectId = (int)$_GET['sec_id'];
			if (($_GET['check'] ?? null) === 'Y') // Just for access check from calendar interface
			{
				$APPLICATION->RestartBuffer();
				if (CCalendarSect::CheckSign($_GET['sign'], (int)$_GET['user'], $sectId > 0 ? $sectId : 'superposed_calendars'))
				{
					echo 'BEGIN:VCALENDAR';
				}
				CMain::FinalActions();
				die();
			}

			if ($sectId > 0 && CCalendarSect::CheckAuthHash())
			{
				// We don't need any warning in .ics file
				error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
				CCalendarSect::ReturnICal(array(
					'sectId' => $sectId,
					'userId' => (int)$_GET['user'],
					'sign' => $_GET['sign'],
					'type' => $_GET['type'],
					'ownerId' => (int)$_GET['owner']
				));
			}
			else
			{
				$APPLICATION->RestartBuffer();
			}
		}
		else
		{
			// Check the access
			$accessController = new TypeAccessController(CCalendar::GetUserId());
			$typeModel = TypeModel::createFromXmlId(CCalendar::GetType());
			$action = ActionDictionary::ACTION_TYPE_VIEW;

			if (!$accessController->check($action, $typeModel) || !check_bitrix_sessid())
			{
				$APPLICATION->ThrowException(Loc::getMessage("EC_ACCESS_DENIED"));
				return false;
			}

			$APPLICATION->ShowAjaxHead();
			$APPLICATION->RestartBuffer();
			self::$reqId = (int)$_REQUEST['reqId'];

			switch ($action)
			{
				case 'set_meeting_status':
					self::SetStatus();
					break;
				case 'get_planner':
					self::GetPlanner();
					break;
				case 'update_planner':
					self::UpdatePlanner();
					break;
				case 'get_destination_items':
					self::getDestinationItems();
					break;
			}
		}

		if($ex = $APPLICATION->GetException())
		{
			ShowError($ex->GetString());
		}

		CMain::FinalActions();
		die();
	}

	public static function OutputJSRes($reqId = false, $res = false)
	{
		if ($res === false)
		{
			return;
		}
		if ($reqId === false)
		{
			$reqId = (int)($_REQUEST['reqId'] ?? null);
		}
		if (!$reqId)
		{
			return;
		}
		?>
		<script>top.BXCRES['<?= $reqId?>'] = <?= CUtil::PhpToJSObject($res)?>;</script>
		<?
	}

	public static function SetStatus()
	{
		CCalendarEvent::SetMeetingStatusEx(array(
			'attendeeId' => CCalendar::GetUserId(),
			'eventId' => (int)$_REQUEST['event_id'],
			'parentId' => (int)$_REQUEST['parent_id'],
			'status' => in_array($_REQUEST['status'], array('Q', 'Y', 'N')) ? $_REQUEST['status'] : 'Q',
			'reccurentMode' => in_array($_REQUEST['reccurent_mode'], array('this', 'next', 'all')) ? $_REQUEST['reccurent_mode'] : false,
			'currentDateFrom' => CCalendar::Date(CCalendar::Timestamp($_REQUEST['current_date_from']), false)
		));

		self::OutputJSRes(self::$reqId, true);
	}

	public static function GetPlanner()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$plannerId = $_REQUEST['planner_id'];
		?><?CCalendarPlanner::Init(array('id' => $plannerId));?><?
	}

	public static function UpdatePlanner()
	{
		$curEventId = (int)self::$request['cur_event_id'];
		$curUserId = CCalendar::GetCurUserId();
		$codes = false;
		if (isset(self::$request['codes']) && is_array(self::$request['codes']))
		{
			$codes = array();
			foreach(self::$request['codes'] as $code)
			{
				if($code)
				{
					$codes[] = $code;
				}
			}

			if(self::$request['add_cur_user_to_list'] === 'Y' || count($codes) <= 0)
			{
				$codes[] = 'U'.$curUserId;
			}
		}

		$result = CCalendarPlanner::PrepareData(array(
			'entry_id' => $curEventId,
			'user_id' => $curUserId,
			'codes' => $codes,
			'entries' => self::$request['entries'],
			'date_from' => CCalendar::Date(CCalendar::Timestamp(self::$request['date_from']), false),
			'date_to' => CCalendar::Date(CCalendar::Timestamp(self::$request['date_to']), false),
			'timezone' => self::$request['timezone'],
			'location' => trim(self::$request['location']),
			'roomEventId' => (int)self::$request['roomEventId']
		));

		self::OutputJSRes(self::$reqId, $result);
	}

	public static function getDestinationItems()
	{
		self::OutputJSRes(self::$reqId,
			array(
				'result' => true,
				'destinationItems' => CCalendar::GetSocNetDestination(false, self::$request['codes'])
			)
		);
	}
}

?>