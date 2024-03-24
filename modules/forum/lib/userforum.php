<?php
namespace Bitrix\Forum;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Entity;

/**
 * Class UserForumTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserForum_Query query()
 * @method static EO_UserForum_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserForum_Result getById($id)
 * @method static EO_UserForum_Result getList(array $parameters = [])
 * @method static EO_UserForum_Entity getEntity()
 * @method static \Bitrix\Forum\EO_UserForum createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\EO_UserForum_Collection createCollection()
 * @method static \Bitrix\Forum\EO_UserForum wakeUpObject($row)
 * @method static \Bitrix\Forum\EO_UserForum_Collection wakeUpCollection($rows)
 */
class UserForumTable extends Main\Entity\DataManager
{
	public static function getTableName(): string
	{
		return 'b_forum_user_forum';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', ['autocomplete' => true]),
			new Entity\IntegerField('USER_ID', ['primary' => true]),
			new Entity\IntegerField('FORUM_ID', ['primary' => true]),
			new Entity\DatetimeField('LAST_VISIT'),
			new Entity\DatetimeField('MAIN_LAST_VISIT'),
			new Reference('USER', Main\UserTable::class, Join::on('this.USER_ID', 'ref.ID')),
			new Reference('FORUM_USER', UserTable::class, Join::on('this.USER_ID', 'ref.USER_ID')),
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