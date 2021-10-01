<?php
namespace Bitrix\Currency;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

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
 * @method static EO_CurrencyRate_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_CurrencyRate_Result getById($id)
 * @method static EO_CurrencyRate_Result getList(array $parameters = array())
 * @method static EO_CurrencyRate_Entity getEntity()
 * @method static \Bitrix\Currency\EO_CurrencyRate createObject($setDefaultValues = true)
 * @method static \Bitrix\Currency\EO_CurrencyRate_Collection createCollection()
 * @method static \Bitrix\Currency\EO_CurrencyRate wakeUpObject($row)
 * @method static \Bitrix\Currency\EO_CurrencyRate_Collection wakeUpCollection($rows)
 */

class CurrencyRateTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_currency_rate';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_ID_FIELD')
			)),
			'CURRENCY' => new Main\Entity\StringField('CURRENCY', array(
				'primary' => true,
				'validation' => array(__CLASS__, 'validateCurrency'),
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_CURRENCY_FIELD')
			)),
			'BASE_CURRENCY' => new Main\Entity\StringField('BASE_CURRENCY', array(
				'primary' => true,
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_BASE_CURRENCY_FIELD')
			)),
			'DATE_RATE' => new Main\Entity\DateField('DATE_RATE', array(
				'primary' => true,
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_DATE_RATE_FIELD')
			)),
			'RATE_CNT' => new Main\Entity\IntegerField('RATE_CNT', array(
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_RATE_CNT_FIELD')
			)),
			'RATE' => new Main\Entity\FloatField('RATE', array(
				'required' => true,
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_RATE_FIELD')
			)),
			'CREATED_BY' => new Main\Entity\IntegerField('CREATED_BY', array(
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_CREATED_BY_FIELD')
			)),
			'DATE_CREATE' => new Main\Entity\DatetimeField('DATE_CREATE', array(
				'default_value' => function(){ return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_DATE_CREATE_FIELD')
			)),
			'MODIFIED_BY' => new Main\Entity\IntegerField('MODIFIED_BY', array(
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_MODIFIED_BY_FIELD')
			)),
			'TIMESTAMP_X' => new Main\Entity\DatetimeField('TIMESTAMP_X', array(
				'required' => true,
				'default_value' => function(){ return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('CURRENCY_RATE_ENTITY_TIMESTAMP_X_FIELD')
			)),
			'CREATED_BY_USER' => new Main\Entity\ReferenceField(
				'CREATED_BY_USER',
				'Bitrix\Main\User',
				array('=this.CREATED_BY' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'MODIFIED_BY_USER' => new Main\Entity\ReferenceField(
				'MODIFIED_BY_USER',
				'Bitrix\Main\User',
				array('=this.MODIFIED_BY' => 'ref.ID'),
				array('join_type' => 'LEFT')
			)
		);
	}

	/**
	 * Returns validators for CURRENCY field.
	 *
	 * @return array
	 */
	public static function validateCurrency()
	{
		return array(
			new Main\Entity\Validator\Length(null, 3),
		);
	}
}