<?php

namespace Bitrix\Currency;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Class CurrencyRateTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CURRENCY string(3) mandatory
 * <li> DATE_RATE date mandatory
 * <li> RATE_CNT int optional default 1
 * <li> RATE float mandatory default 0.0000
 * <li> CREATED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> MODIFIED_BY int optional
 * <li> TIMESTAMP_X datetime optional
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Currency
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CurrencyRate_Query query()
 * @method static EO_CurrencyRate_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CurrencyRate_Result getById($id)
 * @method static EO_CurrencyRate_Result getList(array $parameters = [])
 * @method static EO_CurrencyRate_Entity getEntity()
 * @method static \Bitrix\Currency\EO_CurrencyRate createObject($setDefaultValues = true)
 * @method static \Bitrix\Currency\EO_CurrencyRate_Collection createCollection()
 * @method static \Bitrix\Currency\EO_CurrencyRate wakeUpObject($row)
 * @method static \Bitrix\Currency\EO_CurrencyRate_Collection wakeUpCollection($rows)
 */

class CurrencyRateTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_currency_rate';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => (new ORM\Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true)
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_ID_FIELD'))
			,
			'CURRENCY' => (new ORM\Fields\StringField('CURRENCY'))
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 3))
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_CURRENCY_FIELD'))
			,
			'BASE_CURRENCY' => (new ORM\Fields\StringField('BASE_CURRENCY'))
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_BASE_CURRENCY_FIELD'))
			,
			'DATE_RATE' => (new ORM\Fields\DateField('DATE_RATE'))
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_DATE_RATE_FIELD'))
			,
			'RATE_CNT' => (new ORM\Fields\IntegerField('RATE_CNT'))
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_RATE_CNT_FIELD'))
			,
			'RATE' => (new ORM\Fields\FloatField('RATE'))
				->configureRequired(true)
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_RATE_FIELD'))
			,
			'CREATED_BY' => (new ORM\Fields\IntegerField('CREATED_BY'))
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_CREATED_BY_FIELD'))
			,
			'DATE_CREATE' => (new ORM\Fields\DatetimeField('DATE_CREATE'))
				->configureDefaultValue(static fn() => new Type\DateTime())
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_DATE_CREATE_FIELD'))
			,
			'MODIFIED_BY' => (new ORM\Fields\IntegerField('MODIFIED_BY'))
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_MODIFIED_BY_FIELD'))
			,
			'TIMESTAMP_X' => (new ORM\Fields\DatetimeField('TIMESTAMP_X'))
				->configureRequired(true)
				->configureDefaultValue(static fn() => new Type\DateTime())
				->configureTitle(Loc::getMessage('CURRENCY_RATE_ENTITY_TIMESTAMP_X_FIELD'))
			,
			'CREATED_BY_USER' => (new ORM\Fields\Relations\Reference(
					'CREATED_BY_USER',
					'Bitrix\Main\User',
					ORM\Query\Join::on('this.CREATED_BY', 'ref.ID')
				))->configureJoinType(ORM\Query\Join::TYPE_LEFT)
			,
			'MODIFIED_BY_USER' => (new ORM\Fields\Relations\Reference(
					'MODIFIED_BY_USER',
					'Bitrix\Main\User',
					ORM\Query\Join::on('this.MODIFIED_BY', 'ref.ID')
				))->configureJoinType(ORM\Query\Join::TYPE_LEFT)
			,
		];
	}

	/**
	 * Deletes all rates for a currency.
	 *
	 * @param string $currency Deleted currency id.
	 * @return void
	 */
	public static function deleteByCurrency(string $currency): void
	{
		$currency = trim($currency);
		if ($currency === '')
		{
			return;
		}
		$conn = Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from ' . $helper->quote(self::getTableName())
			. ' where ' . $helper->quote('CURRENCY') . ' = \'' . $helper->forSql($currency) . '\''
		);
		unset($helper, $conn);

		static::cleanCache();
	}
}
