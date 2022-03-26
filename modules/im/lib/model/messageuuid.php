<?php
namespace Bitrix\Im\Model;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;

Loc::loadMessages(__FILE__);

/**
 * Class MessageUuidTable
 *
 * @package Bitrix\Im
 */

class MessageUuidTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_im_message_uuid';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap(): array
	{
		return [
			new StringField(
				'UUID',
				[
					'primary' => true,
					'required' => true,
					'size' => 36
				]
			),
			new IntegerField('MESSAGE_ID'),
			new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
				]
			),
		];
	}
}