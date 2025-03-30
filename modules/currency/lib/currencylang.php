<?php

namespace Bitrix\Currency;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Class CurrencyLangTable
 *
 * Fields:
 * <ul>
 * <li> CURRENCY string(3) mandatory primary
 * <li> LID string(2) mandatory primary
 * <li> FORMAT_STRING string(50) mandatory
 * <li> FULL_NAME string(50) optional
 * <li> DEC_POINT string(16) optional default '.'
 * <li> THOUSANDS_SEP string(16) optional default ' '
 * <li> DECIMALS int optional default 2
 * <li> THOUSANDS_VARIANT string(1) optional
 * <li> HIDE_ZERO bool optional default 'N'
 * <li> CREATED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> MODIFIED_BY int optional
 * <li> TIMESTAMP_X datetime optional
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> LANGUAGE reference to {@link \Bitrix\Main\Localization\LanguageTable}
 * </ul>
 *
 * @package Bitrix\Currency
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CurrencyLang_Query query()
 * @method static EO_CurrencyLang_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CurrencyLang_Result getById($id)
 * @method static EO_CurrencyLang_Result getList(array $parameters = [])
 * @method static EO_CurrencyLang_Entity getEntity()
 * @method static \Bitrix\Currency\EO_CurrencyLang createObject($setDefaultValues = true)
 * @method static \Bitrix\Currency\EO_CurrencyLang_Collection createCollection()
 * @method static \Bitrix\Currency\EO_CurrencyLang wakeUpObject($row)
 * @method static \Bitrix\Currency\EO_CurrencyLang_Collection wakeUpCollection($rows)
 */

class CurrencyLangTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_currency_lang';
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
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 3))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_CURRENCY_FIELD'))
			,
			'LID' => (new ORM\Fields\StringField('LID'))
				->configurePrimary(true)
				->addValidator(new ORM\Fields\Validators\LengthValidator(2, 2))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_LID_FIELD'))
			,
			'FORMAT_STRING' => (new ORM\Fields\StringField('FORMAT_STRING'))
				->configureRequired(true)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 50))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_FORMAT_STRING_FIELD'))
			,
			'FULL_NAME' => (new ORM\Fields\StringField('FULL_NAME'))
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 50))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_FULL_NAME_FIELD'))
			,
			'DEC_POINT' => (new ORM\Fields\StringField('DEC_POINT'))
				->configureDefaultValue('.')
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 16))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_DEC_POINT_FIELD'))
			,
			'THOUSANDS_SEP' => (new ORM\Fields\StringField('THOUSANDS_SEP'))
				->configureDefaultValue(' ')
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 16))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_THOUSANDS_SEP_FIELD'))
			,
			'DECIMALS' => (new ORM\Fields\IntegerField('DECIMALS'))
				->configureDefaultValue(2)
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_DECIMALS_FIELD'))
			,
			'THOUSANDS_VARIANT' => (new ORM\Fields\StringField('THOUSANDS_VARIANT'))
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 1))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_THOUSANDS_VARIANT_FIELD'))
			,
			'HIDE_ZERO' => (new ORM\Fields\BooleanField('HIDE_ZERO'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_HIDE_ZERO_FIELD'))
			,
			'CREATED_BY' => (new ORM\Fields\IntegerField('CREATED_BY'))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_CREATED_BY_FIELD'))
			,
			'DATE_CREATE' => (new ORM\Fields\DatetimeField('DATE_CREATE'))
				->configureDefaultValue(static fn() => new Type\DateTime())
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_DATE_CREATE_FIELD'))
			,
			'MODIFIED_BY' => (new ORM\Fields\IntegerField('MODIFIED_BY'))
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_MODIFIED_BY_FIELD'))
			,
			'TIMESTAMP_X' => (new ORM\Fields\DatetimeField('TIMESTAMP_X'))
				->configureRequired(true)
				->configureDefaultValue(static fn() => new Type\DateTime())
				->configureTitle(Loc::getMessage('CURRENCY_LANG_ENTITY_TIMESTAMP_X_FIELD'))
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
			'LANGUAGE' => (new ORM\Fields\Relations\Reference(
				'LANGUAGE',
				'Bitrix\Main\Localization\Language',
				ORM\Query\Join::on('this.LID', 'ref.LID')
			))->configureJoinType(ORM\Query\Join::TYPE_LEFT)
			,
		];
	}

	/**
	 * Removes all language localizations for a currency.
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

	/**
	 * Clears tablet cache.
	 *
	 * @return void
	 */
	public static function cleanCache(): void
	{
		parent::cleanCache();
		CurrencyTable::cleanCache();
	}
}
