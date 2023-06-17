<?php

namespace Bitrix\Im\V2\Entity\User;

class UserExtranet extends User
{
	protected function checkAccessWithoutCaching(User $otherUser): bool
	{
		if (!static::$moduleManager::isModuleInstalled('intranet'))
		{
			return $this->hasAccessBySocialNetwork($otherUser->getId());
		}

		if ($otherUser->isBot())
		{
			return true;
		}

		if ($this->isNetwork() && !$otherUser->isExtranet())
		{
			return true;
		}

		$inGroup = \Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($this->getId(), $otherUser->getId());
		if ($inGroup)
		{
			return true;
		}

		return false;
	}
}