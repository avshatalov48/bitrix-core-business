<?php

namespace Bitrix\Calendar\Ui;

use Bitrix\Calendar\Internals\Counter;

class CountersManager
{
	private const RED_COLOR = 'DANGER';
	private const GREEN_COLOR = 'SUCCESS';
	private const EMPTY_COLOR = 'THEME';

	private static array $countersValues = [];

	/**
	 * Get list of counters available for calendar module
	 * Implements static cache for each user
	 * @param $userId int id of the user for whom counters are requested
	 * @return array list of counters
	 */
	public static function getValues(int $userId): array
	{
		if (empty(self::$countersValues[$userId]))
		{
			$invites = Counter::getInstance($userId)->get(Counter\CounterDictionary::COUNTER_INVITES);

			self::$countersValues[$userId] = [
				'invitation' => [
					'value' => $invites,
					'color' => self::getCounterColor($invites),
					'preset_id' => 'filter_calendar_meeting_status_q'
				],
				// 'comments' => \CUserCounter::GetValue($userId, self::COMMENTS_COUNTER_ID)
			];
		}

		return self::$countersValues[$userId];
	}

	private static function getCounterColor(int $value): string
	{
		return $value > 0 ? self::RED_COLOR : self::EMPTY_COLOR;
	}
}