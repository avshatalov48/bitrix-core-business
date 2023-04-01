<?php

namespace Bitrix\Calendar\Ui\Preview;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class Event
{
	public static function buildPreview(array $params)
	{
		global $APPLICATION;
		$eventId = (int)$params['eventId'];
		if(!$eventId)
		{
			return '';
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:calendar.event.preview',
			'',
			$params
		);
		return ob_get_clean();
	}

	public static function checkUserReadAccess(array $params): bool
	{
		$eventId = (int)$params['eventId'];
		if(
			!$eventId
			|| !Loader::includeModule('calendar')
		)
		{
			return false;
		}

		$events = \CCalendarEvent::getList(
			[
				'arFilter' => [
					'ID' => $eventId,
					'DELETED' => false,
				],
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			]
		);

		return ($events && is_array($events[0]));
	}

	public static function getImAttach(array $params)
	{
		return false;
	}

	public static function getImRich(array $params)
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		if (!class_exists('\Bitrix\Im\V2\Entity\Url\RichData'))
		{
			return false;
		}

		return (new \Bitrix\Im\V2\Entity\Url\RichData())->setType(\Bitrix\Im\V2\Entity\Url\RichData::CALENDAR_TYPE);
	}

	protected static function getUser()
	{
		global $USER;

		return $USER;
	}
}