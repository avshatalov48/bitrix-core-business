<?php

namespace Bitrix\Catalog\v2\Contractor\Provider;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Class Manager
 *
 * @package Bitrix\Catalog\v2\Contractor\Provider
 */
class Manager
{
	private const ON_GET_PROVIDER_EVENT = 'onGetContractorsProvider';

	/**
	 * @return IProvider|null
	 */
	public static function getActiveProvider(): ?IProvider
	{
		$provider = self::getProvider();
		if (!$provider)
		{
			return null;
		}

		static $isMigrating = false;
		if (!$provider::isMigrated())
		{
			if (!$isMigrating)
			{
				$isMigrating = true;
				$provider::runMigration();
			}

			return null;
		}

		return $provider;
	}

	/**
	 * @param string $moduleId
	 * @return bool
	 */
	public static function isActiveProviderByModule(string $moduleId): bool
	{
		$provider = self::getActiveProvider();

		return $provider && $provider::getModuleId() === $moduleId;
	}

	/**
	 * @return string
	 */
	public static function getMigrationProgressHtml(): string
	{
		ob_start();

		$provider = self::getProvider();
		if (
			$provider
			&& !$provider::isMigrated()
		)
		{
			$provider::showMigrationProgress();
		}

		return ob_get_clean();
	}

	/**
	 * @return IProvider|null
	 */
	private static function getProvider(): ?IProvider
	{
		$event = new Event('catalog', self::ON_GET_PROVIDER_EVENT);
		$event->send();

		$resultList = $event->getResults();

		if (is_array($resultList))
		{
			/** @var EventResult $eventResult */
			foreach ($resultList as $eventResult)
			{
				$provider = $eventResult->getParameters();
				if ($provider instanceof IProvider)
				{
					return $provider;
				}
			}
		}

		return null;
	}
}
