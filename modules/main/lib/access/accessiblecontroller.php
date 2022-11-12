<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access;


use Bitrix\Main\Access\User\AccessibleUser;

interface AccessibleController
{
	public static function can($userId, string $action, $itemId = null, $params = null): bool;

	public function __construct(int $userId);

	public function getUser(): AccessibleUser;

	/**
	 * Get filter for entity.
	 *
	 * The filter contains conditions that allow the user to see only what is available to him according to the granted rights.
	 *
	 * @param string $action
	 * @param string $entityName recommended to use the name of the tablet class.
	 * @param mixed $params
	 *
	 * @return array|null is filter not available - return null.
	 */
	public function getEntityFilter(string $action, string $entityName, $params = null): ?array;

	public function checkByItemId(string $action, int $itemId = null, $params = null): bool;

	public function check(string $action, AccessibleItem $item = null, $params = null): bool;

	public function batchCheck(array $request, AccessibleItem $item): array;
}
