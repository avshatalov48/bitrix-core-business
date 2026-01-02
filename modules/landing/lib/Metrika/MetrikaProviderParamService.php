<?php

declare(strict_types=1);

namespace Bitrix\Landing\Metrika;

use Bitrix\AI\Tuning;
use Bitrix\Landing\Connector\Ai;
use Bitrix\Main\Loader;

/**
 * Service for managing and setting provider parameters in Metrika analytics events.
 *
 * This service encapsulates the logic for mapping Metrika events to AI provider codes,
 * retrieving provider codes from the Tuning manager, and setting provider-related parameters
 * in Metrika analytics objects.
 */
class MetrikaProviderParamService
{
	/**
	 * Position of the provider parameter in the Metrika params array.
	 * @var int
	 */
	private const PROVIDER_PARAM_POSITION = 2;

	/**
	 * Name of the provider parameter in Metrika.
	 * @var string
	 */
	private const PROVIDER_PARAM_NAME = 'provider';

	/**
	 * Tuning manager instance for retrieving provider codes.
	 * @var Tuning\Manager
	 */
	private Tuning\Manager $tuningManager;

	/**
	 * MetrikaProviderParamService constructor.
	 *
	 * @param Tuning\Manager|null $tuningManager Optional tuning manager instance. If not provided, a new instance will
	 *     be created.
	 */
	public function __construct(?Tuning\Manager $tuningManager = null)
	{
		if (Loader::includeModule('ai'))
		{
			$this->tuningManager = $tuningManager ?? new Tuning\Manager();
		}
	}

	/**
	 * Sets all relevant parameters in the Metrika analytics object for the specified event.
	 * Implements MetrikaParamSetterInterface.
	 *
	 * @param Metrika $metrika Metrika analytics object.
	 * @param Events $event Metrika event.
	 *
	 * @return void
	 */
	public function setParams(Metrika $metrika, Events $event): void
	{
		$this->setProviderParam($metrika, $event);
	}

	/**
	 * Sets the provider parameter in the given Metrika analytics object for the specified event.
	 *
	 * @param Metrika $metrika Metrika analytics object.
	 * @param Events $event Metrika event.
	 *
	 * @return void
	 */
	private function setProviderParam(Metrika $metrika, Events $event): void
	{
		$providerCode = $this->getProviderCodeByEvent($event);
		if ($providerCode !== null)
		{
			$metrika->setParam(
				self::PROVIDER_PARAM_POSITION,
				self::PROVIDER_PARAM_NAME,
				$providerCode
			);
		}
	}

	/**
	 * Returns the provider code for a given Metrika event.
	 *
	 * @param Events $event Metrika event.
	 *
	 * @return string|null Provider code if found, null otherwise.
	 */
	private function getProviderCodeByEvent(Events $event): ?string
	{
		$providerSetting = self::getProviderSettingName($event->value);
		if (!isset($providerSetting))
		{
			return null;
		}

		$item = $this->tuningManager->getItem($providerSetting);
		if ($item === null)
		{
			return null;
		}

		$providerCode = $item->getValue();
		if (empty($providerCode))
		{
			return null;
		}
		return $item->getOptions()[$providerCode] ?? null;
	}

	/**
	 * Get setting name for AI tuning
	 * @param string $eventName
	 * @return string|null
	 */
	public static function getProviderSettingName(string $eventName): ?string
	{
		$event = Events::tryFrom($eventName);

		return match ($event)
		{
			Events::dataGeneration,
			Events::textsGeneration => Ai::TUNING_CODE_SITE_TEXT_PROVIDER,
			Events::imagesGeneration => Ai::TUNING_CODE_SITE_IMAGE_PROVIDER,
			default => null,
		};
	}
}