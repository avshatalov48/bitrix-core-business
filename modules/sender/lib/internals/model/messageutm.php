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
 * @method static EO_MessageUtm_Query query()
 * @method static EO_MessageUtm_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageUtm_Result getById($id)
 * @method static EO_MessageUtm_Result getList(array $parameters = array())
 * @method static EO_MessageUtm_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageUtm createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageUtm wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection wakeUpCollection($rows)
 */
class MessageUtmTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_message_utm';
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
			'VALUE' => array(
				'data_type' => 'text',
				'required' => true,
			),
			'MESSAGE' => array(
				'data_type' => MessageTable::class,
				'reference' => array('=this.MESSAGE_ID' => 'ref.ID'),
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
		$items = static::getList(
			[
				'select' => ['MESSAGE_ID', 'CODE'],
				'filter' => ['=MESSAGE_ID' => $messageId]
			]
		);
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