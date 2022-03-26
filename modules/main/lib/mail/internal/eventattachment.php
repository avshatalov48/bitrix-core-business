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
 * Class EventAttachmentTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventAttachment_Query query()
 * @method static EO_EventAttachment_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventAttachment_Result getById($id)
 * @method static EO_EventAttachment_Result getList(array $parameters = [])
 * @method static EO_EventAttachment_Entity getEntity()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventAttachment createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection createCollection()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventAttachment wakeUpObject($row)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection wakeUpCollection($rows)
 */
class EventAttachmentTable extends Entity\DataManager
{

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_event_attachment';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'EVENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
			),
			'IS_FILE_COPIED' => array(
				'data_type' => 'boolean',
				'required' => true,
				'values' => array('N', 'Y'),
				'default_value' => 'Y'
			),
			'EVENT' => array(
				'data_type' => EventTable::class,
				'reference' => array('=this.EVENT_ID' => 'ref.ID'),
			),
		);
	}

}
