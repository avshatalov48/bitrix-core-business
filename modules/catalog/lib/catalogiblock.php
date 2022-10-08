<?php
namespace Bitrix\Catalog;

use Bitrix\Main\ORM,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CatalogIblockTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> YANDEX_EXPORT bool optional default 'N'
 * <li> SUBSCRIPTION bool optional default 'N'
 * <li> VAT_ID int optional
 * <li> PRODUCT_IBLOCK_ID int mandatory
 * <li> SKU_PROPERTY_ID int mandatory
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * <li> PRODUCT_IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CatalogIblock_Query query()
 * @method static EO_CatalogIblock_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CatalogIblock_Result getById($id)
 * @method static EO_CatalogIblock_Result getList(array $parameters = [])
 * @method static EO_CatalogIblock_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_CatalogIblock createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_CatalogIblock_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_CatalogIblock wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_CatalogIblock_Collection wakeUpCollection($rows)
 */

class CatalogIblockTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_iblock';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'IBLOCK_ID' => new ORM\Fields\IntegerField('IBLOCK_ID', array(
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_ENTITY_IBLOCK_ID_FIELD')
			)),
			'YANDEX_EXPORT' => new ORM\Fields\BooleanField('YANDEX_EXPORT', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('IBLOCK_ENTITY_YANDEX_EXPORT_FIELD')
			)),
			'SUBSCRIPTION' => new ORM\Fields\BooleanField('SUBSCRIPTION', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('IBLOCK_ENTITY_SUBSCRIPTION_FIELD')
			)),
			'VAT_ID' => new ORM\Fields\IntegerField('VAT_ID', array(
				'default_value' => 0,
				'title' => Loc::getMessage('IBLOCK_ENTITY_VAT_ID_FIELD')
			)),
			'PRODUCT_IBLOCK_ID' => new ORM\Fields\IntegerField('PRODUCT_IBLOCK_ID', array(
				'default_value' => 0,
				'title' => Loc::getMessage('IBLOCK_ENTITY_PRODUCT_IBLOCK_ID_FIELD'),
			)),
			'SKU_PROPERTY_ID' => new ORM\Fields\IntegerField('SKU_PROPERTY_ID', array(
				'default_value' => 0,
				'title' => Loc::getMessage('IBLOCK_ENTITY_SKU_PROPERTY_ID_FIELD')
			)),
			'IBLOCK' => new ORM\Fields\Relations\Reference(
				'IBLOCK',
				'\Bitrix\Iblock\Iblock',
				array('=this.IBLOCK_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
			'PRODUCT_IBLOCK' => new ORM\Fields\Relations\Reference(
				'PRODUCT_IBLOCK',
				'\Bitrix\Iblock\Iblock',
				array('=this.PRODUCT_IBLOCK_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			)
		);
	}
}