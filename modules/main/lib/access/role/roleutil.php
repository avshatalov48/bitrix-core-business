<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Role;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Access\Exception\PermissionSaveException;
use Bitrix\Main\Access\Exception\RoleNotFoundException;
use Bitrix\Main\Access\Exception\RoleRelationSaveException;
use Bitrix\Main\Access\Exception\RoleSaveException;
use Bitrix\Main\Access\Permission\PermissionDictionary;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;

abstract class RoleUtil
{
	protected $roleId;
	protected $role;

	abstract protected static function getRoleTableClass(): string;

	abstract protected static function getRoleRelationTableClass(): string;

	abstract protected static function getPermissionTableClass(): string;

	abstract protected static function getRoleDictionaryClass(): ?string;

	public static function getRoles()
	{
		$class = static::getRoleTableClass();
		return $class::getList()->fetchAll();
	}

	public static function createRole(string $title): int
	{
		$class = static::getRoleTableClass();
		$res = $class::add([
			'NAME' => $title
		]);

		if (!$res->isSuccess())
		{
			throw new RoleSaveException();
		}

		return (int) $res->getId();
	}

	public function __construct(int $roleId)
	{
		$this->roleId = $roleId;
	}

	public function getMembers(int $limit = 0)
	{
		$filter = [
			'filter' => [
				'ROLE_ID' => $this->roleId
			],
			'order' => ['ID' => 'DESC']
		];
		if ($limit)
		{
			$filter['limit'] = $limit;
		}

		$class = static::getRoleRelationTableClass();
		return $class::getList($filter);
	}

	public function deleteRole()
	{
		if (!$this->roleId)
		{
			return;
		}

		// remove role
		$roleClass = static::getRoleTableClass();
		$roleClass::delete($this->roleId);

		// remove role relations
		$relationClass = static::getRoleRelationTableClass();
		$relationClass::deleteList([
			'=ROLE_ID' => $this->roleId
		]);

		// remove permissions
		$permissionClass = static::getPermissionTableClass();
		$permissionClass::deleteList([
			'=ROLE_ID' => $this->roleId
		]);
	}

	public function updateTitle(string $title)
	{
		$this->loadRole();

		if ($this->role->getName() === $title)
		{
			return;
		}

		$dictionaryClass = static::getRoleDictionaryClass();
		if (
			$dictionaryClass
			&& $dictionaryClass::getRoleName($this->role->getName()) === $title
		)
		{
			return;
		}

		$this->role->setName($title);
		$result = $this->role->save();

		if (!$result->isSuccess())
		{
			throw new RoleNotFoundException();
		}
	}

	public function getPermissions(): array
	{
		$class = static::getPermissionTableClass();
		$res = $class::getList([
				'filter' => [
					'=ROLE_ID' => $this->roleId
				]
			])
			->fetchAll();

		$permissions = [];
		foreach ($res as $row)
		{
			$permissions[$row['PERMISSION_ID']] = $row['VALUE'];
		}

		return $permissions;
	}

	/**
	 * @param array $permissions
	 * 		[
	 * 			permission_id => value
	 * 		]
	 * @throws RoleNotFoundException
	 * @throws RoleSaveException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public function updatePermissions(array $permissions)
	{
		$this->loadRole();

		if (!$this->validatePermissions($permissions))
		{
			throw new RoleNotFoundException();
		}

		$permissionClass = static::getPermissionTableClass();
		$permissionClass::deleteList([
			'=ROLE_ID' => $this->roleId
		]);

		$connection = Application::getConnection();

		$query = [];
		foreach ($permissions as $id => $value)
		{
			$expression = new SqlExpression(
				'(?i, ?s, ?i)',
				$this->roleId,
				trim($id),
				$value,
			);
			$expression->setConnection($connection);

			$query[] = $expression->compile();
		}

		if (empty($query))
		{
			return;
		}

		$expression = new SqlExpression(
			'INSERT INTO ?# (ROLE_ID, PERMISSION_ID, VALUE) VALUES ' . implode(',', $query),
			$permissionClass::getTableName(),
		);
		$expression->setConnection($connection);

		$query = $expression->compile();

		try
		{
			$connection->query($query);
		}
		catch (\Exception $e)
		{
			throw new PermissionSaveException();
		}
	}

	/**
	 * @param array $roleRelations
	 *
	 * @throws RoleRelationSaveException
	 */
	public function updateRoleRelations(array $roleRelations)
	{
		$connection = Application::getConnection();

		$roleRelationsClass = static::getRoleRelationTableClass();
		$roleRelationsClass::deleteList([
			'=ROLE_ID' => $this->roleId
		]);

		$query = [];
		foreach ($roleRelations as $code => $type)
		{
			if(!AccessCode::isValid($code))
			{
				throw new RoleRelationSaveException();
			}

			$expression = new SqlExpression(
				'(?i, ?s)',
				$this->roleId,
				trim($code),
			);
			$expression->setConnection($connection);

			$query[] = $expression->compile();
		}

		if (empty($query))
		{
			return;
		}

		$expression = new SqlExpression(
			'INSERT INTO ?# (ROLE_ID, RELATION) VALUES ' . implode(',', $query),
			$roleRelationsClass::getTableName(),
		);
		$expression->setConnection($connection);

		$query = $expression->compile();

		try
		{
			$connection->query($query);
		}
		catch (\Exception $e)
		{
			throw new RoleRelationSaveException();
		}
	}

	protected function loadRole()
	{
		if (!$this->role)
		{
			$class = static::getRoleTableClass();
			$this->role = $class::getById($this->roleId)->fetchObject();
		}
		if (!$this->role)
		{
			throw new RoleNotFoundException();
		}
		return $this->role;
	}

	protected function validatePermissions(array $permissions): bool
	{
		foreach ($permissions as $id => $value)
		{
			return PermissionDictionary::recursiveValidatePermission($permissions, $id);
		}

		return true;
	}
}