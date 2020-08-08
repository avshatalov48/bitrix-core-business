<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Role;


interface AccessibleRoleDictionary
{
	public static function getRoleName(string $code): string;
}