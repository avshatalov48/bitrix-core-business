<?php

namespace Bitrix\Sale\Internals\Analytics\Events;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class Provider
 *
 * @package Bitrix\Sale\Internals\Analytics\Events
 * @internal
 */
final class Provider extends Sale\Internals\Analytics\Provider
{
	private const TYPE = 'events';

	/** @var Event $event */
	private $event;

	public function __construct(Event $event)
	{
		$this->event = $event;
	}

	/**
	 * @return string
	 */
	public static function getCode(): string
	{
		return self::TYPE;
	}

	protected function needProvideData(): bool
	{
		return true;
	}

	/**
	 * @return array
	 */
	protected function getProviderData(): array
	{
		return [
			'event_code' => $this->event->getName(),
			'events' => [
				[
					'created_at' => (new Main\Type\DateTime())->getTimestamp(),
					'payload' => $this->event->getPayload(),
				],
			],
		];
	}
}
