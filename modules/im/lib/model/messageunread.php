<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\Internals\Query;
use Bitrix\Im\V2\Common\InsertSelectTrait;
use Bitrix\Im\V2\Common\MultiplyInsertTrait;
use Bitrix\Im\V2\Common\UpdateByFilterTrait;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;

/**
 * Class MessageUnreadTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> IS_MUTED string(1) mandatory
 * <li> DATE_CREATE datetime optional default current datetime
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageUnread_Query query()
 * @method static EO_MessageUnread_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_MessageUnread_Result getById($id)
 * @method static EO_MessageUnread_Result getList(array $parameters = [])
 * @method static EO_MessageUnread_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_MessageUnread createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_MessageUnread_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_MessageUnread wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_MessageUnread_Collection wakeUpCollection($rows)
 */

class MessageUnreadTable extends DataManager
{
	use DeleteByFilterTrait;
	use MergeTrait;
	use InsertSelectTrait;
	use MultiplyInsertTrait;
	use UpdateByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_message_unread';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			'USER_ID' => new IntegerField(
				'USER_ID',
				[
					'required' => true,
				]
			),
			'CHAT_ID' => new IntegerField(
				'CHAT_ID',
				[
					'required' => true,
				]
			),
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[
					'required' => true,
				]
			),
			'IS_MUTED' => new BooleanField(
				'IS_MUTED',
				[
					'required' => true,
					'values' => array('N', 'Y'),
				]
			),
			'CHAT_TYPE' => new StringField(
				'CHAT_TYPE',
				[
					'required' => true,
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'default' => function()
					{
						return new DateTime();
					},
				]
			),
			'PARENT_ID' => new IntegerField(
				'PARENT_ID',
				[
					'required' => true,
				]
			),
		];
	}

	public static function updateBatch(array $fields, array $filter): void
	{
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$update = $sqlHelper->prepareUpdate($tableName, $fields);

		$query = new Query(static::getEntity());
		$query->setFilter($filter);
		$query->getQuery();

		$alias = $sqlHelper->quote($query->getInitAlias()) . '.';
		$where = str_replace($alias, '', $query->getWhere());

		$sql = 'UPDATE ' . $tableName . ' SET ' . $update[0] . ' WHERE ' . $where;
		$connection->queryExecute($sql, $update[1]);
	}
}