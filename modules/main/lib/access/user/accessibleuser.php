<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\User;


interface AccessibleUser
{
	public static function createFromId(int $userId): AccessibleUser;

	public function getUserId(): int;
	public function getName(): string;
	public function getRoles(): array;
	public function getUserDepartments(): array;
	public function isAdmin(): bool;
	public function getAccessCodes(): array;
	public function getPermission(string $permissionId): ?int;
	public function getSubordinate(int $userId): int;

	public function setUserId(int $userId): AccessibleUser;
}