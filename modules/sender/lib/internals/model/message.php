<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class MessageTable
 * @package Bitrix\Sender\Internals\Model
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Message_Query query()
 * @method static EO_Message_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Message_Result getById($id)
 * @method static EO_Message_Result getList(array $parameters = array())
 * @method static EO_Message_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_Message createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_Message_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_Message wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_Message_Collection wakeUpCollection($rows)
 */
class MessageTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_message';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autoincrement' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'UTM' => array(
				'data_type' => MessageUtmTable::class,
				'reference' => array('=this.ID' => 'ref.MESSAGE_ID'),
			),
		);
	}

	/**
	 * Handler of after delete event.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		MessageFieldTable::deleteByMessageId($data['primary']['ID']);

		return $result;
	}
}