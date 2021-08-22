<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class DiscountRestrictionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DISCOUNT_ID int mandatory
 * <li> ACTIVE bool optional
 * <li> USER_GROUP_ID int mandatory default -1
 * <li> PRICE_TYPE_ID int mandatory default -1
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DiscountRestriction_Query query()
 * @method static EO_DiscountRestriction_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_DiscountRestriction_Result getById($id)
 * @method static EO_DiscountRestriction_Result getList(array $parameters = array())
 * @method static EO_DiscountRestriction_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_DiscountRestriction createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_DiscountRestriction_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_DiscountRestriction wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_DiscountRestriction_Collection wakeUpCollection($rows)
 */

class DiscountRestrictionTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_discount_cond';
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
				'title' => Loc::getMessage('DISCOUNT_RESTRICTIONS_ENTITY_ID_FIELD')
			)),
			'DISCOUNT_ID' => new Main\Entity\IntegerField('DISCOUNT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_RESTRICTIONS_ENTITY_DISCOUNT_ID_FIELD')
			)),
			'ACTIVE' => new Main\Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('DISCOUNT_RESTRICTIONS_ENTITY_ACTIVE_FIELD')
			)),
			'USER_GROUP_ID' => new Main\Entity\IntegerField('USER_GROUP_ID', array(
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_RESTRICTIONS_ENTITY_USER_GROUP_ID_FIELD')
			)),
			'PRICE_TYPE_ID' => new Main\Entity\IntegerField('PRICE_TYPE_ID', array(
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_RESTRICTIONS_ENTITY_PRICE_TYPE_ID_FIELD')
			)),
			'DISCOUNT' => new Main\Entity\ReferenceField(
				'DISCOUNT',
				'\Bitrix\Catalog\Discount',
				array('=this.DISCOUNT_ID' => 'ref.ID')
			)
		);
	}

	/**
	 * Change active flag in table by discount.
	 *
	 * @param int $discount			Discount id.
	 * @param string $active		Discount active flag.
	 * @return void
	 */
	public static function changeActiveByDiscount($discount, $active)
	{
		$discount = (int)$discount;
		$active = (string)$active;
		if ($discount <= 0 || ($active != 'Y' && $active != 'N'))
			return;
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'update '.$helper->quote(self::getTableName()).
			' set '.$helper->quote('ACTIVE').' = \''.$active.'\' where '.
			$helper->quote('DISCOUNT_ID').' = '.$discount
		);
		unset($helper, $conn);
	}

	/**
	 * Delete restriction list by discount.
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