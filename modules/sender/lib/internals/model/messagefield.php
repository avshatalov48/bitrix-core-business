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
 * Class MessageFieldTable
 * @package Bitrix\Sender\Internals\Model
 */
class MessageFieldTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_message_field';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'primary' => true,
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'VALUE' => array(
				'data_type' => 'text'
			),
		);
	}

	/**
	 * Delete fields by message ID.
	 *
	 * @param int $messageId Message ID.
	 * @return bool
	 */
	public static function deleteByMessageId($messageId)
	{
		$items = static::getList([
			'select' => ['MESSAGE_ID', 'CODE'],
			'filter' => ['=MESSAGE_ID' => $messageId]
		]);
		foreach ($items as $primary)
		{
			$result = static::delete($primary);
			if (!$result->isSuccess())
			{
				return false;
			}
		}

		return true;
	}
}