<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\Internals\Query;
use Bitrix\Im\V2\Common\UpdateByFilterTrait;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;


/**
 * Class RelationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> MESSAGE_TYPE string(1) optional default 'P'
 * <li> USER_ID int mandatory
 * <li> START_ID int optional
 * <li> LAST_ID int optional
 * <li> LAST_SEND_ID int optional
 * <li> LAST_FILE_ID int optional
 * <li> LAST_READ datetime optional
 * <li> STATUS int optional
 * <li> CALL_STATUS int optional
 * <li> NOTIFY_BLOCK bool optional default 'N'
 * <li> CHAT reference to {@link \Bitrix\Im\Model\MessageTable}
 * <li> START reference to {@link \Bitrix\Im\Model\MessageTable}
 * <li> LAST_SEND reference to {@link \Bitrix\Im\Model\MessageTable}
 * <li> LAST reference to {@link \Bitrix\Im\Model\MessageTable}
 * <li> USER reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Relation_Query query()
 * @method static EO_Relation_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Relation_Result getById($id)
 * @method static EO_Relation_Result getList(array $parameters = array())
 * @method static EO_Relation_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Relation createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Relation_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Relation wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Relation_Collection wakeUpCollection($rows)
 */

class RelationTable extends Entity\DataManager
{
	use DeleteByFilterTrait;
	use UpdateByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_relation';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				//'title' => Loc::getMessage('RELATION_ENTITY_ID_FIELD'),
			),
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				//'title' => Loc::getMessage('RELATION_ENTITY_CHAT_ID_FIELD'),
			),
			'MESSAGE_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateMessageType'),
				//'title' => Loc::getMessage('RELATION_ENTITY_MESSAGE_TYPE_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				//'title' => Loc::getMessage('RELATION_ENTITY_USER_ID_FIELD'),
			),
			'START_ID' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('RELATION_ENTITY_START_ID_FIELD'),
			),
			'LAST_ID' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('RELATION_ENTITY_LAST_ID_FIELD'),
			),
			'UNREAD_ID' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('RELATION_ENTITY_UNREAD_ID_FIELD'),
				'default' => 0
			),
			'LAST_SEND_ID' => array( /** @deprecated */
				'data_type' => 'integer',
				//'title' => Loc::getMessage('RELATION_ENTITY_LAST_SEND_ID_FIELD'),
			),
			'LAST_SEND_MESSAGE_ID' => array(
				'data_type' => 'integer',
			),
			'REASON' => array(
				'data_type' => 'string',
				'default_value' => '',
				'validation' => array(__CLASS__, 'validateReason'),
			),
			'LAST_FILE_ID' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('RELATION_ENTITY_LAST_FILE_ID_FIELD'),
			),
			'LAST_READ' => array(
				'data_type' => 'datetime',
				//'title' => Loc::getMessage('RELATION_ENTITY_LAST_READ_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('RELATION_ENTITY_STATUS_FIELD'),
			),
			'CALL_STATUS' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('RELATION_ENTITY_CALL_STATUS_FIELD'),
			),
			'MESSAGE_STATUS' => array(
				'data_type' => 'string',
				'default_value' => IM_MESSAGE_STATUS_RECEIVED,
				'validation' => array(__CLASS__, 'validateMessageStatus'),
			),
			'NOTIFY_BLOCK' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				//'title' => Loc::getMessage('RELATION_ENTITY_NOTIFY_BLOCK_FIELD'),
			),
			'MANAGER' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'COUNTER' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'START_COUNTER' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'CHAT' => array(
				'data_type' => 'Bitrix\Im\Model\ChatTable',
				'reference' => array('=this.CHAT_ID' => 'ref.ID'),
			),
			'START' => array(
				'data_type' => 'Bitrix\Im\Model\MessageTable',
				'reference' => array('=this.START_ID' => 'ref.ID'),
			),
			'LAST_SEND' => array(
				'data_type' => 'Bitrix\Im\Model\MessageTable',
				'reference' => array('=this.LAST_SEND_ID' => 'ref.ID'),
			),
			'LAST' => array(
				'data_type' => 'Bitrix\Im\Model\MessageTable',
				'reference' => array('=this.LAST_ID' => 'ref.ID'),
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for MESSAGE_TYPE field.
	 *
	 * @return array
	 */
	public static function validateMessageType()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for MESSAGE_STATUS field.
	 *
	 * @return array
	 */
	public static function validateMessageStatus()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for REASON field.
	 *
	 * @return array
	 */
	public static function validateReason()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * @param array $fields Record as returned by getList
	 * @return string
	 */
	public static function generateSearchContent(array $fields)
	{
		$result = \Bitrix\Main\Search\MapBuilder::create()
			->addText($fields['TITLE'])
			->build();

		return $result;
	}

	/**
	 * @deprecated
	 */
	public static function deleteBatch(array $filter, $limit = 0): int
	{
		/*
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$query = new Query(static::getEntity());
		$query->setFilter($filter);
		$query->getQuery();

		$alias = $sqlHelper->quote($query->getInitAlias()) . '.';
		$where = str_replace($alias, '', $query->getWhere());

		$sql = 'DELETE FROM ' . $tableName . ' WHERE ' . $where;
		if($limit > 0)
		{
			$sql .= ' LIMIT ' . $limit;
		}

		$connection->queryExecute($sql);
		*/

		$connection = Application::getConnection();

		static::deleteByFilter($filter);

		return $connection->getAffectedRowsCount();
	}
}