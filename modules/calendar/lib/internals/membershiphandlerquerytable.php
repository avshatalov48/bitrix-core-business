<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class MembershipHandlerQueryTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENTITY_TYPE string(255) mandatory
 * <li> ENTITY_ID int optional
 * <li> CREATION_TIMESTAMP_UTC int optional
 * <li> LAST_UPDATED_EVENT_ID int optional
 * </ul>
 *
 * @package Bitrix\Calendar
 **/

class MembershipHandlerQueryTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_membership_handler_query';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('MEMBERSHIP_HANDLER_QUERY_ENTITY_ID_FIELD'),
				]
			),
			'ENTITY_TYPE' => new Fields\StringField(
				'ENTITY_TYPE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateEntityType'],
					'title' => Loc::getMessage('MEMBERSHIP_HANDLER_QUERY_ENTITY_ENTITY_TYPE_FIELD'),
				]
			),
			'ENTITY_ID' => new Fields\IntegerField(
				'ENTITY_ID',
				[
					'title' => Loc::getMessage('MEMBERSHIP_HANDLER_QUERY_ENTITY_ENTITY_ID_FIELD'),
				]
			),
			'CREATION_TIMESTAMP_UTC' => new Fields\IntegerField(
				'CREATION_TIMESTAMP_UTC',
				[
					'title' => Loc::getMessage('MEMBERSHIP_HANDLER_QUERY_ENTITY_CREATION_TIMESTAMP_UTC_FIELD'),
				]
			),
			'LAST_UPDATED_EVENT_ID' => new Fields\IntegerField(
				'LAST_UPDATED_EVENT_ID',
				[
					'title' => Loc::getMessage('MEMBERSHIP_HANDLER_QUERY_ENTITY_LAST_UPDATED_EVENT_ID_FIELD'),
				]
			),
		];
	}

	/**
	 * Returns validators for ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEntityType(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 255),
		];
	}
}