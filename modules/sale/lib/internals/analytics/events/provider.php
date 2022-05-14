<?php

namespace Bitrix\Sale\Internals\Analytics\Events;

use Bitrix\Sale\Internals\Analytics;
use Bitrix\Sale\Internals\AnalyticsEventTable;
use Bitrix\Main\Type\DateTime;

/**
 * Class Provider
 *
 * @package Bitrix\Sale\Internals\Analytics\Events
 */
final class Provider extends Analytics\Provider
{
	private const TYPE = 'events';

	/**
	 * @return string
	 */
	public static function getCode(): string
	{
		return self::TYPE;
	}

	/**
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 */
	protected function getProviderData(DateTime $dateFrom, DateTime $dateTo): array
	{
		$result = [];

		$eventsList = AnalyticsEventTable::getList([
			'filter' => [
				'>=CREATED_AT' => $dateFrom,
				'<CREATED_AT' => $dateTo,
			],
		]);
		while ($event = $eventsList->fetch())
		{
			if (!isset($result[$event['CODE']]))
			{
				$result[$event['CODE']] = [
					'event_code' => $event['CODE'],
					'events' => [],
				];
			}

			$result[$event['CODE']]['events'][] = [
				'created_at' => $event['CREATED_AT']->getTimestamp(),
				'payload' => $event['PAYLOAD'],
			];
		}

		return array_values($result);
	}
}
