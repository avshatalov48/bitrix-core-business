<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class BasketPropsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> BASKET_ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> VALUE string(255) optional
 * <li> CODE string(255) optional
 * <li> SORT int optional default 100
 * </ul>
 *
 * @package Bitrix\Sale
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BasketProperty_Query query()
 * @method static EO_BasketProperty_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BasketProperty_Result getById($id)
 * @method static EO_BasketProperty_Result getList(array $parameters = array())
 * @method static EO_BasketProperty_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_BasketProperty createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_BasketProperty_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_BasketProperty wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_BasketProperty_Collection wakeUpCollection($rows)
 */

class BasketPropertyTable
	extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_basket_props';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField(
				'ID',
				 array(
					 'autocomplete' => true,
					 'primary' => true,
				 )
			),
			new Main\Entity\IntegerField(
				'BASKET_ID',
				 array(
					 'required' => true,
				 )
			),
			new Main\Entity\StringField(
				'NAME',
				array(
					'size' => 255,
					'validation' => array(__CLASS__, 'validateName'),
				)
			),
			new Main\Entity\StringField(
				'VALUE',
				array(
					'size' => 255,
					'validation' => array(__CLASS__, 'validateValue'),
				)
			),
			new Main\Entity\StringField(
				'CODE',
				array(
					'size' => 255,
					'validation' => array(__CLASS__, 'validateCode'),
				)
			),

			new Main\Entity\IntegerField(
				'SORT'
			),
			new Main\Entity\ReferenceField(
				'BASKET',
				'Bitrix\Sale\Internals\Basket',
				array(
					'=this.BASKET_ID' => 'ref.ID'
				)
			),

			new Main\Entity\StringField(
				'XML_ID',
				array(
					'size' => 255,
				)
			),
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
	/**
	 * Returns validators for VALUE field.
	 *
	 * @return array
	 */
	public static function validateValue()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}