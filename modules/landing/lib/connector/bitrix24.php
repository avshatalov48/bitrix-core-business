<?php
namespace Bitrix\Landing\Connector;

use \Bitrix\Landing\Restriction;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;

Loc::loadMessages(__FILE__);

class Bitrix24
{
	/**
	 * @param Event $event Event instance.
	 * @return EventResult
	 */
	public static function onAfterPortalBlockedByLicenseScanner(Event $event): EventResult
	{
		$res = Restriction\Site::unpublishByScannerLockPortal();

		if ($res)
		{
			return new EventResult(EventResult::SUCCESS);
		}

		return new EventResult(EventResult::ERROR);
	}
}