<?php

namespace Bitrix\Calendar\Access\Rule\Traits;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventCategoryAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Main\Access\AccessibleController;

trait CategoryTrait
{
	/* @var AccessibleController $controller */
	protected $controller;

	private function checkCategoryByEvent(
		EventModel $eventModel,
		string $action = ActionDictionary::ACTION_EVENT_CATEGORY_VIEW
	): bool
	{
		if (!($categoryId = $eventModel->getEventCategoryId()))
		{
			return true;
		}

		return EventCategoryAccessController::can(
			userId: $this->controller->getUser()->getUserId(),
			action: $action,
			itemId: $categoryId,
		);
	}
}
