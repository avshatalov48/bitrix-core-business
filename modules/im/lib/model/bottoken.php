<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\Entity;

/**
 * Class BotTokenTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TOKEN string(32) optional
 * <li> DATE_CREATE datetime mandatory
 * <li> DATE_EXPIRE datetime optional
 * <li> BOT_ID int optional
 * <li> DIALOG_ID string(255) mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BotToken_Query query()
 * @method static EO_BotToken_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BotToken_Result getById($id)
 * @method static EO_BotToken_Result getList(array $parameters = array())
 * @method static EO_BotToken_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_BotToken createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_BotToken_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_BotToken wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_BotToken_Collection wakeUpCollection($rows)
 */

class BotTokenTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_bot_token';
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
				//'title' => Loc::getMessage('BOT_TOKEN_ENTITY_ID_FIELD'),
			),
			'TOKEN' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToken'),
				//'title' => Loc::getMessage('BOT_TOKEN_ENTITY_TOKEN_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				//'title' => Loc::getMessage('BOT_TOKEN_ENTITY_DATE_CREATE_FIELD'),
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'DATE_EXPIRE' => array(
				'data_type' => 'datetime',
				//'title' => Loc::getMessage('BOT_TOKEN_ENTITY_DATE_EXPIRE_FIELD'),
			),
			'BOT_ID' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('BOT_TOKEN_ENTITY_BOT_ID_FIELD'),
			),
			'DIALOG_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateDialogId'),
				//'title' => Loc::getMessage('BOT_TOKEN_ENTITY_DIALOG_ID_FIELD'),
			),
		);
	}

	/**
	 * Returns validators for TOKEN field.
	 *
	 * @return array
	 */
	public static function validateToken()
	{
		return array(
			new Entity\Validator\Length(null, 32),
		);
	}

	/**
	 * Returns validators for DIALOG_ID field.
	 *
	 * @return array
	 */
	public static function validateDialogId()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Return current date for DATE_CREATE field.
	 *
	 * @return array
	 */
	public static function getCurrentDate()
	{
		return new \Bitrix\Main\Type\DateTime();
	}
}