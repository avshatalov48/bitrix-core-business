<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab;

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabAddEvent;

final class CollabEvent
{
	private const LOG_MARKER = 'DEBUG_CALENDAR_SOCNET_COLLAB_ADD';

	private static ?Logger $logger = null;

	/**
	 * Used as handler for collab creating event socialnetwork::onCollabAdd
	 *
	 * @param CollabAddEvent $event
	 */
	public static function onCollabAdd($event): void
	{
		try
		{
			$collabId = $event->getCollab()->getId();
			$calendarType = Dictionary::CALENDAR_TYPE['group'];

			if (self::isSectionExist($collabId, $calendarType))
			{
				return;
			}

			\CCalendarSect::CreateDefault(array(
				'type' => $calendarType,
				'ownerId' => $collabId,
			));
		}
		catch (\Throwable $e)
		{
			// not break collab creation flow
			self::getLogger()->log($e);
		}

	}

	private static function getLogger(): Logger
	{
		self::$logger ??= new Logger(self::LOG_MARKER);

		return self::$logger;
	}

	private static function isSectionExist($collabId, $calendarType): bool
	{
		return (bool)SectionTable::query()
			->setSelect(['ID'])
			->where('CAL_TYPE', $calendarType)
			->where('OWNER_ID', $collabId)
			->setLimit(1)
			->exec()
			->fetch()
		;
	}
}
