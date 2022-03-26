<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class DocsBarcodeTable
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
 **/

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
/*			'DOC_ID' => new IntegerField(
				'DOC_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_BARCODE_ENTITY_DOC_ID_FIELD'),
				]
			), */
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
					'validation' => [__CLASS__, 'validateBarcode'],
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_BARCODE_ENTITY_BARCODE_FIELD'),
				]
			),
/*			'DOCUMENT' => new Reference(
				'DOCUMENT',
				'\Bitrix\Catalog\StoreDocument',
				['=this.DOC_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			), */
			'DOCUMENT_ELEMENT' => new Reference(
				'DOCUMENT_ELEMENT',
				'\Bitrix\Catalog\StoreDocumentElement',
				['=this.DOC_ELEMENT_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Returns validators for BARCODE field.
	 *
	 * @return array
	 */
	public static function validateBarcode(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}
}
