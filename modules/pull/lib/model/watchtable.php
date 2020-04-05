<?php

namespace Bitrix\Pull\Model;

use Bitrix\Main\ORM;

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