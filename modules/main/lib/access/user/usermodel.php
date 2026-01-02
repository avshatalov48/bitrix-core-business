<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Access\User;

use Bitrix\HumanResources\Config\Storage;
use Bitrix\HumanResources\Service\Container;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\UserAccessTable;
use Bitrix\Main\UserTable;

abstract class UserModel
	implements AccessibleUser
{
	protected
		$userId,
		$name,
		$roles,
		$userDepartments,
		$isAdmin,
		$accessCodes;

	protected static $cache = [];

	public static function createFromId(int $userId): static
	{
		$key = 'USER_'.static::class.'_'.$userId;
		if (!array_key_exists($key, static::$cache))
		{
			$model = new static();
			$model->setUserId($userId);
			static::$cache[$key] = $model;
		}
		return static::$cache[$key];
	}

	protected function __construct()
	{
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setUserId(int $userId): AccessibleUser
	{
		$this->userId = $userId;
		return $this;
	}

	public function getName(): string
	{
		if ($this->name === null)
		{
			if ($this->userId === 0)
			{
				$this->name = '';
				return $this->name;
			}

		}
		return $this->name;
	}

	public function getUserDepartments(): array
	{
		if ($this->userDepartments === null)
		{
			$this->userDepartments = UserSubordinate::getDepartmentsByUserId($this->userId);
		}
		return $this->userDepartments;
	}

	public function isAdmin(): bool
	{
		if (!$this->userId)
		{
			return false;
		}

		if ($this->isAdmin === null)
		{
			$currentUser = CurrentUser::get();
			if ((int) $currentUser->getId() === $this->userId)
			{
				$userGroups = $currentUser->getUserGroups();
			}
			else
			{
				$userGroups = \CUser::GetUserGroup($this->userId);
			}
			$this->isAdmin = in_array(1, $userGroups);
		}

		return $this->isAdmin;
	}

	public function getAccessCodes(): array
	{
		if ($this->accessCodes === null)
		{
			$this->accessCodes = [];
			if ($this->userId === 0)
			{
				return $this->accessCodes;
			}

			// mantis #0160102, #0213214
			$res = UserAccessTable::query()
				->setSelect(['ACCESS_CODE'])
				->where('USER_ID', $this->userId)
				->whereNot('PROVIDER_ID', 'imchat')
				->exec()
				->fetchAll();

			foreach ($res as $row)
			{
				$signature = (new AccessCode($row['ACCESS_CODE']))->getSignature();
				if ($signature)
				{
					$this->accessCodes[$signature] = $signature;
				}
			}

			$this->accessCodes = array_values($this->accessCodes);

			if (
				Loader::includeModule('humanresources')
				&& Storage::instance()->isCompanyStructureConverted()
			)
			{
				return $this->accessCodes;
			}

			// add employee access code
			if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
            {
                return $this->accessCodes;
            }

            $user = UserTable::getList([
                'select' => ['UF_DEPARTMENT'],
                'filter' => [
                    '=ID' => $this->userId,
                ],
                'limit' => 1
            ])->fetch();

            if (
                $user
                && is_array($user['UF_DEPARTMENT'])
                && count($user['UF_DEPARTMENT'])
                && !empty(array_values($user['UF_DEPARTMENT'])[0])
            )
            {
                $this->accessCodes[] = AccessCode::ACCESS_EMPLOYEE . '0';
            }
		}
		return $this->accessCodes;
	}

	public function getSubordinate(int $userId): int
	{
		return (new UserSubordinate($this->userId))->getSubordinate($userId);
	}

	abstract public function getRoles(): array;

	abstract public function getPermission(string $permissionId): ?int;
}