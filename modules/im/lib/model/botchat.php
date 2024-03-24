<?php
namespace Bitrix\Im\Model;

use Bitrix\Main;

/**
 * Class BotChatTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> BOT_ID int mandatory
 * <li> CHAT_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BotChat_Query query()
 * @method static EO_BotChat_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BotChat_Result getById($id)
 * @method static EO_BotChat_Result getList(array $parameters = array())
 * @method static EO_BotChat_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_BotChat createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_BotChat_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_BotChat wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_BotChat_Collection wakeUpCollection($rows)
 */

class BotChatTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_bot_chat';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				//'title' => Loc::getMessage('BOT_CHAT_ENTITY_ID_FIELD'),
			),
			'BOT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				//'title' => Loc::getMessage('BOT_CHAT_ENTITY_BOT_ID_FIELD'),
			),
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				//'title' => Loc::getMessage('BOT_CHAT_ENTITY_CHAT_ID_FIELD'),
			),
		);
	}
}