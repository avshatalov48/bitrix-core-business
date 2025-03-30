<?php

namespace Bitrix\Currency;

use Bitrix\Main\DB;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Class CurrencyTable
 *
 * Fields:
 * <ul>
 * <li> CURRENCY string(3) mandatory
 * <li> AMOUNT_CNT int optional default 1
 * <li> AMOUNT float optional
 * <li> SORT int optional default 100
 * <li> DATE_UPDATE datetime mandatory
 * <li> NUMCODE string(3) optional
 * <li> BASE string(1) mandatory
 * <li> CREATED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> MODIFIED_BY int optional
 * <li> CURRENT_BASE_RATE float optional
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> LANG_FORMAT reference to {@link \Bitrix\Currency\CurrencyLangTable}
 * <li> CURRENT_LANG_FORMAT reference to {@link \Bitrix\Currency\CurrencyLangTable} with current language (LANGUAGE_ID)
 * </ul>
 *
 * @package Bitrix\Currency
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Currency_Query query()
 * @method static EO_Currency_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Currency_Result getById($id)
 * @method static EO_Currency_Result getList(array $parameters = [])
 * @method static EO_Currency_Entity getEntity()
 * @method static \Bitrix\Currency\EO_Currency createObject($setDefaultValues = true)
 * @method static \Bitrix\Currency\EO_Currency_Collection createCollection()
 * @method static \Bitrix\Currency\EO_Currency wakeUpObject($row)
 * @method static \Bitrix\Currency\EO_Currency_Collection wakeUpCollection($rows)
 */
class CurrencyTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_currency';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'CURRENCY' => (new ORM\Fields\StringField('CURRENCY'))
				->configurePrimary(true)
				->configureRequired(true)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 3))
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_CURRENCY_FIELD'))
			,
			'AMOUNT_CNT' => (new ORM\Fields\IntegerField('AMOUNT_CNT'))
				->configureRequired(true)
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_AMOUNT_CNT_FIELD'))
			,
			'AMOUNT' => (new ORM\Fields\FloatField('AMOUNT'))
				->configureRequired(true)
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_AMOUNT_FIELD'))
			,
			'SORT' => (new ORM\Fields\IntegerField('SORT'))
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_SORT_FIELD'))
			,
			'DATE_UPDATE' => (new ORM\Fields\DatetimeField('DATE_UPDATE'))
				->configureRequired(true)
				->configureDefaultValue(static fn() => new Type\DateTime())
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_DATE_UPDATE_FIELD'))
			,
			'NUMCODE' => (new ORM\Fields\StringField('NUMCODE'))
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 3))
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_NUMCODE_FIELD'))
			,
			'BASE' => (new ORM\Fields\BooleanField('BASE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_BASE_FIELD'))
			,
			'CREATED_BY' => (new ORM\Fields\IntegerField('CREATED_BY'))
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_CREATED_BY_FIELD'))
			,
			'DATE_CREATE' => (new ORM\Fields\DatetimeField('DATE_CREATE'))
				->configureDefaultValue(static fn() => new Type\DateTime())
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_DATE_CREATE_FIELD'))
			,
			'MODIFIED_BY' => (new ORM\Fields\IntegerField('MODIFIED_BY'))
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_MODIFIED_BY_FIELD'))
			,
			'CURRENT_BASE_RATE' => (new ORM\Fields\FloatField('CURRENT_BASE_RATE'))
				->configureTitle(Loc::getMessage('CURRENCY_ENTITY_CURRENT_BASE_RATE_FIELD'))
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
			'LANG_FORMAT' => (new ORM\Fields\Relations\Reference(
					'LANG_FORMAT',
					'Bitrix\Currency\CurrencyLang',
					ORM\Query\Join::on('this.CURRENCY', 'ref.CURRENCY'),
				))->configureJoinType(ORM\Query\Join::TYPE_LEFT)
			,
			'CURRENT_LANG_FORMAT' => (new ORM\Fields\Relations\Reference(
					'CURRENT_LANG_FORMAT',
					'Bitrix\Currency\CurrencyLang',
					[
						'=this.CURRENCY' => 'ref.CURRENCY',
						'=ref.LID' => new DB\SqlExpression('?', LANGUAGE_ID)
					],
				))->configureJoinType(ORM\Query\Join::TYPE_LEFT)
			,
		];
	}

	/**
	 * @deprecated deprecated since currency 16.0.0
	 * @see \Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();
	 *
	 * @return string
	 */
	public static function currencyBaseRateAgent(): string
	{
		return CurrencyManager::currencyBaseRateAgent();
	}
}
