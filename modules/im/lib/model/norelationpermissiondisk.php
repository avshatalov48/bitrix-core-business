<?php
namespace Bitrix\Im\Model;

use \Bitrix\Main\Type\DateTime,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Entity\DataManager,
	\Bitrix\Main\Entity\IntegerField,
	\Bitrix\Main\Entity\DatetimeField;
Loc::loadMessages(__FILE__);

/**
 * Class NoRelationPermissionDiskTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHAT_ID int optional
 * <li> USER_ID int optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_NoRelationPermissionDisk_Query query()
 * @method static EO_NoRelationPermissionDisk_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_NoRelationPermissionDisk_Result getById($id)
 * @method static EO_NoRelationPermissionDisk_Result getList(array $parameters = array())
 * @method static EO_NoRelationPermissionDisk_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_NoRelationPermissionDisk createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_NoRelationPermissionDisk_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_NoRelationPermissionDisk wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_NoRelationPermissionDisk_Collection wakeUpCollection($rows)
 */

class NoRelationPermissionDiskTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_no_relation_permission_disk';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_ID_FIELD'),
			)),
			new IntegerField('CHAT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_CHAT_ID_FIELD'),
			)),
			new IntegerField('USER_ID', array(
				'required' => true,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_USER_ID_FIELD'),
			)),
			new DatetimeField('ACTIVE_TO', array(
				'required' => true,
				'default_value' => new DateTime,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_ACTIVE_TO_FIELD'),
			)),
		);
	}
}