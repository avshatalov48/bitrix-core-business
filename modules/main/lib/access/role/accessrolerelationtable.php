<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Role;

use Bitrix\Main\Access\Entity\DataManager;
use Bitrix\Main\ORM\Fields;

abstract class AccessRoleRelationTable extends DataManager
{
	public static function getMap()
	{
		return [
			new Fields\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Fields\IntegerField('ROLE_ID', [
				'required' => true
			]),
			new Fields\StringField('RELATION', [
				'required' => true
			])
		];
	}
}
