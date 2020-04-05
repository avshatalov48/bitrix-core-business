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