<?php
namespace Bitrix\Forum;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Entity;

class UserForumTable extends Main\Entity\DataManager
{
	public static function getTableName(): string
	{
		return 'b_forum_user_forum';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Entity\IntegerField('USER_ID'),
			new Entity\IntegerField('FORUM_ID'),
			new Entity\DatetimeField('LAST_VISIT'),
			new Entity\DatetimeField('MAIN_LAST_VISIT'),
			new Reference('USER', Main\UserTable::class, Join::on('this.USER_ID', 'ref.ID')),
			new Reference('FORUM_USER', UserTable::class, Join::on('this.USER_ID', 'ref.USER_ID')),
		];
	}
}