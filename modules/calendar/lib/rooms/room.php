<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\LocationTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Main\EventManager;
use Bitrix\Main\Type\DateTime;
use CCalendar;
use CCalendarEvent;
use CCalendarSect;

class Room
{
	/**
	 * @param $params
	 * Creating Room in Location Calendar
	 *
	 * @return int|null id of created room
	 */
	public function create($params): ?int
	{
		if(!isset($params['CREATED_BY']))
		{
			$params['CREATED_BY'] = CCalendar::GetCurUserId();
		}
		$section = SectionTable::add(
			[
				'CAL_TYPE' => $params['CAL_TYPE'],
				'NAME' => $params['NAME'],
				'COLOR' => $params['COLOR'],
				'OWNER_ID' => $params['OWNER_ID'],
				'SORT' => 100,
				'CREATED_BY' => $params['CREATED_BY'],
				'DATE_CREATE' => new DateTime(),
				'TIMESTAMP_X' => new DateTime(),
				'ACTIVE' => 'Y',
			]
		);
		if (!$section->isSuccess())
		{
			return null;
		}
		$sect_id = $section->getId();

		$location = LocationTable::add(
			[
				'SECTION_ID' => $sect_id,
				'NECESSITY' => $params['NECESSITY'],
				'CAPACITY' => $params['CAPACITY'],
			]
		);
		if (!$location->isSuccess())
		{
			SectionTable::delete($sect_id);

			return null;
		}

		$this->saveAccess($params, $sect_id);
		\CCalendarSect::SetClearOperationCache(true);
		Manager::clearCache();

		foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarRoomCreate") as $event)
		{
			ExecuteModuleEventEx($event, array($sect_id, $params));
		}
		\Bitrix\Calendar\Util::addPullEvent(
			'create_room',
			$params['CREATED_BY'],
			[
				'fields' => $params
			]
		);

		return $sect_id;
	}

	/**
	 * @param $params
	 *
	 * Updating data of room in Location calendar
	 *
	 * @return mixed|null id of updated room
	 */
	public function update($params): ?int
	{
		$params['CREATED_BY'] = CCalendar::GetCurUserId();
		$section = SectionTable::update(
			$params['ID'],
			[
				'NAME' => $params['NAME'],
				'COLOR' => $params['COLOR'],
				'TIMESTAMP_X' => new DateTime(),
			]
		);
		if (!$section->isSuccess())
		{
			return null;
		}

		$location = LocationTable::update(
			$params['LOCATION_ID'],
			[
				'NECESSITY' => $params['NECESSITY'],
				'CAPACITY' => $params['CAPACITY'],
			]
		);
		if (!$location->isSuccess())
		{
			return null;
		}

		$sect_id = $section->getId();
		$this->saveAccess($params, $sect_id);
		\CCalendarSect::SetClearOperationCache(true);
		Manager::clearCache();

		foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarRoomUpdate") as $event)
		{
			ExecuteModuleEventEx($event, array($sect_id, $params));
		}
		\Bitrix\Calendar\Util::addPullEvent(
			'update_room',
			$params['CREATED_BY'],
			[
				'fields' => $params
			]
		);

		return $sect_id;
	}

	/**
	 * @param $id
	 *
	 * Deleting room by id in Location calendar
	 *
	 * @return bool true if successful
	 */
	public function delete($params): bool
	{
		$locationName = Manager::getRoomName($params['ID']);
		$section = SectionTable::delete($params['ID']);
		$params['CREATED_BY'] = CCalendar::GetCurUserId();
		if (!$section->isSuccess())
		{
			return false;
		}

		$location = LocationTable::delete($params['LOCATION_ID']);
		if (!$location->isSuccess())
		{
			return false;
		}

		$eventsId = EventTable::getList([
			'select' =>
				[
					'ID',
					'CREATED_BY',
					'PARENT_ID'
				],
			'filter' =>
				[
					'=SECTION_ID' => $params['ID'],
					'=DELETED' => 'N'
				]
										])->fetchAll();

		foreach($eventsId as $event)
		{
			if ($params['CREATED_BY'])
			{
				\Bitrix\Calendar\Util::addPullEvent(
					'delete_event',
					$params['CREATED_BY'],
					[
						'fields' => $event
					]
				);
			}
		}
		CCalendarEvent::DeleteEmpty();
		Manager::deleteLocationFromEvent($params['ID'], $locationName['NAME']);
		CCalendarSect::SetClearOperationCache(true);
		CCalendarSect::CleanAccessTable();
		Manager::clearCache();

		foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarRoomDelete") as $event)
		{
			ExecuteModuleEventEx($event, array($params['ID']));
		}

		\Bitrix\Calendar\Util::addPullEvent(
			'delete_room',
			$params['CREATED_BY'],
			[
				'fields' => $params
			]
		);

		return true;
	}

	/**
	 * @param $params
	 * @param $id
	 *
	 * Saving access into b_calendar_access
	 */
	private function saveAccess($params, $id)
	{
		if ($id > 0 && !isset($params['ACCESS']))
		{
			\CCalendarSect::SavePermissions(
				$id,
				\CCalendarSect::GetDefaultAccess(
					$params['CAL_TYPE'],
					$params['CREATED_BY']
				)
			);
		}
		else if ($id > 0 && !empty($params['ACCESS']))
		{
			CCalendarSect::SavePermissions(
				$id,
				$params['ACCESS']
			);
		}
	}
}