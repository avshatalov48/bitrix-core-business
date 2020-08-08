<?php
namespace Bitrix\Sale\Delivery\Rest\Internals;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class DeliveryRestHandlerTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> CODE string(50) mandatory
 * <li> SORT int optional default 100
 * <li> DESCRIPTION text optional
 * <li> SETTINGS text mandatory
 * <li> PROFILES text mandatory
 * </ul>
 *
 * @package Bitrix\Sale
 **/

class DeliveryRestHandlerTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_delivery_rest_handler';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('DELIVERY_REST_HANDLER_ENTITY_ID_FIELD')
				]
			),
			new StringField(
				'NAME',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('DELIVERY_REST_HANDLER_ENTITY_NAME_FIELD')
				]
			),
			new StringField(
				'CODE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateCode'],
					'title' => Loc::getMessage('DELIVERY_REST_HANDLER_ENTITY_CODE_FIELD')
				]
			),
			new IntegerField(
				'SORT',
				[
					'default' => 100,
					'title' => Loc::getMessage('DELIVERY_REST_HANDLER_ENTITY_SORT_FIELD')
				]
			),
			new TextField(
				'DESCRIPTION',
				[
					'title' => Loc::getMessage('DELIVERY_REST_HANDLER_ENTITY_DESCRIPTION_FIELD')
				]
			),
			new TextField(
				'SETTINGS',
				[
					'required' => true,
					'title' => Loc::getMessage('DELIVERY_REST_HANDLER_ENTITY_SETTINGS_FIELD'),
					'serialized' => true
				]
			),
			new TextField(
				'PROFILES',
				[
					'required' => true,
					'title' => Loc::getMessage('DELIVERY_REST_HANDLER_ENTITY_PROFILES_FIELD'),
					'serialized' => true
				]
			),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function validateName()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function validateCode()
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}