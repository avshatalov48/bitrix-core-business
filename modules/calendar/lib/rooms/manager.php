<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Calendar\Internals\AccessTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\LocationTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\EventManager;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Text\Emoji;

Loc::loadMessages(__FILE__);

class Manager
{
	public const TYPE = 'location';

	/** @var Room|null $room */
	private ?Room $room = null;
	/** @var Error|null $error */
	private ?Error $error = null;

	protected function __construct()
	{
	}

	/**
	 * @param Room $room
	 *
	 * @return Manager
	 */
	public static function createInstanceWithRoom(Room $room): Manager
	{
		return (new self())->setRoom($room);
	}

	/**
	 * @return Manager
	 */
	public static function createInstance(): Manager
	{
		return new self;
	}

	/**
	 * @param Room $room
	 *
	 * @return Manager
	 */
	private function setRoom(Room $room): Manager
	{
		$this->room = $room;

		return $this;
	}

	/**
	 * @param Error $error
	 *
	 * @return void
	 */
	private function addError(Error $error): void
	{
		$this->error = $error;
	}

	public function getError(): ?Error
	{
		return $this->error;
	}

	/**
	 * Creating Room in Location Calendar
	 *
	 * @return Manager
	 * @throws \Exception
	 */
	public function createRoom(): Manager
	{
		if ($this->error)
		{
			return $this;
		}

		$this->room->create();

		if ($this->room->getError())
		{
			$this->addError($this->room->getError());
		}

		return $this;
	}

	/**
	 * Updating data of room in Location calendar
	 *
	 * @return Manager
	 * @throws \Exception
	 */
	public function updateRoom(): Manager
	{
		if ($this->error)
		{
			return $this;
		}

		$this->room->update();

		if ($this->room->getError())
		{
			$this->addError($this->room->getError());
		}

		return $this;
	}

	/**
	 * Deleting room by id in Location calendar
	 *
	 * @return Manager
	 * @throws \Exception
	 */
	public function deleteRoom(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		if (!$this->room->getName())
		{
			$this->room->setName($this->getRoomName($this->room->getId()));
		}

		$this->room->delete();

		if ($this->room->getError())
		{
			$this->addError($this->room->getError());
		}

		return $this;
	}

	/**
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getRoomsList(): ?array
	{
		$roomQuery = LocationTable::query()
			->setSelect([
				'LOCATION_ID' => 'ID',
				'NECESSITY',
	            'CAPACITY',
				'SECTION_ID',
				'CATEGORY_ID',
				'NAME' => 'SECTION.NAME',
				'COLOR' => 'SECTION.COLOR',
				'OWNER_ID' => 'SECTION.OWNER_ID',
				'CAL_TYPE' => 'SECTION.CAL_TYPE',
            ])
			->registerRuntimeField('SECTION',
               new ReferenceField(
                   'SECTION',
                   SectionTable::getEntity(),
                   Query\Join::on('ref.ID', 'this.SECTION_ID'),
                   ['join_type' => Query\Join::TYPE_INNER]
               )
			)
			->setOrder(['ID' => 'ASC'])
			->cacheJoins(true)
			->setCacheTtl(86400)
			->exec()
		;

		[$roomsId, $result] = self::prepareRoomsQueryData($roomQuery);

		if (empty($result))
		{
			\CCalendarSect::CreateDefault([
				'type' => self::TYPE,
				'ownerId' => 0
			]);
			LocationTable::cleanCache();

			return null;
		}

		$result = self::getRoomsAccess($roomsId, $result);

		foreach ($result as $room)
		{
			\CCalendarSect::HandlePermission($room);
		}

		return \CCalendarSect::GetSectionPermission($result);
	}

	/**
	 * @param int $id
	 * @param array $params
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getRoomById(int $id, array $params = []): array
	{
		$roomQuery = LocationTable::query()
			->setSelect([
				'LOCATION_ID' => 'ID',
				'NECESSITY',
				'CAPACITY',
				'SECTION_ID',
				'CATEGORY_ID',
				'NAME' => 'SECTION.NAME',
				'COLOR' => 'SECTION.COLOR',
				'OWNER_ID' => 'SECTION.OWNER_ID',
				'CAL_TYPE' => 'SECTION.CAL_TYPE',
			])
			->where('SECTION.ID', $id)
			->registerRuntimeField('SECTION',
				new ReferenceField(
					'SECTION',
					SectionTable::getEntity(),
					Query\Join::on('ref.ID', 'this.SECTION_ID'),
					['join_type' => Query\Join::TYPE_INNER]
				)
			)
			->cacheJoins(true)
			->setCacheTtl(86400)
			->exec()
		;

		[$roomsId, $result] = self::prepareRoomsQueryData($roomQuery);
		$result = self::getRoomsAccess($roomsId, $result);

		foreach ($result as $room)
		{
			\CCalendarSect::HandlePermission($room);
		}

		$applyPermission = $params['checkPermission'] ?? true;
		if ($applyPermission !== false)
		{
			return \CCalendarSect::GetSectionPermission($result);
		}

		return [...$result];
	}

	/**
	 * @param array $params
	 *
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function reserveRoom(array $params = []): ?int
	{
		$params['checkPermission'] = $params['checkPermission'] ?? null;
		$params['room_id'] = $params['room_id'] ?? null;
		$roomList = self::getRoomById((int)$params['room_id'], ['checkPermission' => $params['checkPermission']]);

		if (
			!$roomList || empty($roomList[0])
			|| empty($roomList[0]['NAME'])
			|| (
				empty($roomList[0]['PERM']['view_full'])
				&& $params['checkPermission'] !== false
			)
		)
		{
			return null;
		}

		$createdBy = $params['parentParams']['arFields']['CREATED_BY']
			?? $params['parentParams']['arFields']['MEETING_HOST']
			?? null
		;

		$arFields = [
			'ID' => $params['room_event_id'] ?? null,
			'SECTIONS' => $params['room_id'] ?? null,
			'DATE_FROM' => $params['parentParams']['arFields']['DATE_FROM'] ?? null,
			'DATE_TO' => $params['parentParams']['arFields']['DATE_TO'] ?? null,
			'TZ_FROM' => $params['parentParams']['arFields']['TZ_FROM'] ?? null,
			'TZ_TO' => $params['parentParams']['arFields']['TZ_TO'] ?? null,
			'SKIP_TIME' => $params['parentParams']['arFields']['SKIP_TIME'] ?? null,
			'RRULE' => $params['parentParams']['arFields']['RRULE'] ?? null,
			'EXDATE' => $params['parentParams']['arFields']['EXDATE'] ?? null,
		];

		if (!$params['room_event_id'])
		{
			$arFields['CREATED_BY'] = $createdBy;
			$arFields['NAME'] = \CCalendar::GetUserName($createdBy);
			$arFields['CAL_TYPE'] = self::TYPE;
		}

		return \CCalendarEvent::Edit([
			'arFields' => $arFields,
		]);
	}

	/**
	 * @param array $params
	 *
	 * Cancel booking of room
	 *
	 * @return Manager
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function cancelBooking(array $params = []): Manager
	{
		$params = [
			'recursion_mode' => $params['recursion_mode'] ?? null,
			'parent_event_id' => $params['parent_event_id'] ?? null,
			'section_id' => $params['section_id'] ?? null,
			'current_event_date_from' => $params['current_event_date_from'] ?? null,
			'current_event_date_to' => $params['current_event_date_to'] ?? null,
			'owner_id' => $params['owner_id'] ?? null,
		];

		if($this->getError() !== null)
		{
			return $this;
		}

		if($params['recursion_mode'] === 'all' || $params['recursion_mode'] === 'next')
		{
			$event = \CCalendarEvent::GetById($params['parent_event_id']);

			$params['frequency'] = $event['RRULE']['FREQ'] ?? null;
			if($params['recursion_mode'] === 'all')
			{
				$params['current_event_date_from'] = $event['DATE_FROM'] ?? null;
				$params['current_event_date_to'] = $event['DATE_TO'] ?? null;
			}
		}

		$result = \CCalendar::SaveEventEx([
			'recursionEditMode' => $params['recursion_mode'],
			'currentEventDateFrom' => $params['current_event_date_from'],
			'checkPermission' => false,
			'sendInvitations' => false,
			'userId' => $params['owner_id'],
			'arFields' => [
				'ID' => $params['parent_event_id'],
				'DATE_FROM' => $params['current_event_date_from'],
				'DATE_TO' => $params['current_event_date_to'],
				'LOCATION' => '',
			],
		]);

		$params['event_id'] = $result['recEventId'] ?? $result['id'] ?? null;

		$this->sendCancelBookingNotification($params);
		return $this;
	}

	private function sendCancelBookingNotification(array $params): void
	{
		$params = [
			'section_id' => $params['section_id'],
			'event_id' => $params['event_id'],
			'owner_id' => $params['owner_id'],
			'current_event_date_from' => $params['current_event_date_from'],
			'recursion_mode' => $params['recursion_mode'],
		];

		$section = \CCalendarSect::GetById($params['section_id']);
		$userId = \CCalendar::GetCurUserId();
		$event = \CCalendarEvent::GetById($params['event_id'], false);

		\CCalendarNotify::Send([
			'eventId' => $params['event_id'],
			'mode' => 'cancel_booking',
			'location' => $section['NAME'] ?? null,
			'locationId' => $params['section_id'],
			'guestId' => $params['owner_id'],
			'userId' => $userId,
			'from' => $params['current_event_date_from'],
			'eventName' => $event['NAME'] ?? null,
			'recursionMode' => $params['recursion_mode'],
			'fields' => $event,
		]);
	}

	/**
	 * @param array $params
	 *
	 * Deleting event from calendar location
	 *
	 * @return bool|string
	 */
	public static function releaseRoom(array $params = [])
	{
		return \CCalendar::DeleteEvent(
			(int)($params['room_event_id'] ?? null),
			false,
			[
				'checkPermissions' => false,
				'markDeleted' => false
			]
		);
	}

	/**
	 * @return Manager
	 *
	 * Clears cache for updating list of rooms on the page
	 */
	public function clearCache(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		\CCalendarSect::SetClearOperationCache(true);
		\CCalendar::clearCache([
			'section_list',
			'event_list'
		]);
		LocationTable::cleanCache();

		return $this;
	}

	/**
	 * @return Manager
	 */
	public function cleanAccessTable(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		\CCalendarSect::CleanAccessTable($this->room->getId());

		return $this;
	}

	/**
	 * @param int $id
	 *
	 * Setting id of new event in user calendar
	 * for event in location calendar
	 *
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function setEventIdForLocation(int $id): void
	{
		$event = EventTable::query()
			->setSelect(['LOCATION'])
			->where('ID', $id)
			->exec()->fetch()
		;

		if (!empty($event['LOCATION']))
		{
			$location = Util::parseLocation($event['LOCATION']);
			if ($location['room_id'] && $location['room_event_id'])
			{
				EventTable::update($location['room_event_id'], [
						'PARENT_ID' => $id,
				]);
			}
		}
	}

	/**
	 * Preparing data with rooms and sections for ajax-actions
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function prepareResponseData(): array
	{
		$result = [];

		$result['rooms'] = self::getRoomsList();
		$sectionList = \CCalendar::GetSectionList([
			'CAL_TYPE' => self::TYPE,
			'OWNER_ID' => 0,
			'checkPermissions' => true,
			'getPermissions' => true,
			'getImages' => true
		]);
		$sectionList = array_merge(
			$sectionList,
			\CCalendar::getSectionListAvailableForUser(\CCalendar::GetUserId())
		);
		$result['sections'] = $sectionList;

		return $result;
	}

	/**
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function prepareRoomManagerData(): ?array
	{
		$userId = \CCalendar::GetUserId();
		$result = [];

		$followedSectionList = UserSettings::getFollowedSectionIdList($userId);
		$sectionList = \CCalendar::GetSectionList([
			'CAL_TYPE' => self::TYPE,
			'OWNER_ID' => 0,
			'ADDITIONAL_IDS' => $followedSectionList,
		]);
		$sectionList = array_merge($sectionList, \CCalendar::getSectionListAvailableForUser($userId));

		$sectionAccessTasks = \CCalendar::GetAccessTasks('calendar_section', 'location');
		$hiddenSections = UserSettings::getHiddenSections(
			$userId,
			[
				'type' => self::TYPE,
				'ownerId' => 0,
			]
		);
		$defaultSectionAccess = \CCalendarSect::GetDefaultAccess(
			self::TYPE,
			$userId
		);

		$result['rooms'] = self::getRoomsList();
		$result['sections'] = $sectionList;
		$result['config'] = [
			'locationAccess' => Util::getLocationAccess($userId),
			'hiddenSections' => $hiddenSections,
			'type' => self::TYPE,
			'ownerId' => 0,
			'userId' => $userId,
			'defaultSectionAccess' => $defaultSectionAccess,
			'sectionAccessTasks' => $sectionAccessTasks,
			'showTasks' => false,
			'accessNames' => \CCalendar::GetAccessNames(),
		];

		return $result;
	}

	/**
	 * @param $handler
	 *
	 * @return Manager
	 */
	public function eventHandler($handler): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		foreach(EventManager::getInstance()->findEventHandlers('calendar', $handler) as $event)
		{
			ExecuteModuleEventEx($event, [
				$this->room->getId(),
			]);
		}

		return $this;
	}

	public function addPullEvent($event): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		\Bitrix\Calendar\Util::addPullEvent(
			$event,
			$this->room->getCreatedBy(),
			[
				'ID' => $this->room->getId()
			]
		);

		return $this;
	}

	/**
	 * @param int $id
	 *
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getRoomName(int $id): ?string
	{
		$section = SectionTable::query()
			->setSelect(['NAME'])
			->where('ID', $id)
			->exec()->fetch()
		;

		return $section ? $section['NAME'] : null;
	}

	/**
	 * @param $name
	 * Validation for name of room
	 *
	 * @return string|null
	 */
	public static function checkRoomName(?string $name): ?string
	{
		if (!$name)
		{
			return '';
		}

		$name = trim($name);

		if (empty($name))
		{
			return '';
		}

		return $name;
	}

	/**
	 * Delete location value when deleting room
	 *
	 * @return $this
	 */
	public function deleteLocationFromEvents(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}
		
		$guestsId = [];
		$eventsId = [];
		$id = $this->room->getId();
		$locationName = $this->room->getName();
		$locationId = 'calendar_' . $id;
		
		$events = EventTable::query()
			->setSelect(['ID', 'PARENT_ID', 'OWNER_ID', 'CREATED_BY', 'LOCATION'])
			->whereLike('LOCATION', $locationId. '%')
			->exec()
		;
		
		while ($event = $events->fetch())
		{
			if ($event['ID'] === $event['PARENT_ID'])
			{
				$guestsId[] = $event['OWNER_ID'];
			}
			
			$eventsId[] = $event['ID'];
		}
		
		if (!empty($eventsId))
		{
			EventTable::updateMulti($eventsId, ['LOCATION' => '']);
			
			$guestsId = array_unique($guestsId);
			$userId = \CCalendar::GetCurUserId();
			
			foreach ($guestsId as $guestId)
			{
				\CCalendarNotify::Send([
					'mode' => 'delete_location',
					'location' => $locationName,
					'locationId' => $id,
					'guestId' => (int)$guestId,
					'userId' => $userId,
				]);
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function pullDeleteEvents(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		$events = self::getLocationEventsId($this->room->getId());

		foreach ($events as $event)
		{
			if ($this->room->getCreatedBy())
			{
				\Bitrix\Calendar\Util::addPullEvent(
					'delete_event',
					$this->room->getCreatedBy(),
					['fields' => $event]
				);
			}
		}

		return $this;
	}

	/**
	 * @return Manager
	 */
	public function deleteEmptyEvents(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		\CCalendarEvent::DeleteEmpty($this->room->getId());

		return $this;
	}

	/**
	 * @param int $roomId
	 *
	 * @return array of location events id with a given id
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getLocationEventsId(int $roomId): array
	{
		return EventTable::query()
			->setSelect([
	            'ID',
	            'CREATED_BY',
	            'PARENT_ID',
			])
			->where('SECTION_ID', $roomId)
			->where('DELETED', 'N')
			->exec()->fetchAll()
		;
	}

	/**
	 * @return Manager
	 */
	public function saveAccess(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		$access = $this->room->getAccess();
		$id = $this->room->getId();

		if (!empty($access))
		{
			\CCalendarSect::SavePermissions(
				$id,
				$access
			);
		}
		else
		{
			\CCalendarSect::SavePermissions(
				$id,
				\CCalendarSect::GetDefaultAccess(
					$this->room->getType(),
					$this->room->getCreatedBy()
				)
			);
		}

		return $this;
	}

	/**
	 * @param Query\Result $query
	 *
	 * @return array
	 */
	private static function prepareRoomsQueryData(Query\Result $query): array
	{
		$roomsId = [];
		$result = [];

		while ($room = $query->fetch())
		{
			$room['ID'] = $room['SECTION_ID'];
			unset($room['SECTION_ID']);

			if (!empty($room['NAME']))
			{
				$room['NAME'] = Emoji::decode($room['NAME']);
			}
			$roomId = (int)$room['ID'];
			$roomsId[] = $roomId;
			$result[$roomId] = $room;
		}

		return [$roomsId, $result];
	}

	/**
	 * @param array $roomsId
	 * @param array $rooms
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getRoomsAccess(array $roomsId, array $rooms): array
	{
		if (empty($roomsId))
		{
			return [];
		}

		$accessQuery = AccessTable::query()
			->setSelect([
				'ACCESS_CODE',
				'TASK_ID',
				'SECT_ID'
			])
			->whereIn('SECT_ID', $roomsId)
			->exec()
		;

		while ($access = $accessQuery->fetch())
		{
			if (!isset($rooms[$access['SECT_ID']]['ACCESS']))
			{
				$rooms[$access['SECT_ID']]['ACCESS'] = [];
			}
			$rooms[$access['SECT_ID']]['ACCESS'][$access['ACCESS_CODE']] = (int)$access['TASK_ID'];
		}

		return $rooms;
	}
}