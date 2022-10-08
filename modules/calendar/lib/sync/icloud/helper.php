<?php

namespace Bitrix\Calendar\Sync\Icloud;

class Helper
{
	public const SERVER_PATH = 'https://caldav.icloud.com/';
	public const ACCOUNT_TYPE = 'icloud';
	public const CONNECTION_NAME = 'ICloud (#NAME#)';

	public const EXCLUDED_CALENDARS = [
		'inbox',
		'outbox',
		'notification',
		'tasks',
		'calendars',
	];

	/**
	 * @param string $accountType
	 * @return bool
	 */
	public function isVendorConnection(string $accountType): bool
	{
		return $accountType === self::ACCOUNT_TYPE;
	}
}