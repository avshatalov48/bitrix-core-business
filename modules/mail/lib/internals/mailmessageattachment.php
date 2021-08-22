<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class MailMessageAttachmentTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailMessageAttachment_Query query()
 * @method static EO_MailMessageAttachment_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailMessageAttachment_Result getById($id)
 * @method static EO_MailMessageAttachment_Result getList(array $parameters = array())
 * @method static EO_MailMessageAttachment_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MailMessageAttachment createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MailMessageAttachment_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MailMessageAttachment wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MailMessageAttachment_Collection wakeUpCollection($rows)
 */
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
