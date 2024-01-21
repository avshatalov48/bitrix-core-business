<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Sale\Internals\ShipmentItemStoreTable;

/**
 * Class StoreBatchDocumentElementTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreBatchDocumentElement_Query query()
 * @method static EO_StoreBatchDocumentElement_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreBatchDocumentElement_Result getById($id)
 * @method static EO_StoreBatchDocumentElement_Result getList(array $parameters = [])
 * @method static EO_StoreBatchDocumentElement_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_StoreBatchDocumentElement createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_StoreBatchDocumentElement_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_StoreBatchDocumentElement wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_StoreBatchDocumentElement_Collection wakeUpCollection($rows)
 */
class StoreBatchDocumentElementTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_store_batch_docs_element';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true
				]
			),
			new IntegerField(
				'PRODUCT_BATCH_ID',
				[
					'required' => true,
				]
			),
			new ReferenceField(
				'PRODUCT_BATCH',
				\Bitrix\Catalog\StoreBatchTable::class,
				['=this.STORE_ID' => 'ref.ID'],
				['join_type' => 'INNER']
			),
			new IntegerField('DOCUMENT_ELEMENT_ID'),
			new ReferenceField(
				'DOCUMENT_ELEMENT',
				\Bitrix\Catalog\StoreDocumentElementTable::class,
				['=this.DOCUMENT_ELEMENT_ID' => 'ref.ID'],
				['join_type' => 'INNER']
			),
			new \Bitrix\Main\Entity\IntegerField('SHIPMENT_ITEM_STORE_ID'),
			new ReferenceField(
				'SHIPMENT_ITEM_STORE',
				ShipmentItemStoreTable::class,
				['=this.SHIPMENT_ITEM_STORE_ID' => 'ref.ID'],
				['join_type' => 'INNER']
			),
			new  FloatField(
				'AMOUNT',
				array(
					'required' => true
				)
			),
			(new FloatField('BATCH_PRICE'))
				->configureDefaultValue(0.00)
			,
			(new StringField('BATCH_CURRENCY'))
				->configureSize(3)
			,
		];
	}
}
