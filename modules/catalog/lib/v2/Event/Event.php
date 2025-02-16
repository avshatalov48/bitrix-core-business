<?php

namespace Bitrix\Catalog\v2\Event;

final class Event
{
	// Option region
	private const MODULE_ID = 'Catalog';
	private const EVENT_NAME_START_WITH = 'on';

	// Entity region
	public const ENTITY_PRODUCT = 'Product';

	// Method region
	public const METHOD_UPDATE = 'Update';

	// Stage region
	public const STAGE_BEFORE = 'Before';
	public const STAGE_ON = '';
	public const STAGE_AFTER = 'After';

	public static function send(string $entity, string $method, string $stage, array $parameters): void
	{
		$eventName = Event::makeEventName($entity, $method, $stage);

		$eventHandler = new \Bitrix\Main\Event(Event::MODULE_ID, $eventName, $parameters);

		$eventHandler->send();
	}

	public static function makeEventName(string $entity, string $method, string $stage): string
	{
		return Event::EVENT_NAME_START_WITH . $stage . Event::MODULE_ID . $entity . $method;
	}
}
