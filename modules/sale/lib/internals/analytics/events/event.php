<?php

namespace Bitrix\Sale\Internals\Analytics\Events;

use Bitrix\Main;

/**
 * Class Event
 *
 * @package Bitrix\Sale\Internals\Analytics\Events
 * @internal
 */
final class Event
{
	public const FACEBOOK_CONVERSION_SHOP_EVENT_ENABLED = 'FACEBOOK_CONVERSION_SHOP_EVENT_ENABLED';
	public const FACEBOOK_CONVERSION_SHOP_EVENT_DISABLED = 'FACEBOOK_CONVERSION_SHOP_EVENT_DISABLED';
	public const FACEBOOK_CONVERSION_EVENT_FIRED = 'FACEBOOK_CONVERSION_EVENT_FIRED';

	/** @var string $name */
	private $name;

	/** @var array $payload */
	private $payload = [];

	/**
	 * @param string $name
	 */
	public function __construct(string $name, array $payload)
	{
		if (!in_array($name, self::getAvailableNames(), true))
		{
			throw new Main\ArgumentException(
				'Name not available, see \Bitrix\Sale\Internals\Analytics\Events\Event::getAvailableNames',
				'name'
			);
		}

		$this->name = $name;
		$this->payload = $payload;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getPayload(): array
	{
		return $this->payload;
	}

	/**
	 * @return array|string[]
	 */
	private static function getAvailableNames(): array
	{
		return [
			self::FACEBOOK_CONVERSION_SHOP_EVENT_ENABLED,
			self::FACEBOOK_CONVERSION_SHOP_EVENT_DISABLED,
			self::FACEBOOK_CONVERSION_EVENT_FIRED,
		];
	}
}