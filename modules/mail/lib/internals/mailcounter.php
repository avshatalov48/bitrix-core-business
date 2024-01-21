<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class MailCounterTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailCounter_Query query()
 * @method static EO_MailCounter_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_MailCounter_Result getById($id)
 * @method static EO_MailCounter_Result getList(array $parameters = [])
 * @method static EO_MailCounter_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MailCounter createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MailCounter_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MailCounter wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MailCounter_Collection wakeUpCollection($rows)
 */
class MailCounterTable extends Entity\DataManager
{
	const DIR = 'DIR';
	const MAILBOX = 'MAILBOX';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_counter';
	}

	public static function getMap()
	{
		return array(
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
				'primary' => true,
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(self::DIR, self::MAILBOX),
				'required'  => true,
				'primary' => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'required'  => true,
				'primary' => true,
			),
			'VALUE' => array(
				'data_type' => 'integer',
			),
		);
	}
}