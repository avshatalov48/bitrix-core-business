<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class ExtraTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(50) mandatory
 * <li> PERCENTAGE double mandatory
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Extra_Query query()
 * @method static EO_Extra_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Extra_Result getById($id)
 * @method static EO_Extra_Result getList(array $parameters = array())
 * @method static EO_Extra_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Extra createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Extra_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Extra wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Extra_Collection wakeUpCollection($rows)
 */

class ExtraTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_extra';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('EXTRA_ENTITY_ID_FIELD')
			)),
			'NAME' => new Main\Entity\StringField('NAME', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('EXTRA_ENTITY_NAME_FIELD')
			)),
			'PERCENTAGE' => new Main\Entity\FloatField('PERCENTAGE', array(
				'required' => true,
				'title' => Loc::getMessage('EXTRA_ENTITY_PERCENTAGE_FIELD'),
			))
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
			new Main\Entity\Validator\Length(null, 50),
		);
	}
}