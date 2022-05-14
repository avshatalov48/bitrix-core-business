<?php

namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Rooms;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Controller;

Loc::loadMessages(__FILE__);

class LocationAjax extends Controller
{
	const TYPE = 'location';
	
	public function createRoomAction(): array
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
		$request = $this->getRequest();
		
		$room = Rooms\Room::createInstanceFromRequest($request);
		
		$manager = Rooms\Manager::createInstanceWithRoom($room)
			->isEnableEdit()
			->createRoom()
			->saveAccess()
			->clearCache()
			->eventHandler('OnAfterCalendarRoomCreate')
			->addPullEvent('create_room');
		
		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}
		
		return $manager->prepareResponseData();
	}

	public function updateRoomAction(): array
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
		$request = $this->getRequest();
		
		$room = Rooms\Room::createInstanceFromRequest($request);
		
		$manager = Rooms\Manager::createInstanceWithRoom($room)
			->isEnableEdit()
			->updateRoom()
			->saveAccess()
			->clearCache()
			->eventHandler('OnAfterCalendarRoomUpdate')
			->addPullEvent('update_room');
		
		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}
		
		return $manager->prepareResponseData();
	}

	public function deleteRoomAction(): array
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
		$request = $this->getRequest();
		
		$room = Rooms\Room::createInstanceFromRequest($request);
		
		$manager = Rooms\Manager::createInstanceWithRoom($room)
			->isEnableEdit()
			->deleteRoom()
			->pullDeleteEvents()
			->deleteEmptyEvents()
			->deleteLocationFromEvents()
			->cleanAccessTable()
			->clearCache()
			->eventHandler('OnAfterCalendarRoomDelete')
			->addPullEvent('delete_room');
		

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return $manager->prepareResponseData();
	}

	public function getRoomsManagerDataAction(): ?array
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
		$manager = Rooms\Manager::createInstance()->isEnableView();
		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}
		
		return $manager->prepareRoomManagerData();
	}

	public function getRoomsListAction(): array
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
		$response = [];
		$manager = Rooms\Manager::createInstance()->isEnableView();
		
		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		$response['rooms'] = Rooms\Manager::getRoomsList();
		return $response;
	}
	
	public function getLocationAccessibilityAction(): array
	{
		$request = $this->getRequest();
		
		return Rooms\AccessibilityManager::createInstance()
			->setLocationList($request->getPost('locationList'))
			->setDatesRange($request->getPost('datesRange'))
			->getLocationAccessibility();
	}

	public function hideSettingsHintLocationAction()
	{
		$request = $this->getRequest();
		$value = ($request->getPost('value') === 'true');
		
		return \CUserOptions::SetOption(
			'calendar',
			'hideSettingsHintLocation',
			$value
		);
	}
}
