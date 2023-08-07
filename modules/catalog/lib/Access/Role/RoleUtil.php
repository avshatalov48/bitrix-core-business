<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Role;

use Bitrix\Catalog\Access\Permission\PermissionTable;
use Bitrix\Main\Application;

class RoleUtil extends \Bitrix\Main\Access\Role\RoleUtil
{

	protected static function getRoleTableClass(): string
	{
		return RoleTable::class;
	}

	protected static function getRoleRelationTableClass(): string
	{
		return RoleRelationTable::class;
	}

	protected static function getPermissionTableClass(): string
	{
		return PermissionTable::class;
	}

	protected static function getRoleDictionaryClass(): ?string
	{
		return RoleDictionary::class;
	}

	/**
	 * insert data to permission table
	 * @param array $valuesData
	 *
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function insertPermissions(array $valuesData): void
	{
		if (empty($valuesData))
		{
			return;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$query = '
			INSERT INTO b_catalog_permission
				(ROLE_ID, PERMISSION_ID, ' . $helper->quote('VALUE') . ')
				VALUES ' . implode(',', $valuesData)
		;

		$connection->query($query);
	}

	public function getPermissions(): array
	{
		$class = static::getPermissionTableClass();

		return $class::getList([
				'filter' => [
					'=ROLE_ID' => $this->roleId,
				],
				'select' => ['PERMISSION_ID', 'VALUE']
			])
			->fetchAll()
		;
	}
}