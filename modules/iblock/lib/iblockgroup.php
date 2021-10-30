<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class IblockGroupTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> GROUP_ID int mandatory
 * <li> PERMISSION string(1) mandatory
 * <li> GROUP reference to {@link \Bitrix\Main\GroupTable}
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IblockGroup_Query query()
 * @method static EO_IblockGroup_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_IblockGroup_Result getById($id)
 * @method static EO_IblockGroup_Result getList(array $parameters = array())
 * @method static EO_IblockGroup_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_IblockGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_IblockGroup_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_IblockGroup wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_IblockGroup_Collection wakeUpCollection($rows)
 */

class IblockGroupTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_group';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_GROUP_ENTITY_IBLOCK_ID_FIELD'),
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_GROUP_ENTITY_GROUP_ID_FIELD'),
			),
			'PERMISSION' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validatePermission'),
				'title' => Loc::getMessage('IBLOCK_GROUP_ENTITY_PERMISSION_FIELD'),
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Main\Group',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for PERMISSION field.
	 *
	 * @return array
	 */
	public static function validatePermission()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}
}