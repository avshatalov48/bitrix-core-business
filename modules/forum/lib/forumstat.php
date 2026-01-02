<?php
namespace Bitrix\Forum;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;


/**
 * Class ForumStatTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ForumStat_Query query()
 * @method static EO_ForumStat_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ForumStat_Result getById($id)
 * @method static EO_ForumStat_Result getList(array $parameters = [])
 * @method static EO_ForumStat_Entity getEntity()
 * @method static \Bitrix\Forum\EO_ForumStat createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\EO_ForumStat_Collection createCollection()
 * @method static \Bitrix\Forum\EO_ForumStat wakeUpObject($row)
 * @method static \Bitrix\Forum\EO_ForumStat_Collection wakeUpCollection($rows)
 */
class ForumStatTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_forum_stat';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Entity\IntegerField('USER_ID'),
			new Entity\StringField('IP_ADDRESS', ['size' => 128]),
			new Entity\StringField('PHPSESSID', ['size' => 255]),
			new Entity\DatetimeField('LAST_VISIT'),
			new Entity\StringField('SITE_ID', ['size' => 2]),
			new Entity\IntegerField('FORUM_ID'),
			new Entity\IntegerField('TOPIC_ID'),
			new Entity\StringField('SHOW_NAME', ['size' => 255]),
			new Reference('USER', \Bitrix\Main\UserTable::class, Join::on('this.USER_ID', 'ref.ID')),
			new Reference('FORUM_USER', \Bitrix\Forum\UserTable::class, Join::on('this.USER_ID', 'ref.USER_ID')),
		];
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws \Exception
	 */
	public static function upsert(array $fields): void
	{
		$existedRecordId = self::query()
			->where('USER_ID', $fields['USER_ID'])
			->where('PHPSESSID', $fields['PHPSESSID'])
			->setSelect(['ID'])
			->setLimit(1)
			->fetch()['ID'] ?? null;

		if ($existedRecordId)
		{
			self::update($existedRecordId, $fields);
		}
		else
		{
			self::add($fields);
		}
	}
}