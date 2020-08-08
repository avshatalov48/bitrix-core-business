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

	public function checkByItemId(string $action, int $itemId = null, $params = null): bool;

	public function check(string $action, AccessibleItem $item = null, $params = null): bool;

	public function batchCheck(array $request, AccessibleItem $item): array;
}