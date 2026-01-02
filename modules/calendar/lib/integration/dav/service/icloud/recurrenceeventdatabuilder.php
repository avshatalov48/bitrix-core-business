<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav\Service\ICloud;

use Bitrix\Dav\Integration\Calendar\RecurrenceEventBuilder;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class RecurrenceEventDataBuilder
{
	private RecurrenceEventBuilder $dataBuilder;

	/**
	 * @throws ArgumentException
	 */
	public function __construct(array $eventsData)
	{
		if (!self::isAvailable())
		{
			throw new SystemException('Module dav is not installed');
		}

		$this->dataBuilder = new RecurrenceEventBuilder($eventsData);
	}

	public function render(): string
	{
		return $this->dataBuilder->render();
	}

	private static function isAvailable(): bool
	{
		return Loader::includeModule('dav');
	}
}
