<?php

namespace Bitrix\Im\V2\Entity\User;

class UserBot extends User
{
	protected function fillOnlineData(): void
	{
		return;
	}

	public function isOnlineDataFilled(): bool
	{
		return true;
	}

	protected function checkAccessWithoutCaching(User $otherUser): bool
	{
		if (!static::$moduleManager::isModuleInstalled('intranet'))
		{
			return $this->hasAccessBySocialNetwork($otherUser->getId());
		}

		global $USER;
		if ($otherUser->isExtranet())
		{
			if ($otherUser->getId() === $USER->GetID())
			{
				if ($USER->IsAdmin())
				{
					return true;
				}

				if (static::$loader::includeModule('bitrix24'))
				{
					if (\CBitrix24::IsPortalAdmin($otherUser->getId()) || \Bitrix\Bitrix24\Integrator::isIntegrator($otherUser->getId()))
					{
						return true;
					}
				}
			}

			$inGroup = \Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($this->getId(), $otherUser->getId());
			if ($inGroup)
			{
				return true;
			}


			return false;
		}

		if ($this->isNetwork())
		{
			return true;
		}

		return true;
	}
}