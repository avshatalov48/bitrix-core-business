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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreBarcode_Query query()
 * @method static EO_StoreBarcode_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreBarcode_Result getById($id)
 * @method static EO_StoreBarcode_Result getList(array $parameters = [])
 * @method static EO_StoreBarcode_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_StoreBarcode createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_StoreBarcode_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_StoreBarcode wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_StoreBarcode_Collection wakeUpCollection($rows)
 */

class StoreBarcodeTable extends Main\ORM\Data\DataManager
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
			'ID' => new Main\ORM\Fields\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_ID_FIELD')
			)),
			'PRODUCT_ID' => new Main\ORM\Fields\IntegerField('PRODUCT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_PRODUCT_ID_FIELD')
			)),
			'BARCODE' => new Main\ORM\Fields\StringField('BARCODE', array(
				'required' => true,
				'unique' => true,
				'validation' => function()
					{
						return [
							new Main\ORM\Fields\Validators\LengthValidator(null, 100),
							new Main\ORM\Fields\Validators\UniqueValidator(
								Loc::getMessage("STORE_BARCODE_ENTITY_BARCODE_IS_NOT_UNIQUE")
							),
						];
					},
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_BARCODE_FIELD')
			)),
			'STORE_ID' => new Main\ORM\Fields\IntegerField('STORE_ID', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_STORE_ID_FIELD')
			)),
			'ORDER_ID' => new Main\ORM\Fields\IntegerField('ORDER_ID', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_ORDER_ID_FIELD')
			)),
			'DATE_MODIFY' => new Main\ORM\Fields\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_DATE_MODIFY_FIELD')
			)),
			'DATE_CREATE' => new Main\ORM\Fields\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_DATE_CREATE_FIELD')
			)),
			'CREATED_BY' => new Main\ORM\Fields\IntegerField('CREATED_BY', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_CREATED_BY_FIELD')
			)),
			'MODIFIED_BY' => new Main\ORM\Fields\IntegerField('MODIFIED_BY', array(
				'title' => Loc::getMessage('STORE_BARCODE_ENTITY_MODIFIED_BY_FIELD')
			)),
			'PRODUCT' => new Main\ORM\Fields\Relations\Reference(
				'PRODUCT',
				'\Bitrix\Catalog\Product',
				array('=this.PRODUCT_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'STORE' => new Main\ORM\Fields\Relations\Reference(
				'STORE',
				'\Bitrix\Catalog\Store',
				array('=this.STORE_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'CREATED_BY_USER' => new Main\ORM\Fields\Relations\Reference(
				'CREATED_BY_USER',
				'\Bitrix\Main\User',
				array('=this.CREATED_BY' => 'ref.ID')
			),
			'MODIFIED_BY_USER' => new Main\ORM\Fields\Relations\Reference(
				'MODIFIED_BY_USER',
				'\Bitrix\Main\User',
				array('=this.MODIFIED_BY' => 'ref.ID')
			)
		);
	}
}
