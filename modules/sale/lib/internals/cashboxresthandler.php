<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class CashboxRestHandlerTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> CODE string(50) mandatory
 * <li> SORT int optional default 100
 * <li> SETTINGS text mandatory
 * </ul>
 *
 * @package Bitrix\Sale
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CashboxRestHandler_Query query()
 * @method static EO_CashboxRestHandler_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_CashboxRestHandler_Result getById($id)
 * @method static EO_CashboxRestHandler_Result getList(array $parameters = array())
 * @method static EO_CashboxRestHandler_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_CashboxRestHandler createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_CashboxRestHandler_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_CashboxRestHandler wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_CashboxRestHandler_Collection wakeUpCollection($rows)
 */

class CashboxRestHandlerTable extends DataManager
{
	/**
	 * @inheritDoc
	 */
	public static function getTableName()
	{
		return 'b_sale_cashbox_rest_handler';
	}

	/**
	 * @inheritDoc
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('CASHBOX_REST_HANDLER_ENTITY_ID_FIELD')
				]
			),
			new StringField(
				'NAME',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('CASHBOX_REST_HANDLER_ENTITY_NAME_FIELD')
				]
			),
			new StringField(
				'CODE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateCode'],
					'title' => Loc::getMessage('CASHBOX_REST_HANDLER_ENTITY_CODE_FIELD')
				]
			),
			new IntegerField(
				'SORT',
				[
					'default' => 100,
					'title' => Loc::getMessage('CASHBOX_REST_HANDLER_ENTITY_SORT_FIELD')
				]
			),
			new TextField(
				'SETTINGS',
				[
					'required' => true,
					'title' => Loc::getMessage('CASHBOX_REST_HANDLER_ENTITY_SETTINGS_FIELD'),
					'serialized' => true,
				]
			),
			new StringField('APP_ID'),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
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
	 */
	public static function validateCode()
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}