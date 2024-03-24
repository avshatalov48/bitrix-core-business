<?php
namespace Bitrix\Subscribe;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class RubricTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> LID string(2) mandatory
 * <li> CODE string(100) optional
 * <li> NAME string(100) optional
 * <li> DESCRIPTION text optional
 * <li> SORT int optional default 100
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> AUTO bool ('N', 'Y') optional default 'N'
 * <li> DAYS_OF_MONTH string(100) optional
 * <li> DAYS_OF_WEEK string(15) optional
 * <li> TIMES_OF_DAY string(255) optional
 * <li> TEMPLATE string(100) optional
 * <li> LAST_EXECUTED datetime optional
 * <li> VISIBLE bool ('N', 'Y') optional default 'Y'
 * <li> FROM_FIELD string(255) optional
 * <li> LID reference to {@link \Bitrix\Main\SiteTable}
 * </ul>
 *
 * @package Bitrix\Subscribe
 **/

class RubricTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_list_rubric';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('RUBRIC_ENTITY_ID_FIELD'),
				]
			),
			new Fields\StringField(
				'LID',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateLid'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_LID_FIELD'),
				]
			),
			new Fields\StringField(
				'CODE',
				[
					'validation' => [__CLASS__, 'validateCode'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_CODE_FIELD'),
				]
			),
			new Fields\StringField(
				'NAME',
				[
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_NAME_FIELD'),
				]
			),
			new Fields\TextField(
				'DESCRIPTION',
				[
					'title' => Loc::getMessage('RUBRIC_ENTITY_DESCRIPTION_FIELD'),
				]
			),
			new Fields\IntegerField(
				'SORT',
				[
					'default' => 100,
					'title' => Loc::getMessage('RUBRIC_ENTITY_SORT_FIELD'),
				]
			),
			new Fields\BooleanField(
				'ACTIVE',
				[
					'values' =>['N', 'Y'],
					'default' => 'Y',
					'title' => Loc::getMessage('RUBRIC_ENTITY_ACTIVE_FIELD'),
				]
			),
			new Fields\BooleanField(
				'AUTO',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('RUBRIC_ENTITY_AUTO_FIELD'),
				]
			),
			new Fields\StringField(
				'DAYS_OF_MONTH',
				[
					'validation' => [__CLASS__, 'validateDaysOfMonth'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_DAYS_OF_MONTH_FIELD'),
				]
			),
			new Fields\StringField(
				'DAYS_OF_WEEK',
				[
					'validation' => [__CLASS__, 'validateDaysOfWeek'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_DAYS_OF_WEEK_FIELD'),
				]
			),
			new Fields\StringField(
				'TIMES_OF_DAY',
				[
					'validation' => [__CLASS__, 'validateTimesOfDay'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_TIMES_OF_DAY_FIELD'),
				]
			),
			new Fields\StringField(
				'TEMPLATE',
				[
					'validation' => [__CLASS__, 'validateTemplate'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_TEMPLATE_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'LAST_EXECUTED',
				[
					'title' => Loc::getMessage('RUBRIC_ENTITY_LAST_EXECUTED_FIELD'),
				]
			),
			new Fields\BooleanField(
				'VISIBLE',
				[
					'values' => ['N', 'Y'],
					'default' => 'Y',
					'title' => Loc::getMessage('RUBRIC_ENTITY_VISIBLE_FIELD'),
				]
			),
			new Fields\StringField(
				'FROM_FIELD',
				[
					'validation' => [__CLASS__, 'validateFromField'],
					'title' => Loc::getMessage('RUBRIC_ENTITY_FROM_FIELD_FIELD'),
				]
			),
			new Fields\Relations\Reference(
				'SITE',
				'\Bitrix\Main\Site',
				['=this.LID' => 'ref.LID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Returns validators for LID field.
	 *
	 * @return array
	 */
	public static function validateLid(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 2),
		];
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for DAYS_OF_MONTH field.
	 *
	 * @return array
	 */
	public static function validateDaysOfMonth(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for DAYS_OF_WEEK field.
	 *
	 * @return array
	 */
	public static function validateDaysOfWeek(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 15),
		];
	}

	/**
	 * Returns validators for TIMES_OF_DAY field.
	 *
	 * @return array
	 */
	public static function validateTimesOfDay(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for TEMPLATE field.
	 *
	 * @return array
	 */
	public static function validateTemplate(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for FROM_FIELD field.
	 *
	 * @return array
	 */
	public static function validateFromField(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 255),
		];
	}
}