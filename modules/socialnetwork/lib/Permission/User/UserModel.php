<?php

namespace Bitrix\Socialnetwork\Permission\User;

use Bitrix\Socialnetwork\Collab\User\User;
use Bitrix\Socialnetwork\Internals\Registry\UserRegistry;

class UserModel extends \Bitrix\Main\Access\User\UserModel
{
	protected ?bool $isCollaber = null;
	protected ?bool $isExtranet = null;
	protected ?bool $isIntranet = null;

	public function isCollaber(): bool
	{
		if ($this->isCollaber === null)
		{
			$this->isCollaber = (new User($this->userId))->isCollaber();
		}

		return $this->isCollaber;
	}

	public function isExtranet(): bool
	{
		if ($this->isExtranet === null)
		{
			$this->isExtranet = (new User($this->userId))->isExtranet();
		}

		return $this->isExtranet;
	}

	public function isIntranet(): bool
	{
		if ($this->isIntranet === null)
		{
			$this->isIntranet = (new User($this->userId))->isIntranet();
		}

		return $this->isIntranet;
	}

	public function isMember(int $groupId): bool
	{
		$userGroups = array_keys(UserRegistry::getInstance($this->userId)->getUserGroups());

		return in_array($groupId, $userGroups, true);
	}

	public function getRoles(): array
	{
		return $this->getAccessCodes();
	}

	public function getPermission(string $permissionId): ?int
	{
		return null;
	}

	public function getAccessCodes(): array
	{
		return array_merge(parent::getAccessCodes(), ['AU']);
	}
}