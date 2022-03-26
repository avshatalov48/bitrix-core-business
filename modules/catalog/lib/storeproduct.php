<?php
namespace Bitrix\Catalog;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;

/**
 * Class StoreProductTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PRODUCT_ID int mandatory
 * <li> AMOUNT double mandatory
 * <li> STORE_ID int mandatory
 * <li> QUANTITY_RESERVED double optional default 0
 * <li> STORE reference to {@link \Bitrix\Catalog\StoreTable}
 * <li> PRODUCT reference to {@link \Bitrix\Catalog\ProductTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreProduct_Query query()
 * @method static EO_StoreProduct_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_StoreProduct_Result getById($id)
 * @method static EO_StoreProduct_Result getList(array $parameters = array())
 * @method static EO_StoreProduct_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_StoreProduct createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_StoreProduct_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_StoreProduct wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_StoreProduct_Collection wakeUpCollection($rows)
 */

class StoreProductTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_store_product';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new ORM\Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_ID_FIELD'),
				]
			),
			'STORE_ID' => new ORM\Fields\IntegerField(
				'STORE_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_STORE_ID_FIELD'),
				]
			),
			'PRODUCT_ID' => new ORM\Fields\IntegerField(
				'PRODUCT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_PRODUCT_ID_FIELD'),
				]
			),
			'AMOUNT' => new ORM\Fields\FloatField(
				'AMOUNT',
				[
					'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_AMOUNT_FIELD'),
				]
			),
			'QUANTITY_RESERVED' => new ORM\Fields\FloatField(
				'QUANTITY_RESERVED',
				[
					'title' => Loc::getMessage('STORE_PRODUCT_ENTITY_QUANTITY_RESERVED_FIELD'),
				],
			),
			'STORE' => new ORM\Fields\Relations\Reference(
				'STORE',
				StoreTable::class,
				ORM\Query\Join::on('this.STORE_ID', 'ref.ID')
			),
			'PRODUCT' => new ORM\Fields\Relations\Reference(
				'PRODUCT',
				ProductTable::class,
				ORM\Query\Join::on('this.PRODUCT_ID', 'ref.ID')
			),
		];
	}

	/**
	 * Delete all rows for product.
	 * @internal
	 *
	 * @param int $id       Product id.
	 * @return void
	 */
	public static function deleteByProduct(int $id): void
	{
		if ($id <= 0)
		{
			return;
		}

		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from ' . $helper->quote(self::getTableName())
			. ' where ' . $helper->quote('PRODUCT_ID') . ' = ' . $id
		);
		unset($helper, $conn);
	}
}
