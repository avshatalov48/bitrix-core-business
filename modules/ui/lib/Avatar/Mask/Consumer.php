<?php
namespace Bitrix\UI\Avatar\Mask;

use \Bitrix\Main;
use Bitrix\Main\Access\User\AccessibleUser;
use \Bitrix\UI\Avatar;

class Consumer extends Main\Access\User\UserModel implements AccessibleUser
{
	public const ACCESS_USER = 'U';

	public function getId()
	{
		return $this->getUserId();
	}

	public function getRoles(): array
	{
		return $this->getAccessCodes();
	}

	public function getPermission(string $permissionId, int $userFieldId = 0): ?int
	{
		return null;
	}

	public function getAccessCodes(): array
	{
		return array_merge(parent::getAccessCodes(), [
			'UA', Main\Access\AccessCode::ACCESS_EMPLOYEE . '0', static::ACCESS_USER . $this->getId()]
		);
	}

	public function useRecentlyMaskId($id)
	{
		Avatar\Model\RecentlyUsedTable::addFromUser($id, $this->getId());
	}
}
