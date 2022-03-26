<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Mail\Internal;

use Bitrix\Main\Entity;

/**
 * Class EventMessageAttachmentTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventMessageAttachment_Query query()
 * @method static EO_EventMessageAttachment_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventMessageAttachment_Result getById($id)
 * @method static EO_EventMessageAttachment_Result getList(array $parameters = [])
 * @method static EO_EventMessageAttachment_Entity getEntity()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment_Collection createCollection()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment wakeUpObject($row)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment_Collection wakeUpCollection($rows)
 */
class EventMessageAttachmentTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_event_message_attachment';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'EVENT_MESSAGE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),

			'FILE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);
	}

}
