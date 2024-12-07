<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait;

trait LogRightTrait
{
	private function getGroupIdFromLogRight(string $logRight): ?int
	{
		if (!str_starts_with($logRight, 'SG'))
		{
			return null;
		}
		$code = explode('_', $logRight)[0] ?? '';
		$groupId = (int)substr($code, 2);

		if ($groupId <= 0)
		{
			return null;
		}

		return $groupId;
	}
}