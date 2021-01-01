<?php
namespace Bitrix\Forum;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;


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
			new Entity\EnumField('SHOW_NAME', ['values' => ['Y', 'N'], 'default_value' => 'N']),
			new Reference('USER', \Bitrix\Main\UserTable::class, Join::on('this.USER_ID', 'ref.ID')),
			new Reference('FORUM_USER', \Bitrix\Forum\UserTable::class, Join::on('this.USER_ID', 'ref.USER_ID')),
		];
	}
}