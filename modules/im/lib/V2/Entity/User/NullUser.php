<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Result;

class NullUser extends User
{

	public function getId(): ?int
	{
		return null;
	}

	public function getName(): ?string
	{
		return \Bitrix\Im\User::formatFullNameFromDatabase([]);
	}

	public function isOnlineDataFilled(bool $withStatus): bool
	{
		return true;
	}

	protected function checkAccessInternal(User $otherUser): Result
	{
		return (new Result())->addError(new ChatError(ChatError::ACCESS_DENIED));
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