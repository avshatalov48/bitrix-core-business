<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class UserToGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserToGroup_Query query()
 * @method static EO_UserToGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserToGroup_Result getById($id)
 * @method static EO_UserToGroup_Result getList(array $parameters = [])
 * @method static EO_UserToGroup_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_UserToGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_UserToGroup_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_UserToGroup wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_UserToGroup_Collection wakeUpCollection($rows)
 */
class UserToGroupTable extends Entity\DataManager
{
	public const ROLE_OWNER = SONET_ROLES_OWNER;
	public const ROLE_MODERATOR = SONET_ROLES_MODERATOR;
	public const ROLE_USER = SONET_ROLES_USER;
	public const ROLE_BAN = SONET_ROLES_BAN;
	public const ROLE_REQUEST = SONET_ROLES_REQUEST;

	public const INITIATED_BY_USER = SONET_INITIATED_BY_USER;
	public const INITIATED_BY_GROUP = SONET_INITIATED_BY_GROUP;

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_sonet_user2group';
	}

	public static function getUfId(): string
	{
		return 'USER_TO_WORKGROUP';
	}

	/**
	 * Returns set of all possible roles of a user in a workgroup
	 *
	 * @return array
	 */
	public static function getRolesAll(): array
	{
		return [ self::ROLE_OWNER, self::ROLE_MODERATOR, self::ROLE_USER, self::ROLE_BAN, self::ROLE_REQUEST ];
	}

	/**
	 * Returns set of membership roles of a user in a workgroup
	 *
	 * @return array
	 */
	public static function getRolesMember(): array
	{
		return [ self::ROLE_OWNER, self::ROLE_MODERATOR, self::ROLE_USER ];
	}

	/**
	 * Returns set of all INITIATED_BY values
	 *
	 * @return array
	 */
	public static function getInitiatedByAll(): array
	{
		return [ self::INITIATED_BY_USER, self::INITIATED_BY_GROUP ];
	}

	/**
	 * Returns entity map definition
	 */
	public static function getMap(): array
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'USER' => array(
				'data_type' => (ModuleManager::isModuleInstalled('intranet') ? 'Bitrix\Intranet\UserTable' : 'Bitrix\Main\UserTable'),
				'reference' => array('=this.USER_ID' => 'ref.ID'),
				'join_type' => Join::TYPE_INNER,
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Socialnetwork\WorkgroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
				'join_type' => Join::TYPE_INNER,
			),
			'ROLE' => array(
				'data_type' => 'enum',
				'values' => array(self::ROLE_OWNER, self::ROLE_MODERATOR, self::ROLE_USER, self::ROLE_BAN, self::ROLE_REQUEST),
			),
			'AUTO_MEMBER' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime'
			),
			'INITIATED_BY_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(self::INITIATED_BY_USER, self::INITIATED_BY_GROUP),
			),
			'INITIATED_BY_USER_ID' => array(
				'data_type' => 'integer',
			),
			'INITIATED_BY_USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.INITIATED_BY_USER_ID' => 'ref.ID'),
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			)
		);
	}

	/**
	 * Adds row to entity table
	 *
	 * @param array $data
	 *
	 * @return Entity\AddResult Contains ID of inserted row
	 *
	 * @throws \Exception
	 */
	public static function add(array $data)
	{
		throw new NotImplementedException("Use CSocNetUserToGroup class.");
	}

	/**
	 * Updates row in entity table by primary key
	 *
	 * @param mixed $primary
	 * @param array $data
	 *
	 * @return Entity\UpdateResult
	 *
	 * @throws \Exception
	 */
	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use CSocNetUserToGroup class.");
	}

	/**
	 * Deletes row in entity table by primary key
	 *
	 * @param mixed $primary
	 *
	 * @return Entity\DeleteResult
	 *
	 * @throws \Exception
	 */
	public static function delete($primary)
	{
		throw new NotImplementedException("Use CSocNetUserToGroup class.");
	}

	public static function getGroupModerators(int $groupId): array
	{
		$query = UserToGroupTable::query()
			->setDistinct()
			->setSelect(['USER_ID'])
			->where('GROUP_ID', '=', $groupId)
			->where('ROLE', '<=', UserToGroupTable::ROLE_MODERATOR)
			->exec();

		return $query->fetchAll() ?? [];
	}
}