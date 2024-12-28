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
	 * @param $groupIds array ids fo groups for which counters are requested
	 * @return array list of counters
	 */
	public static function getValues(int $userId, array $groupIds = []): array
	{
		if (empty(self::$countersValues[$userId]))
		{
			$counterService = Counter::getInstance($userId);
			$invites = $counterService->get(Counter\CounterDictionary::COUNTER_INVITES);

			self::$countersValues[$userId] = [
				'invitation' => [
					'value' => $invites,
					'color' => self::getCounterColor($invites),
					'preset_id' => CalendarFilter::PRESET_INVITED,
				],
				// 'comments' => \CUserCounter::GetValue($userId, self::COMMENTS_COUNTER_ID)
			];

			if ($groupIds)
			{
				foreach ($groupIds as $groupId)
				{
					$groupInvites = $counterService->get(
						Counter\CounterDictionary::COUNTER_GROUP_INVITES,
						$groupId
					);

					self::$countersValues[$userId] = [
						sprintf(Counter\CounterDictionary::COUNTER_GROUP_INVITES_TPL, $groupId) => [
							'value' => $groupInvites,
							'color' => self::getCounterColor($groupInvites),
							'preset_id' => CalendarFilter::PRESET_INVITED,
						]
					];
				}
			}
		}

		return self::$countersValues[$userId];
	}

	private static function getCounterColor(int $value): string
	{
		return $value > 0 ? self::RED_COLOR : self::EMPTY_COLOR;
	}
}
