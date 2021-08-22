<?php

namespace Bitrix\Mail;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class MailFilterTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailFilter_Query query()
 * @method static EO_MailFilter_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailFilter_Result getById($id)
 * @method static EO_MailFilter_Result getList(array $parameters = array())
 * @method static EO_MailFilter_Entity getEntity()
 * @method static \Bitrix\Mail\EO_MailFilter createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\EO_MailFilter_Collection createCollection()
 * @method static \Bitrix\Mail\EO_MailFilter wakeUpObject($row)
 * @method static \Bitrix\Mail\EO_MailFilter_Collection wakeUpCollection($rows)
 */
class MailFilterTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_filter';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'PARENT_FILTER_ID' => array(
				'data_type' => 'integer',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'PHP_CONDITION' => array(
				'data_type' => 'text',
			),
			'WHEN_MAIL_RECEIVED' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'WHEN_MANUALLY_RUN' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'SPAM_RATING' => array(
				'data_type' => 'float',
			),
			'SPAM_RATING_TYPE' => array(
				'data_type' => 'enum',
				'values'    => array('<', '>'),
			),
			'MESSAGE_SIZE' => array(
				'data_type' => 'integer',
			),
			'MESSAGE_SIZE_TYPE' => array(
				'data_type' => 'enum',
				'values'    => array('<', '>'),
			),
			'MESSAGE_SIZE_UNIT' => array(
				'data_type' => 'enum',
				'values'    => array('b', 'k', 'm'),
			),
			'ACTION_STOP_EXEC' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'ACTION_DELETE_MESSAGE' => array(
				'data_type' => 'boolean',
				'values'    => array('N', 'Y'),
			),
			'ACTION_READ' => array(
				'data_type' => 'enum',
				'values'    => array('N', 'Y', '-'),
			),
			'ACTION_PHP' => array(
				'data_type' => 'text',
			),
			'ACTION_TYPE' => array(
				'data_type' => 'string',
			),
			'ACTION_VARS' => array(
				'data_type' => 'text',
			),
			'ACTION_SPAM' => array(
				'data_type' => 'enum',
				'values'    => array('N', 'Y', '-'),
			),
		);
	}

}
