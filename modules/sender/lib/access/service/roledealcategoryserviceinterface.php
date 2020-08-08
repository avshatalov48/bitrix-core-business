<?php
namespace Bitrix\Sender\Access\Service;

use Bitrix\Main\DB\SqlQueryException;

interface RoleDealCategoryServiceInterface
{
	/**
	 * @param int $userId
	 *
	 * @return array
	 */
	public function getAbleDealCategories(int $userId): array;

	/**
	 * @param int $dealCategoryId
	 *
	 * @return array
	 */
	public function fillDefaultDealCategoryPermission(int $dealCategoryId): array;

	/**
	 * @param int $userId
	 * @param array $categories
	 *
	 * @return array
	 * @throws SqlQueryException
	 */
	public function getFilteredDealCategories(int $userId, array $categories): array;
}