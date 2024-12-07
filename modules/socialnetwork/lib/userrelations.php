<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

/**
 * Class UserRelationsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserRelations_Query query()
 * @method static EO_UserRelations_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserRelations_Result getById($id)
 * @method static EO_UserRelations_Result getList(array $parameters = [])
 * @method static EO_UserRelations_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_UserRelations createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_UserRelations_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_UserRelations wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_UserRelations_Collection wakeUpCollection($rows)
 */
class UserRelationsTable extends Entity\DataManager
{
	/** @see SONET_RELATIONS_FRIEND */
	public const RELATION_FRIEND = 'F';

	/** @see SONET_RELATIONS_REQUEST */
	public const RELATION_REQUEST = 'Z';

	/** @see SONET_RELATIONS_BAN */
	public const RELATION_BAN = 'B';

	public const INITIATED_BY_FIRST = 'F';
	public const INITIATED_BY_SECOND = 'S';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sonet_user_relations';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'FIRST_USER_ID' => array(
				'data_type' => 'integer',
			),
			'SECOND_USER_ID' => array(
				'data_type' => 'integer',
			),
			'RELATION' => array(
				'data_type' => 'enum',
				'required' => true,
				'values' => array(self::RELATION_FRIEND, self::RELATION_REQUEST, self::RELATION_BAN),
			),
			'INITIATED_BY' => array(
				'data_type' => 'enum',
				'required' => true,
				'values' => array(self::INITIATED_BY_FIRST, self::INITIATED_BY_SECOND)
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			),
			'FIRST_USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.FIRST_USER_ID' => 'ref.ID'),
			),
			'SECOND_USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.SECOND_USER_ID' => 'ref.ID'),
			),
		);
	}

	public static function getUserFilter($operation, $field, $filter)
	{
		return array(
			'LOGIC' => 'OR',
			$operation.preg_replace('/^USER/', 'FIRST_USER', $field) => $filter,
			$operation.preg_replace('/^USER/', 'SECOND_USER', $field) => $filter,
		);
	}
}