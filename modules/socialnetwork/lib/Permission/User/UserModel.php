<?php

namespace Bitrix\Socialnetwork\Permission\User;

class UserModel extends \Bitrix\Main\Access\User\UserModel
{

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