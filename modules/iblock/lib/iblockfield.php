<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class IblockFieldTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> FIELD_ID string(50) mandatory
 * <li> IS_REQUIRED bool optional default 'N'
 * <li> DEFAULT_VALUE string optional
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IblockField_Query query()
 * @method static EO_IblockField_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IblockField_Result getById($id)
 * @method static EO_IblockField_Result getList(array $parameters = [])
 * @method static EO_IblockField_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_IblockField createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_IblockField_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_IblockField wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_IblockField_Collection wakeUpCollection($rows)
 */
class IblockFieldTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_fields';
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
				'title' => Loc::getMessage('IBLOCK_FIELD_ENTITY_IBLOCK_ID_FIELD'),
			),
			'FIELD_ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateFieldId'),
				'title' => Loc::getMessage('IBLOCK_FIELD_ENTITY_FIELD_ID_FIELD'),
			),
			'IS_REQUIRED' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_FIELD_ENTITY_IS_REQUIRED_FIELD'),
			),
			'DEFAULT_VALUE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_FIELD_ENTITY_DEFAULT_VALUE_FIELD'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID')
			),
		);
	}

	/**
	 * Returns validators for FIELD_ID field.
	 *
	 * @return array
	 */
	public static function validateFieldId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
}
