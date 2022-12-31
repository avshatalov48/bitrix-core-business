<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Role;

class RoleDictionary extends \Bitrix\Main\Access\Role\RoleDictionary
{
	public const ROLE_DIRECTOR = 'CATALOG_ROLE_DIRECTOR';
	public const ROLE_SALESMAN = 'CATALOG_ROLE_SALESMAN';
	public const ROLE_STOCKMAN = 'CATALOG_ROLE_STOCKMAN';
}