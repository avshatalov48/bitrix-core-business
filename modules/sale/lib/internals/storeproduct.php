<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 *
 * @ignore
 * @see \Bitrix\Catalog\StoreProductTable
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!Main\Loader::includeModule('catalog'))
{
	return;
}

Loc::loadMessages(__FILE__);

/**
 * Class StoreProductTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreProduct_Query query()
 * @method static EO_StoreProduct_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreProduct_Result getById($id)
 * @method static EO_StoreProduct_Result getList(array $parameters = [])
 * @method static EO_StoreProduct_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_StoreProduct createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_StoreProduct_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_StoreProduct wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_StoreProduct_Collection wakeUpCollection($rows)
 */
class StoreProductTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_catalog_store_product';
	}

	public static function getMap()
	{
		global $DB;
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'PRODUCT_ID' => array(
				'data_type' => 'integer'
			),
			'SALE_PRODUCT' => array(
				'data_type' => 'Product',
				'reference' => array('=this.PRODUCT_ID' => 'ref.ID')
			),
			'AMOUNT' => array(
				'data_type' => 'float'
			),
			'STORE_ID' => array(
				'data_type' => 'integer',
			),
			'STORE' => array(
				'data_type' => 'Bitrix\Catalog\Store',
				'reference' => array('=this.STORE_ID' => 'ref.ID')
			)
		);

		return $fieldsMap;
	}
}
