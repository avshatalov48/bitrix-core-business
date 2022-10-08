<?php
namespace Bitrix\Messageservice\Internal\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class IncomingMessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> REQUEST_BODY text optional
 * <li> DATE_EXEC datetime optional
 * <li> SENDER_ID string(50) mandatory
 * <li> EXTERNAL_ID string(128) optional
 * </ul>
 *
 * @package Bitrix\Messageservice
 **/

class IncomingMessageTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_messageservice_incoming_message';
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
				(new IntegerField('ID', []))
					->configurePrimary(true)
					->configureAutocomplete(true),
			'REQUEST_BODY' =>
				(new TextField('REQUEST_BODY', [])),
			'DATE_EXEC' =>
				(new DatetimeField('DATE_EXEC', [])),
			'SENDER_ID' =>
				(new StringField('SENDER_ID', [
					'validation' => [__CLASS__, 'validateSenderId']
				]))
					->configureRequired(true),
			'EXTERNAL_ID' =>
				(new StringField('EXTERNAL_ID', [
					'validation' => [__CLASS__, 'validateExternalId']
				])),
		];
	}

	/**
	 * Returns validators for SENDER_ID field.
	 *
	 * @return array
	 */
	public static function validateSenderId(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId(): array
	{
		return [
			new LengthValidator(null, 128),
		];
	}
}