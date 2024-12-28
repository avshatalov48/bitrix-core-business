<?php

namespace Bitrix\Calendar\FileUploader;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\UI\FileUploader\Configuration;
use Bitrix\UI\FileUploader\FileOwnershipCollection;
use Bitrix\UI\FileUploader\UploaderController;

class EventController extends UploaderController
{
	public function __construct(array $options)
	{
		$options['eventId'] = (int)($options['eventId'] ?? 0);

		parent::__construct($options);
	}

	public function isAvailable(): bool
	{
		return $this->hasRights(ActionDictionary::ACTION_EVENT_VIEW_FULL);
	}

	public function getConfiguration(): Configuration
	{
		return new Configuration();
	}

	public function canUpload()
	{
		return $this->hasRights(ActionDictionary::ACTION_EVENT_EDIT);
	}

	public function canView(): bool
	{
		return $this->hasRights(ActionDictionary::ACTION_EVENT_VIEW_FULL);
	}

	public function verifyFileOwner(FileOwnershipCollection $files): void
	{
	}

	public function canRemove(): bool
	{
		return $this->hasRights(ActionDictionary::ACTION_EVENT_EDIT);
	}

	private function hasRights(string $action): bool
	{
		$eventId = $this->getOption('eventId', 0);

		if (empty($eventId))
		{
			return true;
		}

		$userId = \CCalendar::getCurUserId();
		$eventModel = \CCalendarEvent::getEventModelForPermissionCheck($eventId, [], $userId);

		return (new EventAccessController($userId))->check($action, $eventModel);
	}
}
