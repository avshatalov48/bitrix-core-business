<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

class MailboxAccessTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_mailbox_access';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'TASK_ID' => array(
				'data_type' => 'integer',
			),
			'ACCESS_CODE' => array(
				'data_type' => 'string',
				'required'  => true,
			),
		);
	}

}
