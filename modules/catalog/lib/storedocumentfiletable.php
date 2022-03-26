<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * Class StoreDocumentFileTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DOCUMENT_ID int mandatory
 * <li> FILE_ID int mandatory
 * <li> DOCUMENT reference to {@link \Bitrix\Catalog\StoreDocumentTable}
 * <li> FILE reference to {@link \Bitrix\Main\FileTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 **/

class StoreDocumentFileTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_store_document_file';
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
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_ID_FIELD'),
				]
			),
			'DOCUMENT' => new Reference(
				'DOCUMENT',
				'\Bitrix\Catalog\StoreDocument',
				['=this.DOC_ID' => 'ref.ID'],
			),
			'DOCUMENT_ID' => new IntegerField(
				'DOCUMENT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('STORE_DOCUMENT_FILE_ENTITY_DOCUMENT_ID_FIELD'),
				]
			),
			'FILE' => new Reference(
				'DOCUMENT',
				'\Bitrix\Main\FileTable',
				['=this.FILE_ID' => 'ref.ID'],
			),
			'FILE_ID' => new IntegerField(
				'FILE_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('STORE_DOCUMENT_FILE_ENTITY_FILE_ID_FIELD'),
				]
			),
		];
	}
}
