<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Main\Error;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Internals\AccessTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\LocationTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class Manager
{
	const TYPE = 'location';
	
	/** @var Room $room */
	private $room;
	/** @var Error $error */
	private $error;
	
	protected function __construct()
	{
	}
	
	public static function createInstanceWithRoom(Room $room): Manager
	{
		$instance = new self();
		$instance->setRoom($room);
		return $instance;
	}
	
	public static function createInstance(): Manager
	{
		return new self;
	}
	
	private function setRoom(Room $room)
	{
		$this->room = $room;
	}
	
	public function setLocationList(array $locationList): Manager
	{
		$this->locationList = $locationList;
		
		return $this;
	}

	private function addError(Error $error)
	{
		$this->error = $error;
	}
	
	public function getRoom(): Room
	{
		return $this->room;
	}

	public function getError(): ?Error
	{
		return $this->error;
	}
	
	public function getLocationList(): ?array
	{
		return $this->locationList;
	}
	
	/**
	 * Creating Room in Location Calendar
	 *
	 * @return Manager
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
	 * @return array of rooms in Location calendar
	 */
	public static function getRoomsList(): ?array
	{
		$rooms = SectionTable::getList([
			'select' => [
				'ID',
				'NAME',
				'COLOR',
				'OWNER_ID',
				'CAL_TYPE',
				'NECESSITY' => 'LOCATION.NECESSITY',
				'CAPACITY' => 'LOCATION.CAPACITY',
				'LOCATION_ID' => 'LOCATION.ID',
				'ACCESS_CODE' => 'ACCESS_TABLE.ACCESS_CODE',
				'TASK_ID' => 'ACCESS_TABLE.TASK_ID',
			],
			'runtime' => [
				new ReferenceField(
					'LOCATION',
					LocationTable::class, ['=this.ID' => 'ref.SECTION_ID'],
					['join_type' => 'INNER']
				),
				new ReferenceField(
					'ACCESS_TABLE',
					AccessTable::class, ['=this.ID' => 'ref.SECT_ID'],
					['join_type' => 'INNER']
				)
			],
			'order' => [
				'ID'
			],
	    ])->fetchAll();

		if (empty($rooms))
		{
			\CCalendarSect::CreateDefault([
				'type' => self::TYPE,
				'ownerId' => 0
			]);
			
			return null;
		}
		else
		{
			$rooms = self::setAccess($rooms);
			foreach ($rooms as $item)
			{
				\CCalendarSect::HandlePermission($item);
			}
			
			return \CCalendarSect::GetSectionPermission($rooms);
		}
	}

	/**
	 * @param $id
	 *
	 * @return array room by section id
	 */
	public static function getRoomById($id): array
	{
		$room = SectionTable::getList([
			'filter' => [
				'=ID' => $id,
			],
			'select' => [
				'ID',
				'NAME',
				'COLOR',
				'OWNER_ID',
				'CAL_TYPE',
				'NECESSITY' => 'LOCATION.NECESSITY',
				'CAPACITY' => 'LOCATION.CAPACITY',
				'LOCATION_ID' => 'LOCATION.ID',
				'ACCESS_CODE' => 'ACCESS_TABLE.ACCESS_CODE',
				'TASK_ID' => 'ACCESS_TABLE.TASK_ID',
			],
			'runtime' => [
				new ReferenceField(
					'LOCATION',
					LocationTable::class, ['=this.ID' => 'ref.SECTION_ID'],
					['join_type' => 'INNER']
				),
				new ReferenceField(
					'ACCESS_TABLE',
					AccessTable::class, ['=this.ID' => 'ref.SECT_ID'],
					['join_type' => 'INNER']
				)
			],
			'order' => [
				'ID'
			],
		])->fetchAll();

		$room = self::setAccess($room);
		foreach ($room as $item)
		{
			\CCalendarSect::HandlePermission($item);
		}

		return \CCalendarSect::GetSectionPermission($room);
	}

	/**
	 * @param array $params
	 *
	 * @return int|null id of new event
	 */
	public static function reserveRoom(array $params = []): ?int
	{
		$name = self::createInstance()->getRoomName($params['room_id']);
		if (empty($name))
		{
			return null;
		}
		
		$createdBy = ($params['parentParams']['arFields']['CREATED_BY']
			?? $params['parentParams']['arFields']['MEETING_HOST']);
		$userId = $params['parentParams']['userId']
			??  $params['parentParams']['arFields']['userId'];

		return \CCalendarEvent::Edit([
			'arFields' => [
				'ID' => $params['room_event_id'],
				'CAL_TYPE' => self::TYPE,
				'SECTIONS' => $params['room_id'],
				'DATE_FROM' => $params['parentParams']['arFields']['DATE_FROM'],
				'DATE_TO' => $params['parentParams']['arFields']['DATE_TO'],
				'TZ_FROM' => $params['parentParams']['arFields']['TZ_FROM'],
				'TZ_TO' => $params['parentParams']['arFields']['TZ_TO'],
				'SKIP_TIME' => $params['parentParams']['arFields']['SKIP_TIME'],
				'NAME' => \CCalendar::GetUserName($userId),
				'RRULE' => $params['parentParams']['arFields']['RRULE'],
				'EXDATE' => $params['parentParams']['arFields']['EXDATE'],
				'CREATED_BY' => $createdBy
			],
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
		return \CCalendar::deleteEvent(
			(int)$params['room_event_id'],
			false,
			[
				'checkPermissions' => false,
				'markDeleted' => false
			]
		);
	}

	/**
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
		
		\CCalendarSect::CleanAccessTable();
		
		return $this;
	}

	/**
	 * @param int $id
	 *
	 * Setting id of new event in user calendar
	 * for event in location calendar
	 */
	public static function setEventIdForLocation(int $id)
	{
		$event = EventTable::getList([
			'filter' => [
				'=ID' => $id,
			],
			'select' => [
				'LOCATION',
			],
		])->fetch();

		if (!empty($event['LOCATION']))
		{
			$location = Util::parseLocation($event['LOCATION']);
			if ($location['room_id'] && $location['room_event_id'])
			{
				EventTable::update(
					$location['room_event_id'],
					[
						'PARENT_ID' => $id,
					]
				);
			}
		}
	}
	
	/**
	 * Preparing data with rooms and sections for ajax-actions
	 *
	 * @return array
	 */
	public function prepareResponseData(): array
	{
		$result = [];
		
		$result['rooms'] = Manager::getRoomsList();
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
	 */
	public function prepareRoomManagerData(): ?array
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
		
		$result['rooms'] = Manager::getRoomsList();
		$result['sections'] = $sectionList;
		$result['config'] = [
			'locationAccess' => \CCalendarType::CanDo('calendar_type_edit', 'location'),
			'hiddenSections' => $hiddenSections,
			'type' => self::TYPE,
			'ownerId' => 0,
			'userId' => $userId,
			'defaultSectionAccess' => $defaultSectionAccess,
			'sectionAccessTasks' => $sectionAccessTasks,
			'showTasks' => false
		];
		
		return $result;
	}
	
	/**
	 * @return Manager
	 */
	public function isEnableEdit(): Manager
	{
		$userId = \CCalendar::GetUserId();
		$canDo = \CCalendarType::CanDo('calendar_type_edit', 'location', $userId);
		$isEnable = Bitrix24Manager::isFeatureEnabled('calendar_location');
		
		if(!$canDo || !$isEnable)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
		}
		
		return $this;
	}
	
	/**
	 * @return Manager
	 */
	public function isEnableView(): Manager
	{
		$userId = \CCalendar::GetUserId();
		$canDo = \CCalendarType::CanDo('calendar_type_view', 'location', $userId);
		$isEnable = Bitrix24Manager::isFeatureEnabled('calendar_location');
		
		if(!$canDo || !$isEnable)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
		}
		
		return $this;
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
	 * @param $id int
	 *
	 * @return string|null
	 */
	private function getRoomName(int $id): ?string
	{
		$section =  SectionTable::getRow([
			'filter' => [
				'=ID' => $id,
			],
			'select' => [
				'NAME',
			],
		]);
		
		return $section['NAME'];
	}

	/**
	 * @param $name
	 * Validation for name of room
	 *
	 * @return string|null
	 */
	public static function checkRoomName(?string $name): ?string
	{
		$name = trim($name);
		
		if (empty($name))
		{
			return '';
		}
		
		return $name;
	}

	/**

	 * Delete location value when deleting room
	 */
	public function deleteLocationFromEvents(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}
		
		global $DB;
		$guestsId = [];
		$idTemp = "(#ID#, ''),";
		$updateString = '';
		$id = $this->room->getId();
		$locationName = $this->room->getName();
		$locationId = 'calendar_' . $id;

		$events = $DB->Query("
			SELECT ID, PARENT_ID, OWNER_ID, LOCATION
			FROM b_calendar_event
			WHERE LOCATION LIKE '" . $locationId . "%';
		");

		while ($event = $events->Fetch())
		{
			if ($event['ID'] === $event['PARENT_ID'])
			{
				$guestsId[] = $event['OWNER_ID'];
			}
			$updateString .= str_replace('#ID#', $event['ID'], $idTemp);
		}

		if ($updateString)
		{
			$updateString = substr($updateString, 0, -1);
			$DB->Query("
				INSERT INTO b_calendar_event (ID, LOCATION) 
				VALUES ".$updateString."
				ON DUPLICATE KEY UPDATE LOCATION = VALUES(LOCATION)
			");
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
	 * @return Manager
	 */
	public function pullDeleteEvents(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}
		
		$events = Manager::getLocationEventsId($this->room->getId());

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
	public function deleteEmptyEvents()
	{
		if ($this->getError())
		{
			return $this;
		}
		
		\CCalendarEvent::DeleteEmpty();
		return $this;
	}

	/**
	 * @param int $roomId
	 * @return array of location events id with a given id
	 */
	private static function getLocationEventsId(int $roomId): array
	{
		return EventTable::getList([
			'select' => [
				'ID',
				'CREATED_BY',
				'PARENT_ID'
			],
			'filter' => [
				'=SECTION_ID' => $roomId,
				'=DELETED' => 'N'
			]
		])->fetchAll();
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * Saving access into b_calendar_access
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
	 * @param $rooms
	 *  Creates the correct display of access field in rooms
	 *
	 *  If first making temperance array and adding access field
	 *  Else if next is not equal to past, pushing in result array and making new temperance
	 *  Else (if next is equal to past) pushing to existent access field
	 *  And at last checking if is last element and pushing to result
	 *
	 * @return array
	 */
	private static function setAccess($rooms): array
	{
		$length = count($rooms);
		$result = [];
		$tmp = [];
		
		for ($i = 0; $i < $length; $i++)
		{
			if ($i === 0)
			{
				$tmp = $rooms[$i];
				$tmp['ACCESS'] = [$rooms[$i]['ACCESS_CODE'] => $rooms[$i]['TASK_ID']];
			}
			elseif ($rooms[$i - 1]['ID'] !== $rooms[$i]['ID'])
			{
				unset($tmp['ACCESS_CODE'], $tmp['TASK_ID']);
				$result[] = $tmp;
				$tmp = $rooms[$i];
				$tmp['ACCESS'] = [$rooms[$i]['ACCESS_CODE'] => $rooms[$i]['TASK_ID']];
			}
			else
			{
				$tmp['ACCESS'] += [$rooms[$i]['ACCESS_CODE'] => $rooms[$i]['TASK_ID']];
			}
			
			if ($i === $length - 1)
			{
				unset($tmp['ACCESS_CODE'], $tmp['TASK_ID']);
				$result[] = $tmp;
			}
		}
		
		return $result;
	}
}