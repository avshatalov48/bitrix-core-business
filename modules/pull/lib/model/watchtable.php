<?php

namespace Bitrix\Pull\Model;

use Bitrix\Main\ORM;

/**
 * Class WatchTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Watch_Query query()
 * @method static EO_Watch_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Watch_Result getById($id)
 * @method static EO_Watch_Result getList(array $parameters = array())
 * @method static EO_Watch_Entity getEntity()
 * @method static \Bitrix\Pull\Model\EO_Watch createObject($setDefaultValues = true)
 * @method static \Bitrix\Pull\Model\EO_Watch_Collection createCollection()
 * @method static \Bitrix\Pull\Model\EO_Watch wakeUpObject($row)
 * @method static \Bitrix\Pull\Model\EO_Watch_Collection wakeUpCollection($rows)
 */
class WatchTable extends ORM\Data\DataManager
{
	public static function getTableName(): string
	{
		return 'b_pull_watch';
	}

	public static function getMap(): array
	{
		return [
			(new ORM\Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new ORM\Fields\IntegerField('USER_ID'))
				->configureRequired(),
			(new ORM\Fields\StringField('CHANNEL_ID'))
				->configureRequired()
				->configureSize(50),
			(new ORM\Fields\StringField('TAG'))
				->configureRequired()
				->configureSize(255),
			(new ORM\Fields\DatetimeField('DATE_CREATE'))
				->configureRequired(),
		];
	}

	public static function getUserIdsByTag(string $tag): array
	{
		$userIds = [];

		$list = static::getList([
			'select' => ['USER_ID'],
			'filter' => [
				'=TAG' => $tag,
			],
		]);
		while($record = $list->fetch())
		{
			$record['USER_ID'] = (int)$record['USER_ID'];
			$userIds[$record['USER_ID']] = $record['USER_ID'];
		}

		return $userIds;
	}
}