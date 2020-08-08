<?php

namespace Bitrix\Sender\Internals\Model\Role;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity;
use Bitrix\Sender\Access\Role\Role;

/**
 * Class PermissionTable
 *
 * @package Bitrix\Sender\Internals\Model\Role
 */
class PermissionTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_sender_role_permission';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			'ROLE_ID' => new Entity\IntegerField('ROLE_ID', array(
				'required' => true,
			)),
			'ENTITY' => new Entity\StringField('ENTITY', array(
				'required' => true,
			)),
			'ACTION' => new Entity\StringField('ACTION', array(
				'required' => true,
			)),
			'PERMISSION' => new Entity\StringField('PERMISSION'),
			'ROLE_ACCESS' => new Entity\ReferenceField(
				'ROLE_ACCESS',
				'Bitrix\Sender\Internals\Model\Role\Access',
				array('=this.ROLE_ID' => 'ref.ROLE_ID'),
				array('join_type' => 'INNER')
			),
			'ROLE' => new Entity\ReferenceField(
				'ROLE', 'Bitrix\Sender\Access\Role\Role',
				array('=this.ROLE_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
		);
	}

	/**
	 * Deletes all access codes associated with the specified role.
	 *
	 * @param int $roleId Id of the role.
	 * @return Entity\DeleteResult
	 * @throws ArgumentException
	 */
	public static function deleteByRoleId($roleId)
	{
		$roleId = (int) $roleId;
		if($roleId <= 0)
		{
			throw new ArgumentException('Role id should be greater than zero', 'roleId');
		}

		$sql = "DELETE FROM " . self::getTableName() . " WHERE ROLE_ID = " . $roleId;
		Application::getConnection()->queryExecute($sql);

		$result = new Entity\DeleteResult();
		return $result;
	}
}