<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

class MessageClosureTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_message_closure';
	}

	public static function getMap()
	{
		return array(
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
		);
	}

}
