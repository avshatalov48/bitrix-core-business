<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * Class StoreBatchTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreBatch_Query query()
 * @method static EO_StoreBatch_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreBatch_Result getById($id)
 * @method static EO_StoreBatch_Result getList(array $parameters = [])
 * @method static EO_StoreBatch_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_StoreBatch createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_StoreBatch_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_StoreBatch wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_StoreBatch_Collection wakeUpCollection($rows)
 */
class StoreBatchTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_store_batch';
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
				"ELEMENT_ID",
				[
					'required' => true,
				]
			),
			new IntegerField(
				"STORE_ID",
				[
					'required' => true,
				]
			),
			new ReferenceField(
				"STORE",
				\Bitrix\Catalog\StoreTable::class,
				['=this.STORE_ID' => 'ref.ID'],
				['join_type' => 'INNER']
			),
			(new FloatField('AVAILABLE_AMOUNT'))
				->configureDefaultValue(0.00)
			,
			(new FloatField('PURCHASING_PRICE'))
				->configureDefaultValue(0.00)
			,
			(new StringField('PURCHASING_CURRENCY'))
				->configureSize(3)
			,
		];
	}
}
