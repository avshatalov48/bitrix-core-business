<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

class MessageUploadQueueTable extends Entity\DataManager
{

	const SYNC_STAGE_NEW = 0;

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_message_upload_queue';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'string',
				'primary'   => true,
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
			'SYNC_STAGE' => array(
				'data_type' => 'integer',
			),
			'SYNC_LOCK' => array(
				'data_type' => 'integer',
			),
			'ATTEMPTS' => array(
				'data_type' => 'integer',
			),
		);
	}

}
