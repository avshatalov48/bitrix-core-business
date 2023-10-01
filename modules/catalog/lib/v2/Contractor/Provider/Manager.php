<?php

namespace Bitrix\Catalog\v2\Contractor\Provider;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Catalog\v2\Contractor\IConverter;

/**
 * Class Manager
 *
 * @package Bitrix\Catalog\v2\Contractor\Provider
 */
class Manager
{
	public const PROVIDER_STORE_DOCUMENT = 'StoreDocument';
	public const PROVIDER_AGENT_CONTRACT = 'AgentContract';

	private const ON_GET_PROVIDER_EVENT = 'onGetContractorsProvider';
	private const ON_GET_CONVERTER_EVENT = 'onGetContractorsConverter';

	/**
	 * @return IProvider|null
	 */
	public static function getActiveProvider(string $providerName): ?IProvider
	{
		$converter = self::getConverter();
		if (!$converter)
		{
			return null;
		}

		static $isMigrating = false;
		if (!$converter::isMigrated())
		{
			if (!$isMigrating)
			{
				$isMigrating = true;
				$converter::runMigration();
			}

			return null;
		}

		return self::getProvider($providerName);
	}

	public static function isActiveProviderExists(): bool
	{
		$event = new Event('catalog', self::ON_GET_PROVIDER_EVENT);
		$event->send();

		$resultList = $event->getResults();

		if (is_array($resultList))
		{
			/** @var EventResult $eventResult */
			foreach ($resultList as $eventResult)
			{
				$providers = $eventResult->getParameters();
				foreach ($providers as $provider)
				{
					if ($provider instanceof IProvider)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * @param string $moduleId
	 * @return bool
	 */
	public static function isActiveProviderByModule(string $providerName, string $moduleId): bool
	{
		$provider = self::getActiveProvider($providerName);

		return $provider && $provider::getModuleId() === $moduleId;
	}

	/**
	 * @return string
	 */
	public static function getMigrationProgressHtml(): string
	{
		ob_start();

		$converter = self::getConverter();
		if (
			$converter
			&& !$converter::isMigrated()
		)
		{
			$converter::showMigrationProgress();
		}

		return ob_get_clean();
	}

	/**
	 * @return IProvider|null
	 */
	private static function getProvider(string $providerName): ?IProvider
	{
		$event = new Event('catalog', self::ON_GET_PROVIDER_EVENT);
		$event->send();

		$resultList = $event->getResults();

		if (is_array($resultList))
		{
			/** @var EventResult $eventResult */
			foreach ($resultList as $eventResult)
			{
				$providers = $eventResult->getParameters();
				foreach ($providers as $name => $provider)
				{
					if ($name === $providerName && $provider instanceof IProvider)
					{
						return $provider;
					}
				}
			}
		}

		return null;
	}

	private static function getConverter(): ?IConverter
	{
		$event = new Event('catalog', self::ON_GET_CONVERTER_EVENT);
		$event->send();

		$resultList = $event->getResults();

		if (is_array($resultList))
		{
			/** @var EventResult $eventResult */
			foreach ($resultList as $eventResult)
			{
				$provider = $eventResult->getParameters();
				if ($provider instanceof IConverter)
				{
					return $provider;
				}
			}
		}

		return null;
	}
}
