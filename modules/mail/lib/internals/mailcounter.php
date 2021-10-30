<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

class MailCounterTable extends Entity\DataManager
{
	const DIR = 'DIR';
	const MAILBOX = 'MAILBOX';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_counter';
	}

	public static function getMap()
	{
		return array(
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
				'primary' => true,
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(self::DIR, self::MAILBOX),
				'required'  => true,
				'primary' => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'required'  => true,
				'primary' => true,
			),
			'VALUE' => array(
				'data_type' => 'integer',
			),
		);
	}
}