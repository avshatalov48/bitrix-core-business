<?php

namespace Bitrix\Calendar\Ui;

class CountersManager
{
	private const INVITATION_COUNTER_ID = 'calendar';
	private const COMMENTS_COUNTER_ID = 'calendar_comments';

	private static $countersValues = [];

	/**
	 * Get list of counters available for calendar module
	 * Implements static cache for for each user
	 * @param $userId int id of the user for whom counters are requested
	 * @return array list of counters
	 */
	public static function getValues(int $userId)
	{
		if (empty(self::$countersValues[$userId]))
		{
			self::$countersValues[$userId] = [
				'invitation' => \CUserCounter::GetValue($userId, self::INVITATION_COUNTER_ID),
				// 'comments' => \CUserCounter::GetValue($userId, self::COMMENTS_COUNTER_ID)
			];
		}
		return self::$countersValues[$userId];
	}
}