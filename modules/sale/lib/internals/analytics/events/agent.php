<?php

namespace Bitrix\Sale\Internals\Analytics\Events;

use Bitrix\Sale\Internals\Analytics;

/**
 * Class Agent
 *
 * @package Bitrix\Sale\Internals\Analytics\Events
 */
final class Agent extends Analytics\Agent
{
	private const CLEAN_OLD_EVENTS_DAYS = 30;

	/**
	 * @inheritDoc
	 */
	protected static function getProviderCode(): string
	{
		return Provider::getCode();
	}

	/**
	 * @inheritDoc
	 */
	protected static function onSuccessfullySent(): void
	{
		$GLOBALS['DB']->query("
			DELETE FROM b_sale_analytics_events
			WHERE CREATED_AT <= DATE_SUB(CURDATE(), INTERVAL " . (int)self::CLEAN_OLD_EVENTS_DAYS . " DAY)
		");

		parent::onSuccessfullySent();
	}
}
