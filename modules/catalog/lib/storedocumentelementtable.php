<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * Class StoreDocumentElementTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DOC_ID int mandatory
 * <li> STORE_FROM int optional
 * <li> STORE_TO int optional
 * <li> ELEMENT_ID int optional
 * <li> AMOUNT double optional
 * <li> PURCHASING_PRICE double optional
 * <li> BASE_PRICE double optional
 * <li> BASE_PRICE_EXTRA double optional
 * <li> BASE_PRICE_EXTRA_RATE int optional
 * <li> ELEMENT reference to {@link \Bitrix\Iblock\ElementTable}
 * <li> PRODUCT reference to {@link \Bitrix\Catalog\ProductTable}
 * <li> DOCUMENT reference to {@link \Bitrix\Catalog\StoreDocumentTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreDocumentElement_Query query()
 * @method static EO_StoreDocumentElement_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreDocumentElement_Result getById($id)
 * @method static EO_StoreDocumentElement_Result getList(array $parameters = [])
 * @method static EO_StoreDocumentElement_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_StoreDocumentElement createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_StoreDocumentElement_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_StoreDocumentElement wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_StoreDocumentElement_Collection wakeUpCollection($rows)
 */

class StoreDocumentElementTable extends DataManager
{
	public const EXTRA_RATE_PERCENTAGE = 1;
	public const EXTRA_RATE_MONETARY = 2;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_docs_element';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_ID_FIELD'),
				]
			),
			'DOC_ID' => new IntegerField(
				'DOC_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_DOC_ID_FIELD'),
				]
			),
			'STORE_FROM' => new IntegerField(
				'STORE_FROM',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_STORE_FROM_FIELD'),
				]
			),
			'STORE_TO' => new IntegerField(
				'STORE_TO',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_STORE_TO_FIELD'),
				]
			),
			'ELEMENT_ID' => new IntegerField(
				'ELEMENT_ID',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_ELEMENT_ID_FIELD'),
				]
			),
			'AMOUNT' => new FloatField(
				'AMOUNT',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_AMOUNT_FIELD'),
				]
			),
			'PURCHASING_PRICE' => new IntegerField(
				'PURCHASING_PRICE',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_PURCHASING_PRICE_FIELD'),
				]
			),
			'BASE_PRICE' => new IntegerField(
				'BASE_PRICE',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_BASE_PRICE_FIELD'),
				]
			),
			'BASE_PRICE_EXTRA' => new FloatField(
				'BASE_PRICE_EXTRA',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_BASE_PRICE_EXTRA_FIELD'),
				]
			),
			'BASE_PRICE_EXTRA_RATE' => new EnumField(
				'BASE_PRICE_EXTRA_RATE',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ELEMENT_ENTITY_BASE_PRICE_EXTRA_RATE_FIELD'),
					'values' => [
						self::EXTRA_RATE_PERCENTAGE, self::EXTRA_RATE_MONETARY
					],
					'default_value' => self::EXTRA_RATE_PERCENTAGE,
				]
			),
			'ELEMENT' => new Reference(
				'ELEMENT',
				'\Bitrix\Iblock\Element',
				['=this.ELEMENT_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'PRODUCT' => new Reference(
				'PRODUCT',
				'\Bitrix\Catalog\Product',
				['=this.ELEMENT_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'DOCUMENT' => new Reference(
				'DOCUMENT',
				'\Bitrix\Catalog\StoreDocument',
				['=this.DOC_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'STORE_FROM_REF' => new Reference(
				'STORE_FROM_REF',
				'\Bitrix\Catalog\StoreTable',
				['=this.STORE_FROM' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'STORE_TO_REF' => new Reference(
				'STORE_TO_REF',
				'\Bitrix\Catalog\StoreTable',
				['=this.STORE_TO' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Delete all rows for document.
	 * @internal
	 *
	 * @param int $id
	 * @return void
	 */
	public static function deleteByDocument(int $id): void
	{
		if ($id <= 0)
		{
			return;
		}

		$conn = Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from ' . $helper->quote(self::getTableName())
			. ' where ' . $helper->quote('DOC_ID') . ' = ' . $id
		);
		unset($helper, $conn);
	}
}
