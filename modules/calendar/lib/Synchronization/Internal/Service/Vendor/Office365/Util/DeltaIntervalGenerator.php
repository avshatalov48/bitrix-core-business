<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Util;

use Bitrix\Calendar\Core\Base\Date;

class DeltaIntervalGenerator
{
	public const TIME_FORMAT_LONG = 'Y-m-d\TH:i:s.u';

	private const DELTA_INTERVAL = [
		'from' => '-1 month',
		'to' => '+20 year',
	];

	private static array $deltaInterval;

	/**
	 * @return Date[] ['from' => Date, 'to' => Date]
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
}
