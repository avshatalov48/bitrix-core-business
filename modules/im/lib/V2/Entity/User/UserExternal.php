<?php

namespace Bitrix\Im\V2\Entity\User;

class UserExternal extends User
{
	protected function fillOnlineData(): void
	{
		return;
	}

	public function isOnlineDataFilled(): bool
	{
		return true;
	}
}