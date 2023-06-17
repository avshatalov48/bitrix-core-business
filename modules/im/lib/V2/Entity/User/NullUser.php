<?php

namespace Bitrix\Im\V2\Entity\User;

class NullUser extends User
{

	public function getId(): ?int
	{
		return null;
	}

	public function isOnlineDataFilled(): bool
	{
		return true;
	}

	protected function checkAccessWithoutCaching(User $otherUser): bool
	{
		return false;
	}

	public function isExist(): bool
	{
		return false;
	}

	public function isActive(): bool
	{
		return false;
	}
}