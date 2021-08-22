<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class MailboxAccessTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailboxAccess_Query query()
 * @method static EO_MailboxAccess_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailboxAccess_Result getById($id)
 * @method static EO_MailboxAccess_Result getList(array $parameters = array())
 * @method static EO_MailboxAccess_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess_Collection wakeUpCollection($rows)
 */
class MailboxAccessTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_mailbox_access';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'TASK_ID' => array(
				'data_type' => 'integer',
			),
			'ACCESS_CODE' => array(
				'data_type' => 'string',
				'required'  => true,
			),
		);
	}

}
