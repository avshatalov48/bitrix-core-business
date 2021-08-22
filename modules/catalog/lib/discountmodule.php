<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class DiscountModuleTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DISCOUNT_ID int mandatory
 * <li> MODULE_ID string(50) mandatory
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DiscountModule_Query query()
 * @method static EO_DiscountModule_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_DiscountModule_Result getById($id)
 * @method static EO_DiscountModule_Result getList(array $parameters = array())
 * @method static EO_DiscountModule_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_DiscountModule createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_DiscountModule_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_DiscountModule wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_DiscountModule_Collection wakeUpCollection($rows)
 */

class DiscountModuleTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_discount_module';
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
				'title' => Loc::getMessage('DISCOUNT_MODULE_ENTITY_ID_FIELD')
			)),
			'DISCOUNT_ID' => new Main\Entity\IntegerField('DISCOUNT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_MODULE_ENTITY_DISCOUNT_ID_FIELD')
			)),
			'MODULE_ID' => new Main\Entity\StringField('MODULE_ID', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateModuleId'),
				'title' => Loc::getMessage('DISCOUNT_MODULE_ENTITY_MODULE_ID_FIELD')
			))
		);
	}
	/**
	 * Returns validators for MODULE_ID field.
	 *
	 * @return array
	 */
	public static function validateModuleId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Delete modules by discount.
	 *
	 * @param int $discount			Discount id.
	 * @return void
	 */
	public static function deleteByDiscount($discount)
	{
		$discount = (int)$discount;
		if ($discount <= 0)
			return;
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from '.$helper->quote(self::getTableName()).' where '.$helper->quote('DISCOUNT_ID').' = '.$discount
		);
		unset($helper, $conn);
	}
}