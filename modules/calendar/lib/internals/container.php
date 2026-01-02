<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Internals;

use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudEventSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudSectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleEventSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleSectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365EventSynchronizer;
use Bitrix\Main\DI\ServiceLocator;

class Container
{
	public static function getInstance(): self
	{
		return self::getService('calendar.container');
	}

	private static function getService(string $name): mixed
	{
		$locator = ServiceLocator::getInstance();

		if ($locator->has($name))
		{
			return $locator->get($name);
		}

		$prefix = 'calendar.';

		if (mb_strpos($name, $prefix) !== 0)
		{
			$name = $prefix . $name;
		}

		return $locator->has($name) ? $locator->get($name) : null;
	}

	public static function getGoogleSectionSynchronizer(): GoogleSectionSynchronizer
	{
		return ServiceLocator::getInstance()->get(GoogleSectionSynchronizer::class);
	}

	public static function getICloudSectionSynchronizer(): ICloudSectionSynchronizer
	{
		return ServiceLocator::getInstance()->get(ICloudSectionSynchronizer::class);
	}

	public static function getGoogleEventSynchronizer(): GoogleEventSynchronizer
	{
		return ServiceLocator::getInstance()->get(GoogleEventSynchronizer::class);
	}

	public static function getICloudEventSynchronizer(): ICloudEventSynchronizer
	{
		return ServiceLocator::getInstance()->get(ICloudEventSynchronizer::class);
	}

	public static function getOffice365EventSynchronizer(): Office365EventSynchronizer
	{
		return ServiceLocator::getInstance()->get(Office365EventSynchronizer::class);
	}
}
