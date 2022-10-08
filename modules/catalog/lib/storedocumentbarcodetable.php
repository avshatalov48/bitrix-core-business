<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class StoreDocumentBarcodeTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DOC_ID int mandatory
 * <li> DOC_ELEMENT_ID int mandatory
 * <li> BARCODE string(100) mandatory
 * <li> DOCUMENT reference to {@link \Bitrix\Catalog\StoreDocumentTable}
 * <li> DOCUMENT_ELEMENT reference to {@link \Bitrix\Catalog\StoreDocumentElementTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreDocumentBarcode_Query query()
 * @method static EO_StoreDocumentBarcode_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreDocumentBarcode_Result getById($id)
 * @method static EO_StoreDocumentBarcode_Result getList(array $parameters = [])
 * @method static EO_StoreDocumentBarcode_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_StoreDocumentBarcode createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_StoreDocumentBarcode wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection wakeUpCollection($rows)
 */

class StoreDocumentBarcodeTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_docs_barcode';
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
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_BARCODE_ENTITY_ID_FIELD'),
				]
			),
			'DOC_ID' => new IntegerField(
				'DOC_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_BARCODE_ENTITY_DOC_ID_FIELD'),
				]
			),
			'DOC_ELEMENT_ID' => new IntegerField(
				'DOC_ELEMENT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_BARCODE_ENTITY_DOC_ELEMENT_ID_FIELD'),
				]
			),
			'BARCODE' => new StringField(
				'BARCODE',
				[
					'required' => true,
					'validation' => function()
						{
							return [
								new LengthValidator(null, 100),
							];
						},
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_BARCODE_ENTITY_BARCODE_FIELD'),
				]
			),
			'DOCUMENT' => new Reference(
				'DOCUMENT',
				'\Bitrix\Catalog\StoreDocument',
				['=this.DOC_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'DOCUMENT_ELEMENT' => new Reference(
				'DOCUMENT_ELEMENT',
				'\Bitrix\Catalog\StoreDocumentElement',
				['=this.DOC_ELEMENT_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Delete all barcodes for document.
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
