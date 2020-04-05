<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class ProductGroupAccessTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PRODUCT_ID int mandatory
 * <li> GROUP_ID int mandatory
 * <li> ACCESS_LENGTH int mandatory
 * <li> ACCESS_LENGTH_TYPE string(1) mandatory default 'D'
 * <li> PRODUCT reference to {@link \Bitrix\Catalog\ProductTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 **/

class ProductGroupAccessTable extends Main\Entity\DataManager
{
	const ACCESS_LENGTH_HOUR = 'H';
	const ACCESS_LENGTH_DAY = 'D';
	const ACCESS_LENGTH_WEEK = 'W';
	const ACCESS_LENGTH_MONTH = 'M';
	const ACCESS_LENGTH_QUART = 'Q';
	const ACCESS_LENGTH_SEMIYEAR = 'S';
	const ACCESS_LENGTH_YEAR = 'Y';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_product2group';
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
				'title' => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ID_FIELD')
			)),
			'PRODUCT_ID' => new Main\Entity\IntegerField('PRODUCT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_PRODUCT_ID_FIELD')
			)),
			'GROUP_ID' => new Main\Entity\IntegerField('GROUP_ID', array(
				'required' => true,
				'title' => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_GROUP_ID_FIELD')
			)),
			'ACCESS_LENGTH' => new Main\Entity\IntegerField('ACCESS_LENGTH', array(
				'required' => true,
				'title' => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_FIELD')
			)),
			'ACCESS_LENGTH_TYPE' => new Main\Entity\EnumField('ACCESS_LENGTH_TYPE', array(
				'required' => true,
				'values' => static::getAccessPeriods(false),
				'default_value' => self::ACCESS_LENGTH_DAY,
				'title' => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_TYPE_FIELD')
			)),
			'PRODUCT' => new Main\Entity\ReferenceField(
				'PRODUCT',
				'\Bitrix\Catalog\Product',
				array('=this.PRODUCT_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			)
		);
	}

	/**
	 * Return access period list.
	 *
	 * @param bool $descr			With description.
	 * @return array
	 */
	public static function getAccessPeriods($descr = false)
	{
		if ($descr)
		{
			return array(
				self::ACCESS_LENGTH_HOUR => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_HOUR'),
				self::ACCESS_LENGTH_DAY => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_DAY'),
				self::ACCESS_LENGTH_WEEK => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_WEEK'),
				self::ACCESS_LENGTH_MONTH => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_MONTH'),
				self::ACCESS_LENGTH_QUART => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_QUART'),
				self::ACCESS_LENGTH_SEMIYEAR => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_SEMIYEAR'),
				self::ACCESS_LENGTH_YEAR => Loc::getMessage('PRODUCTGROUPACCESS_ENTITY_ACCESS_LENGTH_YEAR')
			);
		}
		return array(
			self::ACCESS_LENGTH_HOUR,
			self::ACCESS_LENGTH_DAY,
			self::ACCESS_LENGTH_WEEK,
			self::ACCESS_LENGTH_MONTH,
			self::ACCESS_LENGTH_QUART,
			self::ACCESS_LENGTH_SEMIYEAR,
			self::ACCESS_LENGTH_YEAR
		);
	}
}