<?php
namespace Bitrix\Subscribe;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class SubscriptionTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DATE_INSERT datetime mandatory
 * <li> DATE_UPDATE datetime optional
 * <li> USER_ID int optional
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> EMAIL string(255) mandatory
 * <li> FORMAT string(4) optional default 'text'
 * <li> CONFIRM_CODE string(8) optional
 * <li> CONFIRMED bool ('N', 'Y') optional default 'N'
 * <li> DATE_CONFIRM datetime mandatory
 * <li> USER_ID reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Subscribe
 **/

class SubscriptionTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_subscription';
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
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_ID_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'DATE_INSERT',
				[
					'required' => true,
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_DATE_INSERT_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'DATE_UPDATE',
				[
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_DATE_UPDATE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'USER_ID',
				[
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_USER_ID_FIELD'),
				]
			),
			new Fields\BooleanField(
				'ACTIVE',
				[
					'values' => ['N', 'Y'],
					'default' => 'Y',
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_ACTIVE_FIELD'),
				]
			),
			new Fields\StringField(
				'EMAIL',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateEmail'],
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_EMAIL_FIELD'),
				]
			),
			new Fields\StringField(
				'FORMAT',
				[
					'default' => 'text',
					'validation' => [__CLASS__, 'validateFormat'],
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_FORMAT_FIELD'),
				]
			),
			new Fields\StringField(
				'CONFIRM_CODE',
				[
					'validation' => [__CLASS__, 'validateConfirmCode'],
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_CONFIRM_CODE_FIELD'),
				]
			),
			new Fields\BooleanField(
				'CONFIRMED',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_CONFIRMED_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'DATE_CONFIRM',
				[
					'required' => true,
					'title' => Loc::getMessage('SUBSCRIPTION_ENTITY_DATE_CONFIRM_FIELD'),
				]
			),
			new Fields\Relations\Reference(
				'USER',
				'Bitrix\Main\User',
				['=this.USER_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			(new Fields\Relations\ManyToMany('RUBRICS', \Bitrix\Subscribe\RubricTable::class))
				->configureMediatorTableName('b_subscription_rubric')
				->configureLocalPrimary('ID', 'SUBSCRIPTION_ID', 'LIST_RUBRIC_ID')
				->configureRemotePrimary('ID', 'LIST_RUBRIC_ID')
			,
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
			new Fields\Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for FORMAT field.
	 *
	 * @return array
	 */
	public static function validateFormat(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 4),
		];
	}

	/**
	 * Returns validators for CONFIRM_CODE field.
	 *
	 * @return array
	 */
	public static function validateConfirmCode(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 8),
		];
	}
}