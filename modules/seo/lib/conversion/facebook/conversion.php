<?php

namespace Bitrix\Seo\Conversion\Facebook;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Seo\BusinessSuite\DTO\Profile;
use Bitrix\Seo\BusinessSuite\Service;
use Bitrix\Seo\Conversion\ConversionEventInterface;
use Bitrix\Seo\Conversion\ConversionObjectInterface;

final class Conversion implements ConversionObjectInterface
{
	public const TYPE = 'facebook';

	/** @var Event[] $events */
	private $events = [];

	/**@var Service|null $service */
	private $service;

	/**
	 * Conversion constructor.
	 *
	 * @param Service|null $service
	 *
	 */
	public function __construct(?Service $service)
	{
		$this->service = $service;
	}

	/**
	 * added event to sequence
	 *
	 * @param ConversionEventInterface $event
	 *
	 * @return $this
	 */
	public function addEvent(ConversionEventInterface $event): ConversionObjectInterface
	{
		$this->events[] = $event;

		return $this;
	}

	/**
	 * @return ConversionEventInterface[]
	 */
	public function getEvents(): array
	{
		return $this->events;
	}

	/**
	 * firing event to facebook
	 * @return bool
	 * @throws SystemException
	 */
	public function fireEvents(): bool
	{
		if ($this->isAvailable())
		{
			if (!empty($this->events))
			{
				$response = $this->service->getConversion($this->getType())->fireEvents($this->events);
				if ($response && $response->isSuccess())
				{
					$this->events = [];

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isAvailable(): bool
	{

		return $this->service && $this->service::getAuthAdapter($this->getType())->hasAuth();
	}

	/**
	 * @inheritDoc
	 */
	public function getType(): string
	{
		return static::TYPE;
	}
}