<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage mail
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Mail\User;

use Bitrix\Main\Entity;

/**
 * Class MessageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Message_Query query()
 * @method static EO_Message_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Message_Result getById($id)
 * @method static EO_Message_Result getList(array $parameters = array())
 * @method static EO_Message_Entity getEntity()
 * @method static \Bitrix\Mail\User\EO_Message createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\User\EO_Message_Collection createCollection()
 * @method static \Bitrix\Mail\User\EO_Message wakeUpObject($row)
 * @method static \Bitrix\Mail\User\EO_Message_Collection wakeUpCollection($rows)
 */
class MessageTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_user_message';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'SUBJECT' => array(
				'data_type' => 'string',
			),
			'CONTENT' => array(
				'data_type' => 'string',
			),
			'ATTACHMENTS' => array(
				'data_type' => 'string',
			),
			'HEADERS' => array(
				'data_type' => 'string',
			),
		);
	}
}
