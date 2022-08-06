<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Type;

/**
 * Class VatTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 100
 * <li> NAME string(50) mandatory
 * <li> RATE double mandatory default 0.00
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Vat_Query query()
 * @method static EO_Vat_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Vat_Result getById($id)
 * @method static EO_Vat_Result getList(array $parameters = array())
 * @method static EO_Vat_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Vat createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Vat_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Vat wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Vat_Collection wakeUpCollection($rows)
 */

class VatTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_vat';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('VAT_ENTITY_ID_FIELD'),
				]
			),
			'TIMESTAMP_X' => new Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'required' => true,
					'default_value' => function()
					{
						return new Type\DateTime();
					},
					'title' => Loc::getMessage('VAT_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			'ACTIVE' => new Fields\BooleanField(
				'ACTIVE',
				[
					'values' => [
						'N',
						'Y',
					],
					'default_value' => 'Y',
					'title' => Loc::getMessage('VAT_ENTITY_ACTIVE_FIELD'),
				]
			),
			'SORT' => new Fields\IntegerField(
				'SORT',
				[
					'column_name' => 'C_SORT',
					'default_value' => 100,
					'title' => Loc::getMessage('VAT_ENTITY_SORT_FIELD'),
				]
			),
			'NAME' => new Fields\StringField(
				'NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new Fields\Validators\LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('VAT_ENTITY_NAME_FIELD'),
				]
			),
			'RATE' => new Fields\FloatField(
				'RATE',
				[
					'required' => true,
					'title' => Loc::getMessage('VAT_ENTITY_RATE_FIELD'),
				]
			),
		];
	}

	/**
	 * Returns the ID of the active VAT rate for the specified value. If necessary, it creates a new VAT rate.
	 *
	 * @param float $rate Vat rate value.
	 * @param bool $create Create new vat, if not exists.
	 * @return int|null
	 */
	public static function getActiveVatIdByRate(float $rate, bool $create = false): ?int
	{
		if ($rate < 0 || $rate > 100)
		{
			return null;
		}
		$row = static::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=RATE' => $rate,
			],
		])->fetch();
		if (!empty($row))
		{
			return (int)$row['ID'];
		}

		if ($create)
		{
			$result = static::add([
				'ACTIVE' => 'Y',
				'NAME' => $rate . '%',
				'RATE' => $rate,
			]);

			return $result->isSuccess() ? (int)$result->getId() : null;
		}

		return null;
	}
}
