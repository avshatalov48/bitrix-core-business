<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Sharing;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

class CalendarPubSharingComponent extends CBitrixComponent
{
	protected array $allowedActions = [Sharing\Helper::CANCEL, Sharing\Helper::CONFERENCE, Sharing\Helper::ICS];

	/**
	 * @param $component
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
	}

	/**
	 * @return mixed|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function executeComponent()
	{
		if (!Main\Loader::includeModule('calendar') || !\Bitrix\Calendar\Sharing\Helper::isSharingFeatureEnabled())
		{
			return $this->includeComponentTemplate('alert');
		}
		$link = $this->getLinkInfo();
		$this->arResult['LINK'] = $link;

		$link['type'] = $link['type'] ?? null;
		$link['active'] = $link['active'] ?? null;
		$link['eventId'] = $link['eventId'] ?? null;

		if ($link['type'] === 'user' && $link['active'] === true)
		{
			$this->prepareCalendarParams($link);

			return $this->includeComponentTemplate();
		}

		if ($link['type'] === 'event' && $link['eventId'] && $link['active'] === true)
		{
			$this->prepareEventParams($link);

			if (!$this->arResult['EVENT'])
			{
				return $this->includeComponentTemplate('alert');
			}

			return $this->includeComponentTemplate('event');
		}

		return $this->includeComponentTemplate('alert');
	}

	/**
	 * @return array|null
	 */
	protected function getLinkInfo(): ?array
	{
		$hash = $this->arParams['HASH'];

		return (new Sharing\Link\Factory())->getLinkArrayByHash($hash);
	}

	/**
	 * @param array $link
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function prepareCalendarParams(array $link): void
	{
		$userId = $link['userId'];

		$this->arResult['OWNER'] = Sharing\Helper::getOwnerInfo($userId);
		$this->arResult['SHARING_USER'] = $this->getSharingUserInfo($userId);
		$this->arResult['USER_ACCESSIBILITY'] = $this->getUserAccessibility($userId);
		$this->arResult['TIMEZONE_LIST'] = \CCalendar::GetTimezoneList();

		$this->arResult['CALENDAR_SETTINGS'] = [
			'workTimeStart' => $this->getWorkStart($userId),
			'workTimeEnd' => $this->getWorkEnd($userId),
			'weekHolidays' => explode('|', COption::GetOptionString('calendar', 'week_holidays', 'SA|SU')),
			'yearHolidays' => $this->getYearHolidays(),
			'weekStart' => CCalendar::GetWeekStart(),
			'phoneFeatureEnabled' => Sharing\Helper::isPhoneFeatureEnabled(),
		];

		$this->arResult['WELCOME_PAGE_VISITED'] = false;
		// $this->arResult['WELCOME_PAGE_VISITED'] = \Bitrix\Main\Context::getCurrent()
		// 	->getRequest()
		// 	->getCookieRaw('CALENDAR_SHARING_FIRST_PAGE_VISITED')
		// ;
	}


	/**
	 * @param array $link
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function prepareEventParams(array $link): void
	{
		$this->arResult['EVENT'] = $this->getEventById($link['eventId']);
		$this->arResult['OWNER_MEETING_STATUS'] = $this->getOwnerMeetingStatus($link['eventId'], $link['ownerId']);
		$this->arResult['OWNER'] = Sharing\Helper::getOwnerInfo($link['ownerId']);
		$this->arResult['SHARING_USER'] = $this->getSharingUserInfo($link['ownerId']);
		$this->arResult['ACTION'] = $this->getAction();
	}

	/**
	 * @param int $userId
	 * @return array
	 */
	protected function getUserAccessibility(int $userId): array
	{
		$date = new \Bitrix\Main\Type\Date();
		$arrayKey = $date->format('n') . '.' . $date->format('Y');
		$monthStart = strtotime('first day of this month 00:00:00');
		$monthEnd = strtotime('last day of this month 23:59:59');

		$result = (new Sharing\SharingAccessibilityManager([
			'userId' => $userId,
			'timestampFrom' => $monthStart,
			'timestampTo' => $monthEnd
		]))->getUserAccessibilitySegmentsInUtc();

		return [$arrayKey => $result];
	}

	/**
	 * @param $userId
	 * @return int
	 */
	protected function getWorkStart($userId): float
	{
		$timezoneOffset = \CCalendar::GetCurrentOffsetUTC($userId) / 3600;
		$workStart = (float)COption::GetOptionString('calendar', 'work_time_start', 9);
		$workStart = floor($workStart) + 5 * ($workStart - floor($workStart)) / 3;

		return $workStart - $timezoneOffset;
	}

	/**
	 * @param $userId
	 * @return int
	 */
	protected function getWorkEnd($userId): float
	{
		$timezoneOffset = \CCalendar::GetCurrentOffsetUTC($userId) / 3600;
		$workEnd = (float)COption::GetOptionString('calendar', 'work_time_end', 19);
		$workEnd = floor($workEnd) + 5 * ($workEnd - floor($workEnd)) / 3;

		return $workEnd - $timezoneOffset;
	}

	/**
	 * @return array
	 */
	protected function getYearHolidays(): array
	{
		$result = [];
		$holidays = explode(',', COption::GetOptionString('calendar', 'year_holidays', Loc::getMessage('EC_YEAR_HOLIDAYS_DEFAULT')));

		foreach ($holidays as $day)
		{
			$result[$day] = $day;
		}

		return $result;
	}

	/**
	 * @param int $eventId
	 * @return array
	 * @throws Main\ObjectException
	 */
	protected function getEventById(int $eventId): array
	{
		$event = \CCalendarEvent::GetById($eventId, false);

		if (!$event)
		{
			return [];
		}

		$eventTsFromUTC = Sharing\Helper::getEventTimestampUTC(new Main\Type\DateTime($event['DATE_FROM']), $event['TZ_FROM']);
		$eventTsToUTC = Sharing\Helper::getEventTimestampUTC(new Main\Type\DateTime($event['DATE_TO']), $event['TZ_TO']);

		if ($event['DT_SKIP_TIME'] === 'Y')
		{
			$eventTsToUTC += \CCalendar::GetDayLen();
		}

		return [
			'id' => $eventId,
			'name' => $event['NAME'],
			'dateFrom' => $event['DATE_FROM'],
			'dateTo' => $event['DATE_TO'],
			'timezone' => $event['TZ_FROM'],
			'timestampFromUTC' => $eventTsFromUTC,
			'timestampToUTC' => $eventTsToUTC,
		];
	}

	/**
	 * @param int $eventId
	 * @param int $ownerId
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getOwnerMeetingStatus(int $eventId, int $ownerId)
	{
		$result = \Bitrix\Calendar\Internals\EventTable::query()
			->setSelect(['MEETING_STATUS'])
			->setLimit(1)
			->where('PARENT_ID', $eventId)
			->where('OWNER_ID', $ownerId)
			->where('DELETED', 'N')
			->exec()->fetch()
		;

		return $result['MEETING_STATUS'];
	}

	/**
	 * @param int|null $ownerId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getSharingUserInfo(?int $ownerId = null): array
	{
		$result = [
			'ownerCreated' => false,
			'userId' => null,
			'userName' => '',
			'personalPhone' => '',
			'personalMailbox' => ''
		];
		$currentUserId = \Bitrix\Calendar\Sharing\SharingUser::getInstance()->login();

		if ($currentUserId && $ownerId && (int)$currentUserId === $ownerId)
		{
			$result['ownerCreated'] = true;
			$result['userId'] = $currentUserId;

			return $result;
		}

		if ($currentUserId)
		{
			$user = CUser::GetByID($currentUserId)->Fetch();

			$result = [
				'userId' => $currentUserId,
				'userName' => $user['NAME'],
				'personalPhone' => $user['PERSONAL_PHONE'],
				'personalMailbox' => $user['PERSONAL_MAILBOX'],
			];
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getAction(): string
	{
		$request = Main\Context::getCurrent()->getRequest();
		$action = (string)$request->getQueryList()->get(Sharing\Helper::ACTION);

		if (in_array($action, $this->allowedActions, true))
		{
			return $action;
		}

		return '';
	}

}