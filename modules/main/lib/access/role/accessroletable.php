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

abstract class AccessRoleTable extends DataManager
{
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Entity\StringField('NAME', [
				'required' => true,
			])
		];
	}

}