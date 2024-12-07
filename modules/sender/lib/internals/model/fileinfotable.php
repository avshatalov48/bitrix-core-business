<?php

namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Access\Entity\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

class FileInfoTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_file_info';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID', //ID from b_file
				[
					'primary' => true,
					'title' => 'ID',
				]
			),
			new StringField(
				'FILE_NAME',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => 'FILE_NAME',
				]
			),
		];
	}
}