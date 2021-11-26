<?php
namespace Bitrix\Forum;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class GroupTable
 *
 * Fields:
 * <ul>
 * <li> ID int not null auto_increment,
 * <li> SORT int not null default '150',
 * <li> PARENT_ID int null,
 * <li> LEFT_MARGIN int null,
 * <li> RIGHT_MARGIN int null,
 * <li> DEPTH_LEVEL int null,
 * <li> XML_ID varchar(255)
 * </ul>
 *
 * @package Bitrix\Forum
 */
class GroupTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_group';
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
			new Entity\IntegerField('SORT', ['default_value' => 150]),
			new Entity\IntegerField('PARENT_ID'),
			new Entity\IntegerField('LEFT_MARGIN'),
			new Entity\IntegerField('RIGHT_MARGIN'),
			new Entity\IntegerField('DEPTH_LEVEL'),
			new Entity\StringField('XML_ID', ['size' => 255]),
			new Reference("LANG", GroupLangTable::class, Join::on("this.ID", "ref.FORUM_GROUP_ID"))
		];
	}
}

/**
 * Class GroupLangTable
 * <ul>
 * <li> ID int not null auto_increment,
 * <li> FORUM_GROUP_ID int not null,
 * <li> LID char(2) not null,
 * <li> NAME varchar(255) not null,
 * <li> DESCRIPTION varchar(255) null,
 * <li> primary key (ID),
 * </ul>
 *
 * @package Bitrix\Forum
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupLang_Query query()
 * @method static EO_GroupLang_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_GroupLang_Result getById($id)
 * @method static EO_GroupLang_Result getList(array $parameters = array())
 * @method static EO_GroupLang_Entity getEntity()
 * @method static \Bitrix\Forum\EO_GroupLang createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\EO_GroupLang_Collection createCollection()
 * @method static \Bitrix\Forum\EO_GroupLang wakeUpObject($row)
 * @method static \Bitrix\Forum\EO_GroupLang_Collection wakeUpCollection($rows)
 */
class GroupLangTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_group_lang';
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
			new Entity\IntegerField('FORUM_GROUP_ID'),
			new Entity\StringField('LID', ['size' => 2]),
			new Entity\StringField('NAME', ['size' => 255]),
			new Entity\StringField('DESCRIPTION', ['size' => 255])
		];
	}
}