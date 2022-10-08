<?php

namespace Bitrix\Rest\Event;

/**
 * Interface EventBindInterface for subscribing to PHP Bitrix events and implementing handlers transferred to REST events
 *
 * @package Bitrix\Rest\Event
 */

interface EventBindInterface
{
	/**
	 *
	 * Get callback for all PHP events transferred to REST
	 *
	 * @return array
	 */
	public static function getCallbackRestEvent(): array;

	/**
	 *
	 * Get config, handlers and bindings PHP events to REST events
	 *
	 * @return array
	 */
	public static function getHandlers(): array;
}