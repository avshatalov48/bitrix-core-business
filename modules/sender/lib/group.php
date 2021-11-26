<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type;
use Bitrix\Sender\Internals\Model\GroupCounterTable;
use Bitrix\Sender\Posting\SegmentDataBuilder;

Loc::loadMessages(__FILE__);

class GroupTable extends Entity\DataManager
{
	public const STATUS_NEW = 'N';
	public const STATUS_IN_PROGRESS = 'P';
	public const STATUS_READY_TO_USE = 'R';
	public const STATUS_DONE = 'D';
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => function ()
				{
					return array(
						//new Entity\Validator\Unique
					);
				}
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_GROUP_FIELD_TITLE_NAME')
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new Type\DateTime(),
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new Type\DateTime(),
			),
			'DATE_USE' => array(
				'data_type' => 'datetime',
			),
			'DATE_USE_EXCLUDE' => array(
				'data_type' => 'datetime',
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'required' => true,
				'default_value' => 'Y',
				'values' => array('N', 'Y'),
			),
			'HIDDEN' => array(
				'data_type' => 'boolean',
				'required' => true,
				'default_value' => 'N',
				'values' => array('N', 'Y'),
			),
			'IS_SYSTEM' => array(
				'data_type' => 'boolean',
				'required' => true,
				'default_value' => 'N',
				'values' => array('N', 'Y'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SENDER_ENTITY_GROUP_FIELD_TITLE_DESCRIPTION'),
				'validation' => array(__CLASS__, 'validateDescription'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 100,
				'title' => Loc::getMessage('SENDER_ENTITY_GROUP_FIELD_TITLE_SORT')
			),
			'ADDRESS_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'required' => true,
			),
			'USE_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'required' => true,
			),
			'USE_COUNT_EXCLUDE' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'required' => true,
			),
			'GROUP_CONNECTOR' => array(
				'data_type' => 'Bitrix\Sender\GroupConnectorTable',
				'reference' => array('=this.ID' => 'ref.GROUP_ID'),
			),
			'MAILING_GROUP' => array(
				'data_type' => 'Bitrix\Sender\MailingGroupTable',
				'reference' => array('=this.ID' => 'ref.GROUP_ID'),
			),
			'DEAL_CATEGORY' => new ReferenceField(
					'DEAL_CATEGORY',
					GroupDealCategoryTable::class,
					[
						'=this.ID' => 'ref.GROUP_ID',
					],
					['join_type' => 'LEFT']
			),
			'STATUS' => [
				'data_type' => 'string',
				'default_value' => self::STATUS_NEW,
				'values' => [
					self::STATUS_NEW,
					self::STATUS_IN_PROGRESS,
					self::STATUS_READY_TO_USE,
					self::STATUS_DONE,
				]
			],
		);
	}

	/**
	 * Returns validators for DESCRIPTION field.
	 *
	 * @return array
	 */
	public static function validateDescription()
	{
		return array(
			new Entity\Validator\Length(null, 2000),
		);
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$primary = array('GROUP_ID' => $data['primary']['ID']);
		GroupConnectorTable::delete($primary);
		GroupCounterTable::deleteList($primary);
		SegmentDataBuilder::clearGroupBuilding((int) $data['primary']['ID']);

		return $result;
	}

	public static function onBeforeUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;

		$data = $event->getParameters();
		if (array_key_exists('DATE_UPDATE', $data['fields']))
		{
			$data['fields']['DATE_UPDATE'] = new Type\DateTime();
			$result->modifyFields($data['fields']);
		}

		if (array_key_exists('CODE', $data['fields']) && is_null($data['fields']['CODE']))
		{
			$data['fields']['CODE'] = new SqlExpression('NULL');
			$result->modifyFields($data['fields']);
		}

		return $result;
	}
}


class GroupConnectorTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_connector';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'ENDPOINT' => array(
				'data_type' => 'string',
				'required' => true,
				'serialized' => true,
			),
			'ADDRESS_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'FILTER_ID' => array(
				'data_type' => 'string',
			),
		);
	}

	public static function onAfterUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;

		$data = $event->getParameters();
		$groupId = $data['fields']['GROUP_ID'];

		SegmentDataBuilder::actualize($groupId, false);
		return $result;
	}
}



/**
 * Class GroupDealCategoryTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupDealCategory_Query query()
 * @method static EO_GroupDealCategory_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_GroupDealCategory_Result getById($id)
 * @method static EO_GroupDealCategory_Result getList(array $parameters = array())
 * @method static EO_GroupDealCategory_Entity getEntity()
 * @method static \Bitrix\Sender\EO_GroupDealCategory createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\EO_GroupDealCategory_Collection createCollection()
 * @method static \Bitrix\Sender\EO_GroupDealCategory wakeUpObject($row)
 * @method static \Bitrix\Sender\EO_GroupDealCategory_Collection wakeUpCollection($rows)
 */
class GroupDealCategoryTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_deal_category';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'DEAL_CATEGORY_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);
	}


	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}
