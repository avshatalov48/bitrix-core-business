<?php

namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\SectionAccessController;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Rooms;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Controller;
use CCalendar;

Loc::loadMessages(__FILE__);

class LocationAjax extends Controller
{
	public const TYPE = 'location';

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function createRoomAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (
			!$accessController->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, [])
			|| !Rooms\PermissionManager::isLocationFeatureEnabled()
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		$builder = new \Bitrix\Calendar\Core\Builders\Rooms\RoomBuilderFromRequest($this->getRequest());
		$manager =
			Rooms\Manager::createInstanceWithRoom($builder->build())
				->createRoom()
				->saveAccess()
				->clearCache()
				->eventHandler('OnAfterCalendarRoomCreate')
				->addPullEvent(PushCommand::CreateRoom)
		;

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return $manager->prepareResponseData();
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateRoomAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$sectionId = (int)$this->getRequest()->getPost('id');
		$section = \CCalendarSect::GetById($sectionId);

		if (empty($section))
		{
			$this->addError(new Error(Loc::getMessage('EC_ROOM_DELETE_ERROR')));

			return [];
		}

		$sectionModel = SectionModel::createFromArray($section);
		$accessController = new SectionAccessController(CCalendar::GetUserId());
		if (
			!$accessController->check(ActionDictionary::ACTION_SECTION_EDIT, $sectionModel, [])
			|| !Rooms\PermissionManager::isLocationFeatureEnabled()
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		$builder = new \Bitrix\Calendar\Core\Builders\Rooms\RoomBuilderFromRequest($this->getRequest());
		$manager =
			Rooms\Manager::createInstanceWithRoom($builder->build())
				->updateRoom()
				->saveAccess()
				->clearCache()
				->eventHandler('OnAfterCalendarRoomUpdate')
				->addPullEvent(PushCommand::UpdateRoom)
		;

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return $manager->prepareResponseData();
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function deleteRoomAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$sectionId = (int)$this->getRequest()->getPost('id');
		$section = \CCalendarSect::GetById($sectionId);

		if (empty($section))
		{
			$this->addError(new Error(Loc::getMessage('EC_ROOM_DELETE_ERROR')));

			return [];
		}

		$sectionModel = SectionModel::createFromArray($section);
		$accessController = new SectionAccessController(CCalendar::GetUserId());
		if (
			!$accessController->check(ActionDictionary::ACTION_SECTION_EDIT, $sectionModel, [])
			|| !Rooms\PermissionManager::isLocationFeatureEnabled()
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));

			return [];
		}

		$builder = new \Bitrix\Calendar\Core\Builders\Rooms\RoomBuilderFromRequest($this->getRequest());
		$manager =
			Rooms\Manager::createInstanceWithRoom($builder->build())
				->deleteRoom()
				->pullDeleteEvents()
				->deleteEmptyEvents()
				->deleteLocationFromEvents()
				->cleanAccessTable()
				->clearCache()
				->eventHandler('OnAfterCalendarRoomDelete')
				->addPullEvent(PushCommand::DeleteRoom)
		;

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return $manager->prepareResponseData();
	}

	/**
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getRoomsManagerDataAction(): ?array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (!$accessController->check(ActionDictionary::ACTION_TYPE_VIEW, $typeModel, []))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		return Rooms\Manager::prepareRoomManagerData();
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getRoomsListAction(): array
	{
		$result = [];
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return $result;
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (!$accessController->check(ActionDictionary::ACTION_TYPE_VIEW, $typeModel, []))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return $result;
		}

		$result['rooms'] = Rooms\Manager::getRoomsList();

		return $result;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function getLocationAccessibilityAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		if (Loader::includeModule('pull'))
		{
			$userId = \CCalendar::GetUserId();

			\CPullWatch::Add($userId, 'calendar-location', true);
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (!$accessController->check(ActionDictionary::ACTION_TYPE_VIEW, $typeModel, []))
		{
			return [];
		}

		$request = $this->getRequest();

		return
			Rooms\AccessibilityManager::createInstance()
				->setLocationList($request->getPost('locationList'))
				->setDatesRange($request->getPost('datesRange'))
				->getLocationAccessibility()
			;
	}

	/**
	 * @return void
	 */
	public function hideSettingsHintLocationAction(): void
	{
		$request = $this->getRequest();
		$value = ($request->getPost('value') === 'true');

		\CUserOptions::SetOption(
			'calendar',
			'hideSettingsHintLocation',
			$value
		);
	}

	public function cancelBookingAction(): ?array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$request = $this->getRequest();

		$params['recursion_mode'] = $request->getPost('recursion_mode');
		$params['parent_event_id'] = (int)$request->getPost('parent_event_id');
		$params['section_id'] = (int)$request->getPost('section_id');
		$params['current_event_date_from'] = $request->getPost('current_event_date_from');
		$params['current_event_date_to'] = $request->getPost('current_event_date_to');
		$params['owner_id'] = (int)$request->getPost('owner_id');

		/** @var SectionModel $sectionModel */
		$sectionModel =
			SectionModel::createFromId($params['section_id'])
				->setType(Dictionary::CALENDAR_TYPE['location'])
		;
		$accessController = new SectionAccessController(CCalendar::GetUserId());
		if (!$accessController->check(ActionDictionary::ACTION_SECTION_ACCESS, $sectionModel, []))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		$manager =
			Rooms\Manager::createInstance()
			->cancelBooking($params)
		;

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return [];
	}

	public function createCategoryAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (
			!$accessController->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, [])
			|| !Rooms\PermissionManager::isLocationFeatureEnabled()
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		$builder = new \Bitrix\Calendar\Core\Builders\Rooms\Categories\CategoryBuilderFromRequest($this->getRequest());
		$manager =
			Rooms\Categories\Manager::createInstance($builder->build())
				->createCategory()
				->clearCache()
				->addPullEvent(PushCommand::CreateCategory)
		;

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return Rooms\Categories\Manager::getCategoryList();
	}

	public function updateCategoryAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (
			!$accessController->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, [])
			|| !Rooms\PermissionManager::isLocationFeatureEnabled()
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		$builder = new \Bitrix\Calendar\Core\Builders\Rooms\Categories\CategoryBuilderFromRequest($this->getRequest());
		$manager =
			Rooms\Categories\Manager::createInstance($builder->build())
				->updateCategory()
				->clearCache()
				->addPullEvent(PushCommand::UpdateCategory)
		;

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return Rooms\Categories\Manager::getCategoryList();
	}

	public function deleteCategoryAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (
			!$accessController->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, [])
			|| !Rooms\PermissionManager::isLocationFeatureEnabled()
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		$builder = new \Bitrix\Calendar\Core\Builders\Rooms\Categories\CategoryBuilderFromRequest($this->getRequest());
		$manager =
			Rooms\Categories\Manager::createInstance($builder->build())
				->deleteCategory()
				->clearCache()
				->addPullEvent(PushCommand::DeleteCategory)
		;

		if ($manager->getError())
		{
			$this->addError(
				$manager->getError()
			);
		}

		return Rooms\Categories\Manager::getCategoryList();
	}

	public function getCategoryListAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		if (!$accessController->check(ActionDictionary::ACTION_TYPE_VIEW, $typeModel, []))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED')));
			return [];
		}

		$response = [];
		$response['categories'] = Rooms\Categories\Manager::getCategoryList();

		return $response;
	}

	public function getCategoryManagerDataAction(): array
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$categoryManagerData = [];
		$categoryManagerData['permissions'] = Rooms\PermissionManager::getAvailableOperations() ?? [];
		$categoryManagerData['categories'] = Rooms\Categories\Manager::getCategoryList() ?? [];

		return $categoryManagerData;
	}
}
