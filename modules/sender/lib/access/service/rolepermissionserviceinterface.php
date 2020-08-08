<?php
namespace Bitrix\Sender\Access\Service;

interface RolePermissionServiceInterface
{
	/**
	 * @param array $permissionSettings
	 * @param int $dealCategoryId
	 */
	public function saveRolePermissions(array &$permissionSettings, int $dealCategoryId): void;

	/**
	 * @param String $name
	 * @param int $dealCategoryId
	 * @param int|null $roleId
	 *
	 * @return int
	 */
	public function saveRole(String $name, int $dealCategoryId = 0, int $roleId = null): int;

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

	/**
	 * returns user groups
	 * @param int $dealCategoryId
	 *
	 * @return array
	 */
	public function getUserGroups(int $dealCategoryId): array;

	/**
	 *  returns access rights list
	 * @return array
	 */
	public function getAccessRights(): array;
}