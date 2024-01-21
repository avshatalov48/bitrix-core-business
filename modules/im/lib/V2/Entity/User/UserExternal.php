<?php

namespace Bitrix\Im\V2\Entity\User;

class UserExternal extends User
{
	protected function fillOnlineData(bool $withStatus = false): void
	{
		return;
	}

	public function isOnlineDataFilled(bool $withStatus): bool
	{
		return true;
	}
}