<?php
namespace Bitrix\Catalog;

use Bitrix\Crm\DealTable;
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class ProductCompilationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DEAL_ID int mandatory
 * <li> PRODUCT_IDS text mandatory
 * <li> CREATION_DATE datetime mandatory
 * <li> CHAT_ID int optional
 * <li> QUEUE_ID int optional
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ProductCompilation_Query query()
 * @method static EO_ProductCompilation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ProductCompilation_Result getById($id)
 * @method static EO_ProductCompilation_Result getList(array $parameters = [])
 * @method static EO_ProductCompilation_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_ProductCompilation createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_ProductCompilation_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_ProductCompilation wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_ProductCompilation_Collection wakeUpCollection($rows)
 */

class ProductCompilationTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_product_compilation';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('PRODUCT_COMPILATION_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'DEAL_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('PRODUCT_COMPILATION_ENTITY_DEAL_ID_FIELD')
				]
			),
			new TextField(
				'PRODUCT_IDS',
				[
					'required' => true,
					'title' => Loc::getMessage('PRODUCT_COMPILATION_ENTITY_PRODUCT_IDS_FIELD')
				]
			),
			new DatetimeField(
				'CREATION_DATE',
				[
					'required' => true,
					'title' => Loc::getMessage('PRODUCT_COMPILATION_ENTITY_PRODUCT_IDS_FIELD')
				]
			),
			new IntegerField(
				'CHAT_ID',
				[
					'required' => false,
					'title' => Loc::getMessage('PRODUCT_COMPILATION_ENTITY_CHAT_ID_FIELD')
				]
			),
			new IntegerField(
				'QUEUE_ID',
				[
					'required' => false,
					'title' => Loc::getMessage('PRODUCT_COMPILATION_ENTITY_QUEUE_ID_FIELD')
				]
			),
		];
	}
}
