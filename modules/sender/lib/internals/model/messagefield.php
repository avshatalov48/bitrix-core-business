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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageField_Query query()
 * @method static EO_MessageField_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageField_Result getById($id)
 * @method static EO_MessageField_Result getList(array $parameters = array())
 * @method static EO_MessageField_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageField createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageField_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageField wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_MessageField_Collection wakeUpCollection($rows)
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
				'data_type' => 'text',
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
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