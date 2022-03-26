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
 * Class EventMessageSiteTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventMessageSite_Query query()
 * @method static EO_EventMessageSite_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventMessageSite_Result getById($id)
 * @method static EO_EventMessageSite_Result getList(array $parameters = [])
 * @method static EO_EventMessageSite_Entity getEntity()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageSite createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection createCollection()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageSite wakeUpObject($row)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection wakeUpCollection($rows)
 */
class EventMessageSiteTable extends Entity\DataManager
{

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_event_message_site';
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
				'autocomplete' => true,
			),

			'SITE_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
		);
	}

}
