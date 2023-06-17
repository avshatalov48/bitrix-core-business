<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

/**
 * Class UserTransactTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> TIMESTAMP_X datetime optional default current datetime
 * <li> TRANSACT_DATE datetime mandatory
 * <li> AMOUNT double optional default 0.0000
 * <li> CURRENCY string(3) mandatory
 * <li> DEBIT bool ('N', 'Y') optional default 'N'
 * <li> ORDER_ID int optional
 * <li> DESCRIPTION string(255) mandatory
 * <li> NOTES text optional
 * <li> PAYMENT_ID int optional
 * <li> EMPLOYEE_ID int optional
 * </ul>
 *
 * @package Bitrix\Sale
 **/
class UserTransactTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_user_transact';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' =>
				(new IntegerField('ID'))
					->configurePrimary(true)
					->configureAutocomplete(true)
			,
			'USER_ID' =>
				(new IntegerField('USER_ID'))
					->configureRequired(true)
			,
			'TIMESTAMP_X' =>
				(new DatetimeField('TIMESTAMP_X'))
					->configureDefaultValue(
						static function()
						{
							return new DateTime();
						}
					)
			,
			'TRANSACT_DATE' =>
				(new DatetimeField('TRANSACT_DATE'))
					->configureRequired(true)
			,
			'AMOUNT' =>
				(new FloatField('AMOUNT'))
					->configureDefaultValue(0.0000)
			,
			'CURRENCY' =>
				(new StringField('CURRENCY'))
					->configureRequired(true)
					->addValidator([__CLASS__, 'validateCurrency'])
			,
			'DEBIT' => (new BooleanField('DEBIT'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
			,
			'ORDER_ID' => (new IntegerField('ORDER_ID')),
			'DESCRIPTION' =>
				(new StringField('DESCRIPTION'))
					->configureRequired(true)
					->addValidator([__CLASS__, 'validateDescription'])
			,
			'NOTES' => (new TextField('NOTES')),
			'PAYMENT_ID' => (new IntegerField('PAYMENT_ID')),
			'EMPLOYEE_ID' => (new IntegerField('EMPLOYEE_ID')),
			//
			new Reference(
				'ORDER',
				OrderTable::class,
				Join::on('this.ORDER_ID', 'ref.ID')
			),
			new Reference(
				'PAYMENT',
				PaymentTable::class,
				Join::on('this.PAYMENT_ID', 'ref.ID')
			),
		];
	}

	/**
	 * Returns validators for CURRENCY field.
	 *
	 * @return array
	 */
	public static function validateCurrency(): array
	{
		return [
			new LengthValidator(null, 3),
		];
	}

	/**
	 * Returns validators for DESCRIPTION field.
	 *
	 * @return array
	 */
	public static function validateDescription(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}
}
