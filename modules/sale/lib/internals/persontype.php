<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PersonTypeTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> LID string(2) mandatory
 * <li> NAME string(255) mandatory
 * <li> SORT int optional default 150
 * <li> ACTIVE bool optional default 'Y'
 * </ul>
 *
 * @package Bitrix\Sale
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PersonType_Query query()
 * @method static EO_PersonType_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PersonType_Result getById($id)
 * @method static EO_PersonType_Result getList(array $parameters = array())
 * @method static EO_PersonType_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PersonType createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PersonType_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PersonType wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PersonType_Collection wakeUpCollection($rows)
 */

class PersonTypeTable extends Main\Entity\DataManager
{
	/**
	 * Returns path to the file which contains definition of the class.
	 *
	 * @return string
	 */
	public static function getFilePath()
	{
		return __FILE__;
	}

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_person_type';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateLid'),
			),
			new Main\Entity\ReferenceField(
				'PERSON_TYPE_SITE',
				'\Bitrix\Sale\Internals\PersonTypeSiteTable',
				array('=this.ID' => 'ref.PERSON_TYPE_ID'),
				array('join_type' => 'LEFT')
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'SORT' => array(
				'data_type' => 'integer'
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'XML_ID' => array(
				'data_type' => 'string',
			),
			'ENTITY_REGISTRY_TYPE' => array(
				'data_type' => 'string',
			),
		);
	}

	/**
	 * Returns validators for LID field.
	 *
	 * @return array
	 */
	public static function validateLid()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}
