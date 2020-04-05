<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class StoreBarcodeTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PRODUCT_ID int mandatory
 * <li> BARCODE string(100) mandatory
 * <li> STORE_ID int optional
 * <li> ORDER_ID int optional
 * <li> DATE_MODIFY datetime optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * <li> PRODUCT reference to {@link \Bitrix\Catalog\Product}
 * <li> STORE reference to {@link \Bitrix\Catalog\Store}
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\User}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\User}
 * </ul>
 *
 * @package Bitrix\Catalog
 **/

class StoreBarcodeTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_store_barcode';
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
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_ID_FIELD')
			)),
			'PRODUCT_ID' => new Main\Entity\IntegerField('PRODUCT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_PRODUCT_ID_FIELD')
			)),
			'BARCODE' => new Main\Entity\StringField('BARCODE', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateBarcode'),
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_BARCODE_FIELD')
			)),
			'STORE_ID' => new Main\Entity\IntegerField('STORE_ID', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_STORE_ID_FIELD')
			)),
			'ORDER_ID' => new Main\Entity\IntegerField('ORDER_ID', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_ORDER_ID_FIELD')
			)),
			'DATE_MODIFY' => new Main\Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_DATE_MODIFY_FIELD')
			)),
			'DATE_CREATE' => new Main\Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_DATE_CREATE_FIELD')
			)),
			'CREATED_BY' => new Main\Entity\IntegerField('CREATED_BY', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_CREATED_BY_FIELD')
			)),
			'MODIFIED_BY' => new Main\Entity\IntegerField('MODIFIED_BY', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_MODIFIED_BY_FIELD')
			)),
			'PRODUCT' => new Main\Entity\ReferenceField(
				'PRODUCT',
				'\Bitrix\Catalog\Product',
				array('=this.PRODUCT_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'STORE' => new Main\Entity\ReferenceField(
				'STORE',
				'\Bitrix\Catalog\Store',
				array('=this.STORE_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'CREATED_BY_USER' => new Main\Entity\ReferenceField(
				'CREATED_BY_USER',
				'\Bitrix\Main\User',
				array('=this.CREATED_BY' => 'ref.ID')
			),
			'MODIFIED_BY_USER' => new Main\Entity\ReferenceField(
				'MODIFIED_BY_USER',
				'\Bitrix\Main\User',
				array('=this.MODIFIED_BY' => 'ref.ID')
			)
		);
	}
	/**
	 * Returns validators for BARCODE field.
	 *
	 * @return array
	 */
	public static function validateBarcode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}
}