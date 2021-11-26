<?php
namespace Bitrix\Forum;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main;

/**
 * Class UserTopicTable
 *
 * @package Bitrix\Forum
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserTopic_Query query()
 * @method static EO_UserTopic_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_UserTopic_Result getById($id)
 * @method static EO_UserTopic_Result getList(array $parameters = array())
 * @method static EO_UserTopic_Entity getEntity()
 * @method static \Bitrix\Forum\EO_UserTopic createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\EO_UserTopic_Collection createCollection()
 * @method static \Bitrix\Forum\EO_UserTopic wakeUpObject($row)
 * @method static \Bitrix\Forum\EO_UserTopic_Collection wakeUpCollection($rows)
 */
class UserTopicTable extends DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string|null
	 */
	public static function getTableName(): string
	{
		return 'b_forum_user_topic';
	}

	/**
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'autocomplete' => true,
			],
			'TOPIC_ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			],
			'USER_ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			],
			'FORUM_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'LAST_VISIT' => [
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => static function() {
					return new DateTime();
				},
			],
		];
	}

	public static function deleteBatch(array $filter)
	{
		$tableName = static::getTableName();
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$where = [];
		foreach ($filter as $key => $value)
		{
			$where[] = $helper->prepareAssignment($tableName, $key, $value);
		}
		$where = implode(' AND ', $where);

		if($where)
		{
			$quotedTableName = $helper->quote($tableName);
			$connection->queryExecute("DELETE FROM {$quotedTableName} WHERE {$where}");
		}
	}
}