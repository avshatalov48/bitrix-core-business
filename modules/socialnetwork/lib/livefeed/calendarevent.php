<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

final class CalendarEvent extends Provider
{
	const PROVIDER_ID = 'CALENDAR';
	const CONTENT_TYPE_ID = 'CALENDAR_EVENT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('calendar');
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
		return $provider;
	}

	public function initSourceFields()
	{
		$calendarEventId = $this->entityId;

		if (
			$calendarEventId > 0
			&& Loader::includeModule('calendar')
		)
		{
			$res = \CCalendarEvent::getList(
				array(
					'arFilter' => array(
						"ID" => $calendarEventId,
					),
					'parseRecursion' => false,
					'fetchAttendees' => false,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);

			$calendarEvent = is_array($res[0]) && is_array($res[0]) ? $res[0] : array();
			if (!empty($calendarEvent))
			{

				$this->setSourceFields($calendarEvent);
				$this->setSourceDescription($calendarEvent['DESCRIPTION']);
				$this->setSourceTitle($calendarEvent['NAME']);
				$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($calendarEventId));
				$this->setSourceDiskObjects($this->getDiskObjects($calendarEventId, $this->cloneDiskObjects));
			}
		}
	}

	public function getLiveFeedUrl()
	{
		$pathToCalendarEvent = '';
		$userPage = Option::get('socialnetwork', 'user_page', '', SITE_ID);
		if (
			!empty($userPage)
			&& ($calendarEvent = $this->getSourceFields())
			&& !empty($calendarEvent)
		)
		{
			$pathToCalendarEvent = \CComponentEngine::makePathFromTemplate($userPage."user/#user_id#/calendar/?EVENT_ID=#event_id#/", array(
				"user_id" => $calendarEvent["CREATED_BY"],
				"event_id" => $calendarEvent["ID"]
			));
		}

		return $pathToCalendarEvent;
	}
}