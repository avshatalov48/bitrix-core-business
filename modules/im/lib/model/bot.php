<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Entity\User\Data\BotData;
use Bitrix\Main;
use Bitrix\Main\ORM\Event;
use Bitrix\Im\V2\Common\UpdateByFilterTrait;


/**
 * Class BotTable
 *
 * Fields:
 * <ul>
 * <li> BOT_ID int mandatory
 * <li> MODULE_ID int mandatory
 * <li> TO_CLASS string(255) optional
 * <li> TO_METHOD string(255) optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Bot_Query query()
 * @method static EO_Bot_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Bot_Result getById($id)
 * @method static EO_Bot_Result getList(array $parameters = [])
 * @method static EO_Bot_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Bot createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Bot_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Bot wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Bot_Collection wakeUpCollection($rows)
 */

class BotTable extends Main\Entity\DataManager
{
	use UpdateByFilterTrait;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_bot';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'BOT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'MODULE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateModuleId'),
				'required' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBotCode'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateBotType'),
				'default_value' => 'B',
			),
			'CLASS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToClass'),
			),
			'LANG' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateLanguage'),
				'default_value' => '',
			),
			'METHOD_BOT_DELETE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
			),
			'METHOD_MESSAGE_ADD' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
			),
			'METHOD_MESSAGE_UPDATE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
			),
			'METHOD_MESSAGE_DELETE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
			),
			'METHOD_CONTEXT_GET' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
			),
			'METHOD_REACTION_CHANGE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
			),
			'METHOD_WELCOME_MESSAGE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
			),
			'TEXT_PRIVATE_WELCOME_MESSAGE' => array(
				'data_type' => 'text',
			),
			'TEXT_CHAT_WELCOME_MESSAGE' => array(
				'data_type' => 'text',
			),
			'COUNT_MESSAGE' => array(
				'data_type' => 'integer',
			),
			'COUNT_COMMAND' => array(
				'data_type' => 'integer',
			),
			'COUNT_CHAT' => array(
				'data_type' => 'integer',
			),
			'COUNT_USER' => array(
				'data_type' => 'integer',
			),
			'APP_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateAppId'),
				'default_value' => '',
			),
			'VERIFIED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'OPENLINE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'HIDDEN' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'REACTIONS_ENABLED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'BACKGROUND_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateBotCode'),
			),
		);
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateBotCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for CLASS field.
	 *
	 * @return array
	 */
	public static function validateToClass()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for MODULE_ID field.
	 *
	 * @return array
	 */
	public static function validateModuleId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for METHODS field.
	 *
	 * @return array
	 */
	public static function validateToMethod()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for APP_ID field.
	 *
	 * @return array
	 */
	public static function validateAppId()
	{
		return array(
			new  Main\Entity\Validator\Length(null, 128),
		);
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateBotType()
	{
		return array(
			new  Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateLanguage()
	{
		return array(
			new  Main\Entity\Validator\Length(null, 50),
		);
	}

	public static function onAfterUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$id = (int)$event->getParameter('primary')['BOT_ID'];
		$fields = $event->getParameter('fields');

		if (static::needCacheInvalidate($fields))
		{
			BotData::cleanCache($id);
		}

		return new Main\Entity\EventResult();
	}

	public static function onAfterDelete(Event $event)
	{
		$id = (int)$event->getParameter('primary')['BOT_ID'];
		BotData::cleanCache($id);

		return new Main\Entity\EventResult();
	}

	protected static function needCacheInvalidate(array $updatedFields): bool
	{
		$cacheInvalidatingFields = [
			'BOT_ID',
			'MODULE_ID',
			'CODE',
			'TYPE',
			'CLASS',
			'LANG',
			'METHOD_BOT_DELETE',
			'METHOD_MESSAGE_ADD',
			'METHOD_MESSAGE_UPDATE',
			'METHOD_MESSAGE_DELETE',
			'METHOD_CONTEXT_GET',
			'METHOD_REACTION_CHANGE',
			'METHOD_WELCOME_MESSAGE',
			'TEXT_PRIVATE_WELCOME_MESSAGE',
			'TEXT_CHAT_WELCOME_MESSAGE',
			'APP_ID',
			'VERIFIED',
			'OPENLINE',
			'HIDDEN',
			'REACTIONS_ENABLED',
			'BACKGROUND_ID',
		];

		return !empty(array_intersect($cacheInvalidatingFields, array_keys($updatedFields)));
	}
}
