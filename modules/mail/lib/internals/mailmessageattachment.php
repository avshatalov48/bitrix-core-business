<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

class MailMessageAttachmentTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_msg_attachment';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
			),
			'FILE_NAME' => array(
				'data_type' => 'string',
			),
			'FILE_SIZE' => array(
				'data_type' => 'integer',
			),
			'FILE_DATA' => array(
				'data_type' => 'string',
			),
			'CONTENT_TYPE' => array(
				'data_type' => 'string',
			),
			'IMAGE_WIDTH' => array(
				'data_type' => 'integer',
			),
			'IMAGE_HEIGHT' => array(
				'data_type' => 'integer',
			),
		);
	}

}
