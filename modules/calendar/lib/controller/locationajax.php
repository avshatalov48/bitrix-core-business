<?php

namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Rooms;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Controller;
use Bitrix\Calendar\UserSettings;
use CCalendar;
use CCalendarType;
use Bitrix\Intranet;

Loc::loadMessages(__FILE__);

class LocationAjax extends Controller
{
	const TYPE = 'location';

	public function configureActions()
	{
		return [
			'createRoom' => [
				'+prefilters' => [
					new Intranet\ActionFilter\IntranetUser(),
				]
			],
			'updateRoom' => [
				'+prefilters' => [
					new Intranet\ActionFilter\IntranetUser(),
				]
			],
			'deleteRoom' => [
				'+prefilters' => [
					new Intranet\ActionFilter\IntranetUser(),
				]
			],
			'getRoomsManagerData' => [
				'+prefilters' => [
					new Intranet\ActionFilter\IntranetUser(),
				]
			],
			'getRoomsList' => [
				'+prefilters' => [
					new Intranet\ActionFilter\IntranetUser(),
				]
			],
			'setUserOption' => [
				'+prefilters' => [
					new Intranet\ActionFilter\IntranetUser(),
				]
			],
		];
	}

	public function createRoomAction()
	{
		$request = $this->getRequest();
		$response = [];
		$name = Rooms\Manager::checkRoomName($request->getPost('name'));
		$userId = CCalendar::GetCurUserId();
		$canDo = CCalendarType::CanDo('calendar_type_edit', 'location', $userId);
		$isEnable = Bitrix24Manager::isFeatureEnabled("calendar_location");

		if(!$name || !$canDo || !$isEnable)
		{
			$this->addError(
				new Error(Loc::getMessage('EC_CALENDAR_SAVE_ERROR'), 'saving_error_05')
			);
		}

		else
		{
			$color = CCalendar::Color($request->getPost('color'));
			$capacity = (int)$request->getPost('capacity');
			$necessity = $request->getPost('necessity') === 'Y' ? 'Y' : 'N';
			$id =  Rooms\Manager::createRoom(
				[
					'NAME' => $name,
					'NECESSITY' => $necessity,
					'CAPACITY' => $capacity,
					'COLOR' => $color,
					'OWNER_ID' => $request->getPost('ownerId'),
					'CAL_TYPE' => self::TYPE,
					'ACCESS' => $request->getPost('access')
				]
			);

			if ($id)
			{
				$response['room'] = Rooms\Manager::getRoomById($id)[0];
				if (!$response['room'])
				{
					$this->addError(
						new Error(Loc::getMessage('EC_ROOM_SAVE_ERROR'))
					);
				}
				$response['rooms'] = Rooms\Manager::getRoomsList();
				$sectionList = CCalendar::GetSectionList(
					[
						'CAL_TYPE' => self::TYPE,
						'OWNER_ID' => 0,
						'checkPermissions' => true,
						'getPermissions' => true,
						'getImages' => true
					]);
				$sectionList = array_merge($sectionList, \CCalendar::getSectionListAvailableForUser($userId));
				$response['sections'] = $sectionList;
				$response['accessNames'] = CCalendar::GetAccessNames();
			}
			else
			{
				$this->addError(
					new Error(Loc::getMessage('EC_ROOM_SAVE_ERROR'))
				);
			}
		}

		return $response;
	}

	public function updateRoomAction()
	{
		$request = $this->getRequest();
		$response = [];
		$request_id = (int)$request->getPost('id');
		$name = Rooms\Manager::checkRoomName($request->getPost('name'));
		$userId = CCalendar::GetCurUserId();
		$canDo = CCalendarType::CanDo('calendar_type_edit', 'location', $userId);
		$isEnable = Bitrix24Manager::isFeatureEnabled("calendar_location");
		
		if(!$name || !$canDo || !$isEnable)
		{
			$this->addError(
				new Error(Loc::getMessage('EC_CALENDAR_SAVE_ERROR'), 'saving_error_05')
			);
		}

		else
		{
			$color = CCalendar::Color($request->getPost('color'));
			$capacity = (int)$request->getPost('capacity');
			$necessity = $request->getPost('necessity') === 'Y' ? 'Y' : 'N';
			$id = Rooms\Manager::updateRoom(
				[
					'ID' => $request_id,
					'LOCATION_ID' => $request->getPost('location_id'),
					'NAME' => $name,
					'NECESSITY' => $necessity,
					'CAPACITY' => $capacity,
					'COLOR' => $color,
					'ACCESS' => $request->getPost('access')
				]
			);

			if ($id)
			{
				$response['room'] = Rooms\Manager::getRoomById($id)[0];
				if (!$response['room'])
				{
					$this->addError(
						new Error(Loc::getMessage('EC_ROOM_SAVE_ERROR'))
					);
				}
				$response['rooms'] = Rooms\Manager::getRoomsList();
				$sectionList = CCalendar::GetSectionList(
					[
						'CAL_TYPE' => self::TYPE,
						'OWNER_ID' => 0,
						'checkPermissions' => true,
						'getPermissions' => true,
						'getImages' => true
					]);
				$sectionList = array_merge($sectionList, \CCalendar::getSectionListAvailableForUser($userId));
				$response['sections'] = $sectionList;
				$response['accessNames'] = CCalendar::GetAccessNames();
			}
			else
			{
				$this->addError(
					new Error(Loc::getMessage('EC_ROOM_SAVE_ERROR'))
				);
			}
		}

		return $response;
	}

	public function deleteRoomAction()
	{
		$request = $this->getRequest();
		$response = [];
		$userId = CCalendar::GetCurUserId();
		$canDo = CCalendarType::CanDo('calendar_type_edit', 'location', $userId);
		$isEnable = Bitrix24Manager::isFeatureEnabled("calendar_location");
		
		if(!$canDo || !$isEnable)
		{
			$this->addError(
				new Error(Loc::getMessage('EC_ROOM_DELETE_ERROR'))
			);
		}

		else
		{
			$isSuccess = Rooms\Manager::deleteRoom(
				[
					'ID' => $request->getPost('id'),
					'LOCATION_ID' => $request->getPost('location_id')
				]
			);
			if($isSuccess)
			{
				$response['rooms'] = Rooms\Manager::getRoomsList();
				$sectionList = CCalendar::GetSectionList(
					[
						'CAL_TYPE' => self::TYPE,
						'OWNER_ID' => 0,
						'checkPermissions' => true,
						'getPermissions' => true,
						'getImages' => true
					]);
				$sectionList = array_merge($sectionList, \CCalendar::getSectionListAvailableForUser($userId));
				$response['sections'] = $sectionList;
				$response['accessNames'] = CCalendar::GetAccessNames();
			}
			else
			{
				$this->addError(
					new Error(Loc::getMessage('EC_ROOM_DELETE_ERROR'))
				);
			}
		}

		return $response;
	}

	public function getRoomsManagerDataAction()
	{
		$response = [];
		$userId = CCalendar::GetCurUserId();
		$canDo = CCalendarType::CanDo('calendar_type_view', 'location', $userId);

		if(!$canDo)
		{
			$this->addError(
				new Error(Loc::getMessage('EC_ACCESS_DENIED'))
			);
		}
		else
		{
			$followedSectionList = UserSettings::getFollowedSectionIdList($userId);
			$type = self::TYPE;
			$sectionList = CCalendar::GetSectionList(
				[
					'CAL_TYPE' => $type,
					'OWNER_ID' => 0,
					'ADDITIONAL_IDS' => $followedSectionList,
				]
			);
			$sectionList = array_merge($sectionList, CCalendar::getSectionListAvailableForUser($userId));

			$sectionAccessTasks = CCalendar::GetAccessTasks('calendar_section', 'location');
			$hiddenSections = UserSettings::getHiddenSections(
				$userId,
				[
					'type' => $type,
					'ownerId' => 0,
				]
			);
			$defaultSectionAccess = \CCalendarSect::GetDefaultAccess(
				self::TYPE,
				$userId
			);
			$response['rooms'] = Rooms\Manager::getRoomsList();
			$response['sections'] = $sectionList;
			$response['config'] =
				[
					'locationAccess' => \CCalendarType::CanDo('calendar_type_edit', 'location'),
					'hiddenSections' => $hiddenSections,
					'type' => $type,
					'ownerId' => 0,
					'userId' => $userId,
					'defaultSectionAccess' => $defaultSectionAccess,
					'sectionAccessTasks' => $sectionAccessTasks,
					'showTasks' => false
				];
		}

		return $response;
	}

	public function getRoomsListAction()
	{
		$response = [];
		$userId = CCalendar::GetCurUserId();
		$canDo = CCalendarType::CanDo('calendar_type_view', 'location', $userId);
		$isEnable = Bitrix24Manager::isFeatureEnabled("calendar_location");
		
		if(!$canDo || !$isEnable)
		{
			$this->addError(
				new Error(Loc::getMessage('EC_ACCESS_DENIED'))
			);
		}
		else
		{
			return ['rooms' => Rooms\Manager::getRoomsList()];
		}

		return $response;
	}

	public function hideSettingsHintLocationAction()
	{
		$request = $this->getRequest();
		$value = $request->getPost('value') === 'true';
		return \CUserOptions::SetOption("calendar", "hideSettingsHintLocation", $value);
	}
}
