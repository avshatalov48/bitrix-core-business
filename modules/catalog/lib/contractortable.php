<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

/**
 * Class ContractorTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PERSON_TYPE string(1) mandatory
 * <li> PERSON_NAME string(100) optional
 * <li> PERSON_LASTNAME string(100) optional
 * <li> PERSON_MIDDLENAME string(100) optional
 * <li> EMAIL string(100) optional
 * <li> PHONE string(45) optional
 * <li> POST_INDEX string(45) optional
 * <li> COUNTRY string(45) optional
 * <li> CITY string(45) optional
 * <li> COMPANY string(145) optional
 * <li> INN string(145) optional
 * <li> KPP string(145) optional
 * <li> ADDRESS string(255) optional
 * <li> DATE_MODIFY datetime optional default current datetime
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * </ul>
 *
 * @package Bitrix\Catalog
 **/

class ContractorTable extends DataManager
{
	public const TYPE_INDIVIDUAL = '1';
	public const TYPE_COMPANY = '2';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_contractor';
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
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_ID_FIELD'),
				]
			),
			'PERSON_TYPE' => new EnumField(
				'PERSON_TYPE',
				[
					'required' => true,
					'values' => static::getTypeList(),
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_PERSON_TYPE_FIELD'),
				]
			),
			'PERSON_NAME' => new StringField(
				'PERSON_NAME',
				[
					'validation' => [__CLASS__, 'validatePersonName'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_PERSON_NAME_FIELD'),
				]
			),
			'PERSON_LASTNAME' => new StringField(
				'PERSON_LASTNAME',
				[
					'validation' => [__CLASS__, 'validatePersonLastname'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_PERSON_LASTNAME_FIELD'),
				]
			),
			'PERSON_MIDDLENAME' => new StringField(
				'PERSON_MIDDLENAME',
				[
					'validation' => [__CLASS__, 'validatePersonMiddlename'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_PERSON_MIDDLENAME_FIELD'),
				]
			),
			'EMAIL' => new StringField(
				'EMAIL',
				[
					'validation' => [__CLASS__, 'validateEmail'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_EMAIL_FIELD'),
				]
			),
			'PHONE' => new StringField(
				'PHONE',
				[
					'validation' => [__CLASS__, 'validatePhone'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_PHONE_FIELD'),
				]
			),
			'POST_INDEX' => new StringField(
				'POST_INDEX',
				[
					'validation' => [__CLASS__, 'validatePostIndex'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_POST_INDEX_FIELD'),
				]
			),
			'COUNTRY' => new StringField(
				'COUNTRY',
				[
					'validation' => [__CLASS__, 'validateCountry'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_COUNTRY_FIELD'),
				]
			),
			'CITY' => new StringField(
				'CITY',
				[
					'validation' => [__CLASS__, 'validateCity'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_CITY_FIELD'),
				]
			),
			'COMPANY' => new StringField(
				'COMPANY',
				[
					'validation' => [__CLASS__, 'validateCompany'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_COMPANY_FIELD'),
				]
			),
			'INN' => new StringField(
				'INN',
				[
					'validation' => [__CLASS__, 'validateInn'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_INN_FIELD'),
				]
			),
			'KPP' => new StringField(
				'KPP',
				[
					'validation' => [__CLASS__, 'validateKpp'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_KPP_FIELD'),
				]
			),
			'ADDRESS' => new StringField(
				'ADDRESS',
				[
					'validation' => [__CLASS__, 'validateAddress'],
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_ADDRESS_FIELD'),
				]
			),
			'DATE_MODIFY' => new DatetimeField(
				'DATE_MODIFY',
				[
					'default' => function()
					{
						return new DateTime();
					},
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_DATE_MODIFY_FIELD'),
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'default_value' => function()
					{
						return new DateTime();
					},
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_DATE_CREATE_FIELD'),
				]
			),
			'CREATED_BY' => new IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_CREATED_BY_FIELD'),
				]
			),
			'MODIFIED_BY' => new IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('CONTRACTOR_ENTITY_MODIFIED_BY_FIELD'),
				]
			),
		];
	}

	/**
	 * Returns validators for PERSON_NAME field.
	 *
	 * @return array
	 */
	public static function validatePersonName(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for PERSON_LASTNAME field.
	 *
	 * @return array
	 */
	public static function validatePersonLastname(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for PERSON_MIDDLENAME field.
	 *
	 * @return array
	 */
	public static function validatePersonMiddlename(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for EMAIL field.
	 *
	 * @return array
	 */
	public static function validateEmail(): array
	{
		return [
			new LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for PHONE field.
	 *
	 * @return array
	 */
	public static function validatePhone(): array
	{
		return [
			new LengthValidator(null, 45),
		];
	}

	/**
	 * Returns validators for POST_INDEX field.
	 *
	 * @return array
	 */
	public static function validatePostIndex(): array
	{
		return [
			new LengthValidator(null, 45),
		];
	}

	/**
	 * Returns validators for COUNTRY field.
	 *
	 * @return array
	 */
	public static function validateCountry(): array
	{
		return [
			new LengthValidator(null, 45),
		];
	}

	/**
	 * Returns validators for CITY field.
	 *
	 * @return array
	 */
	public static function validateCity(): array
	{
		return [
			new LengthValidator(null, 45),
		];
	}

	/**
	 * Returns validators for COMPANY field.
	 *
	 * @return array
	 */
	public static function validateCompany(): array
	{
		return [
			new LengthValidator(null, 145),
		];
	}

	/**
	 * Returns validators for INN field.
	 *
	 * @return array
	 */
	public static function validateInn(): array
	{
		return [
			new LengthValidator(null, 145),
		];
	}

	/**
	 * Returns validators for KPP field.
	 *
	 * @return array
	 */
	public static function validateKpp(): array
	{
		return [
			new LengthValidator(null, 145),
		];
	}

	/**
	 * Returns validators for ADDRESS field.
	 *
	 * @return array
	 */
	public static function validateAddress(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	public static function getTypeList(bool $description = false): array
	{
		if ($description)
		{
			return [
				self::TYPE_INDIVIDUAL => Loc::getMessage('CONTRACTOR_ENTITY_TYPE_INDIVIDUAL'),
				self::TYPE_COMPANY =>  Loc::getMessage('CONTRACTOR_ENTITY_TYPE_COMPANY'),
			];
		}
		else
		{
			return [
				self::TYPE_INDIVIDUAL,
				self::TYPE_COMPANY,
			];
		}
	}
}
