<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\Type\DateTime;

class MessageDisappearingTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_im_message_disappearing';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[
					'primary' => true,
					'required' => true,
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
					'default' => function()
					{
						return new DateTime();
					},
				]
			),
			'DATE_REMOVE' => new DatetimeField(
				'DATE_REMOVE',
				[
					'required' => true,
				]
			),
		];
	}
}
