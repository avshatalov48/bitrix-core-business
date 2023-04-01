<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\Date;

class Helper
{
	public const TYPE = 'office365';
	public const ACCOUNT_TYPE = 'office365';
	public const SERVER_PATH_V1 = 'https://graph.microsoft.com/v1.0/';
	public const SERVER_PATH = self::SERVER_PATH_V1;

	public const TIME_FORMAT_LONG = 'Y-m-d\TH:i:s.u';

	public const NEED_SCOPE = [
		'User.Read',
		'Calendars.ReadWrite',
		'offline_access'
	];

	public const DELTA_INTERVAL = [
		'from' => '-1 month',
		'to' => '+20 year',
	];

	public const EVENT_TYPES = [
		'single' => 'singleInstance',
		'series' => 'seriesMaster',
		'exception' => 'exception',
		'occurrence' => 'occurrence',
		'deleted' => 'deleted',
	];

	public const RECURRENCE_TYPES = [
		'daily' => 'daily',
		'weekly' => 'weekly',
		'absoluteMonthly' => 'absoluteMonthly',
		'relativeMonthly' => 'relativeMonthly',
		'absoluteYearly' => 'absoluteYearly',
		'relativeYearly' => 'relativeYearly',
	];
	const PUSH_PATH = '/bitrix/tools/calendar/office365push.php';

	private static array $deltaInterval;

	/**
	 * @return Date[] = [
	 	'from' => Date,
	 	'to' => Date,
	 ]
	 */
	public function getDeltaInterval(): array
	{
		if (empty(static::$deltaInterval))
		{
			$from = new Date();
			$from->getDate()->add(self::DELTA_INTERVAL['from']);
			$to = new Date();
			$to->getDate()->add(self::DELTA_INTERVAL['to']);
			static::$deltaInterval = [
				'from' => $from,
				'to' =>$to
			];
		}

		return static::$deltaInterval;
	}

	public function isVendorConnection(string $accountType): bool
	{
		return $accountType === self::ACCOUNT_TYPE;
	}
}
