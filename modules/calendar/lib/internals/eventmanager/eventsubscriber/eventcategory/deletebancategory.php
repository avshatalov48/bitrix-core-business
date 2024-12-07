<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventCategory;

use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryAttendeesDelete;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Calendar\OpenEvents\Service\CategoryBanService;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class DeleteBanCategory implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		$categoryId = (int)$event->getParameter('eventCategoryId');
		$userIds = $event->getParameter('userIds');

		CategoryBanService::getInstance()->unbanCategoryMulti($categoryId, $userIds);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterEventCategoryAttendeesDelete::class,
		];
	}
}
