<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class CalendarEvent extends Provider
{
	public const PROVIDER_ID = 'CALENDAR';
	public const CONTENT_TYPE_ID = 'CALENDAR_EVENT';

	protected static $calendarEventClass = \CCalendarEvent::class;

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'calendar' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getCommentProvider(): Provider
	{
		return new ForumPost();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$calendarEventId = $this->entityId;

		if ($calendarEventId <= 0)
		{
			return;
		}

		$calendarEvent = [];

		if (isset($cache[$calendarEventId]))
		{
			$calendarEvent = $cache[$calendarEventId];
		}
		elseif (Loader::includeModule('calendar'))
		{
			$res = self::$calendarEventClass::getList(
				[
					'arFilter' => [
						"ID" => $calendarEventId,
					],
					'parseRecursion' => false,
					'fetchAttendees' => false,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				]
			);

			$calendarEvent = is_array($res) && is_array($res[0]) ? $res[0] : [];
			$cache[$calendarEventId] = $calendarEvent;
		}

		if (empty($calendarEvent))
		{
			return;
		}

		$this->setSourceFields($calendarEvent);
		$this->setSourceDescription($calendarEvent['DESCRIPTION']);
		$this->setSourceTitle($calendarEvent['NAME']);
		$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($this->cloneDiskObjects));
		$this->setSourceDiskObjects($this->getDiskObjects($calendarEventId, $this->cloneDiskObjects));
	}

	public function getPinnedTitle(): string
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$calendarEvent = $this->getSourceFields();
		if (empty($calendarEvent))
		{
			return $result;
		}

		return Loc::getMessage('SONET_LIVEFEED_CALENDAR_EVENT_PINNED_TITLE', [
			'#TITLE#' => $calendarEvent['NAME']
		]);
	}

	public function getLiveFeedUrl(): string
	{
		$pathToCalendarEvent = '';
		$userPage = Option::get('socialnetwork', 'user_page', '', SITE_ID);
		if (
			!empty($userPage)
			&& ($calendarEvent = $this->getSourceFields())
			&& !empty($calendarEvent)
		)
		{
			$pathToCalendarEvent = \CComponentEngine::makePathFromTemplate($userPage."user/#user_id#/calendar/?EVENT_ID=#event_id#", [
				"user_id" => $calendarEvent["CREATED_BY"],
				"event_id" => $calendarEvent["ID"]
			]);
		}

		return $pathToCalendarEvent;
	}
}
