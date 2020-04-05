<?php
namespace Bitrix\Forum;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class SubscribeTable
 *
 * Fields:
 * <ul>
 * <li> ID int not null auto_increment,
 * <li> USER_ID int(10) not null,
 * <li> FORUM_ID int(10) not null,
 * <li> TOPIC_ID int(10) null,
 * <li> START_DATE datetime not null,
 * <li> LAST_SEND int(10) null,
 * <li> NEW_TOPIC_ONLY char(50) not null default 'N',
 * <li> SITE_ID char(2) not null default 'ru',
 * <li> SOCNET_GROUP_ID int NULL,
 * <li> primary key (ID),
 * <li> unique UX_FORUM_SUBSCRIBE_USER(USER_ID, FORUM_ID, TOPIC_ID, SOCNET_GROUP_ID)
 * </ul>
 *
 * @package Bitrix\Forum
 */
class SubscribeTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_subscribe';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Entity\IntegerField('USER_ID'),
			new Entity\IntegerField('FORUM_ID'),
			new Entity\IntegerField('TOPIC_ID'),
			new Entity\DatetimeField('START_DATE'),
			new Entity\IntegerField('LAST_SEND'),
			new Entity\EnumField('NEW_TOPIC_ONLY', ['values' => ['Y', 'N'], 'default_value' => 'N']),
			new Entity\StringField('SITE_ID', ['size' => 2, 'default_value' => 'ru']),
			new Entity\IntegerField('SOCNET_GROUP_ID'),
			new Reference("USER", \Bitrix\Main\UserTable::class, Join::on("this.USER_ID", "ref.ID")),
			new Reference("FORUM_USER", \Bitrix\Forum\UserTable::class, Join::on("this.USER_ID", "ref.USER_ID")),
		];
	}
}