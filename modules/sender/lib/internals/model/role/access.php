<?php

namespace Bitrix\Sender\Internals\Model\Role;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;

/**
 * Class AccessTable
 *
 * @package Bitrix\Sender\Internals\Model\Role
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Access_Query query()
 * @method static EO_Access_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Access_Result getById($id)
 * @method static EO_Access_Result getList(array $parameters = array())
 * @method static EO_Access_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Access createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Access wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection wakeUpCollection($rows)
 */
class AccessTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_sender_role_access';
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
			'ACCESS_CODE' => new Entity\StringField('ACCESS_CODE', array(
				'required' => true,
			)),
			'ROLE' => new Entity\ReferenceField(
				'ROLE', 'Bitrix\Sender\Access\Role\Role',
				array('=this.ROLE_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			)
		);
	}

	/**
	 * Deletes all records from the table.
	 *
	 * @return Entity\DeleteResult
	 */
	public static function truncate()
	{
		$sql = "TRUNCATE TABLE " . self::getTableName();
		Application::getConnection()->queryExecute($sql);

		$result = new Entity\DeleteResult();
		return $result;
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