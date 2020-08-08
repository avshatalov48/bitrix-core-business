<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Role;

use Bitrix\Main\Entity;
use Bitrix\Main\Access\Entity\DataManager;

abstract class AccessRoleRelationTable extends DataManager
{
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Entity\IntegerField('ROLE_ID', [
				'required' => true
			]),
			new Entity\StringField('RELATION', [
				'required' => true
			])
		];
	}
}