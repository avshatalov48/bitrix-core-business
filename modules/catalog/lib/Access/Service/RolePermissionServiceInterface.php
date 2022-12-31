<?php
namespace Bitrix\Catalog\Access\Service;

interface RolePermissionServiceInterface
{
	/**
	 * @param array $permissionSettings
	 */
	public function saveRolePermissions(array $permissionSettings): array;

	/**
	 * @param String $name
	 * @param int|null $roleId
	 *
	 * @return int
	 */
	public function saveRole(String $name, int $roleId = null): int;

	/**
	 * @param int $roleId
	 */
	public function deleteRole(int $roleId): void;

	/**
	 *
	 * @param array $parameters
	 *
	 * @return array
	 */
	public function getSavedPermissions(array $parameters = []): array;

	/**
	 * @param array $parameters
	 *
	 * @return array
	 */
	public function getRoleList(array $parameters = []): array;

	/**
	 * @param int $userId
	 *
	 * @return array
	 */
	public function getRoleListByUser(int $userId): array;
}