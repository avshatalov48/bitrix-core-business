<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class MessageUploadQueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageUploadQueue_Query query()
 * @method static EO_MessageUploadQueue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageUploadQueue_Result getById($id)
 * @method static EO_MessageUploadQueue_Result getList(array $parameters = array())
 * @method static EO_MessageUploadQueue_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MessageUploadQueue createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MessageUploadQueue_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MessageUploadQueue wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MessageUploadQueue_Collection wakeUpCollection($rows)
 */
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
