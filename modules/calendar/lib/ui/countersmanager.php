<?php

namespace Bitrix\Calendar\Ui;

class CountersManager
{
	private const INVITATION_COUNTER_ID = 'calendar';
	private const COMMENTS_COUNTER_ID = 'calendar_comments';
	private const RED_COLOR = 'DANGER';
	private const GREEN_COLOR = 'SUCCESS';
	private const EMPTY_COLOR = 'THEME';

	private static array $countersValues = [];

	/**
	 * Get list of counters available for calendar module
	 * Implements static cache for for each user
	 * @param $userId int id of the user for whom counters are requested
	 * @return array list of counters
	 */
	public static function getValues(int $userId): array
	{
		if (empty(self::$countersValues[$userId]))
		{
			self::$countersValues[$userId] = [
				'invitation' => [
					'value' => \CUserCounter::GetValue($userId, self::INVITATION_COUNTER_ID),
					'color' => self::getCounterColor(\CUserCounter::GetValue($userId, self::INVITATION_COUNTER_ID)),
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