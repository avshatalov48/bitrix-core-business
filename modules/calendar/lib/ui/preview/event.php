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

	protected static function getUser()
	{
		global $USER;

		return $USER;
	}
}