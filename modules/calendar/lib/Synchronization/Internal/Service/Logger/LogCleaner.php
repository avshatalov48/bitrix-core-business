<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Logger;

use Bitrix\Calendar\Synchronization\Internal\Model\CalendarLogTable;
use Bitrix\Main\Type\DateTime;

class LogCleaner
{
	private const THRESHOLD = 86400 * 14;

	public function cleanOldRecords(): void
	{
		$timestamp = new DateTime();

		$timestamp->add(sprintf('-%d seconds', self::THRESHOLD));

		CalendarLogTable::deleteByFilter(
			['<TIMESTAMP_X' => $timestamp->toString()]
		);
	}
}
