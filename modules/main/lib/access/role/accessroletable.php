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

abstract class AccessRoleTable extends DataManager
{
	public static function getMap()
	{
		return [
			new Fields\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Fields\StringField('NAME', [
				'required' => true,
			])
		];
	}

}