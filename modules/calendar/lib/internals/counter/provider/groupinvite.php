<?php

namespace Bitrix\Calendar\Internals\Counter\Provider;

use Bitrix\Calendar\Internals\Counter\CounterDictionary;

final class GroupInvite implements Base
{
	public function __construct(
		private readonly int $userId,
		private readonly int $groupId
	)
	{
	}

	public function getValue(): int
	{
		return \CUserCounter::GetValue(
			user_id: $this->userId,
			code: sprintf(CounterDictionary::COUNTER_GROUP_INVITES_TPL, $this->groupId),
		);
	}
}
