<?php
namespace Bitrix\Sender\Access\Service;


interface RoleRelationServiceInterface
{
	/**
	 * @param array $settings
	 */
	public function saveRoleRelation(array $settings): void;

	/**
	 * @param array $relations
	 *
	 * @return array|null
	 */
	public function getRolesByRelations(array $relations): ?array;

	/**
	 * @param int $roleId
	 */
	public function deleteRoleRelations(int $roleId): void;

	/**
	 * @param array $parameters
	 *
	 * @return array|null
	 */
	public function getRelationList(array $parameters): ?array;
}