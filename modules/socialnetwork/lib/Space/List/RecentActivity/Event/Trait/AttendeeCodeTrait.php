<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait;

trait AttendeeCodeTrait
{
	private function getGroupIdFromAttendeeCode(string $attendeeCode): ?int
	{
		if (!str_starts_with($attendeeCode, 'SG'))
		{
			return null;
		}
		$attendeeCodeMainInfo = explode('_', $attendeeCode)[0] ?? '';
		$groupId = (int)substr($attendeeCodeMainInfo, 2);

		if ($groupId <= 0)
		{
			return null;
		}

		return $groupId;
	}
}