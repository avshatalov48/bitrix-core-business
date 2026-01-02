<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Infrastructure\Agent\Logger;

use Bitrix\Calendar\Synchronization\Internal\Service\Logger\LogCleaner;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;

class CleanLogAgent
{
	public static function runAgent(): string
	{
		if (Loader::includeModule('calendar'))
		{
			ServiceLocator::getInstance()->get(LogCleaner::class)->cleanOldRecords();
		}

		return '\\Bitrix\\Calendar\\Synchronization\\Infrastructure\\Agent\\Logger\\CleanLogAgent::runAgent();';
	}
}
