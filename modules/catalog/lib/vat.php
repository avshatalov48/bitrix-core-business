<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
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
 * @method static EO_Vat_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Vat_Result getById($id)
 * @method static EO_Vat_Result getList(array $parameters = [])
 * @method static EO_Vat_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Vat createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Vat_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Vat wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Vat_Collection wakeUpCollection($rows)
 */

class VatTable extends ORM\Data\DataManager
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
			'ID' => new ORM\Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('VAT_ENTITY_ID_FIELD'),
				]
			),
			'TIMESTAMP_X' => new ORM\Fields\DatetimeField(
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
			'ACTIVE' => new ORM\Fields\BooleanField(
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
			'SORT' => new ORM\Fields\IntegerField(
				'SORT',
				[
					'column_name' => 'C_SORT',
					'default_value' => 100,
					'title' => Loc::getMessage('VAT_ENTITY_SORT_FIELD'),
				]
			),
			'NAME' => new ORM\Fields\StringField(
				'NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new ORM\Fields\Validators\LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('VAT_ENTITY_NAME_FIELD'),
				]
			),
			'RATE' => new ORM\Fields\FloatField(
				'RATE',
				[
					'nullable' => true,
					'title' => Loc::getMessage('VAT_ENTITY_RATE_FIELD'),
				]
			),
			'EXCLUDE_VAT' => new ORM\Fields\BooleanField(
				'EXCLUDE_VAT',
				[
					'values' => [
						'N',
						'Y',
					],
					'default_value' => 'N',
					'title' => Loc::getMessage('VAT_ENTITY_ACTIVE_FIELD'),
				]
			),
			'XML_ID' => new ORM\Fields\StringField(
				'XML_ID',
				[
					'required' => false,
					'validation' => function()
					{
						return [
							new ORM\Fields\Validators\LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('VAT_ENTITY_XML_ID_FIELD'),
				]
			),
		];
	}

	/**
	 * Default onBeforeAdd handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for add.
	 * @return ORM\EventResult
	 */
	public static function onBeforeAdd(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult;
		$fields = $event->getParameter('fields');

		if (isset($fields['EXCLUDE_VAT']) && $fields['EXCLUDE_VAT'] === 'Y')
		{
			if (static::isExistsExcludeVat())
			{
				$result->addError(
					new ORM\EntityError(Loc::getMessage('VAT_ENTITY_ERR_EXCLUDE_VAT_ALREADY_EXISTS'))
				);

				return $result;
			}

			$result->modifyFields([
				'RATE' => null,
			]);
		}

		return $result;
	}

	/**
	 * Default onBeforeUpdate handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for update.
	 * @return ORM\EventResult
	 */
	public static function onBeforeUpdate(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult;
		$fields = $event->getParameter('fields');

		if (isset($fields['EXCLUDE_VAT']) && $fields['EXCLUDE_VAT'] === 'Y')
		{
			$id = (int)$event->getParameter('primary')['ID'];

			$excludeId = static::getExcludeVatId();

			if ($excludeId !== null && $excludeId !== $id)
			{
				$result->addError(
					new ORM\EntityError(Loc::getMessage('VAT_ENTITY_ERR_EXCLUDE_VAT_ALREADY_EXISTS'))
				);

				return $result;
			}

			$result->modifyFields([
				'RATE' => null,
			]);
		}

		return $result;
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

	/**
	 * Returns true, if system vat exists.
	 *
	 * @return bool
	 */
	public static function isExistsExcludeVat(): bool
	{
		return (static::getExcludeVatId() !== null);
	}

	/**
	 * Returns excluded vat id, if exists.
	 *
	 * @return int|null
	 */
	public static function getExcludeVatId(): ?int
	{
		$iterator = static::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=EXCLUDE_VAT' => 'Y',
			],
			'limit' => 1,
		]);
		$row = $iterator->fetch();
		unset($iterator);

		return (!empty($row) ? (int)$row['ID'] : null);
	}
}
