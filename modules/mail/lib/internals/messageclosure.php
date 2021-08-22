<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class MessageClosureTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageClosure_Query query()
 * @method static EO_MessageClosure_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageClosure_Result getById($id)
 * @method static EO_MessageClosure_Result getList(array $parameters = array())
 * @method static EO_MessageClosure_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure_Collection wakeUpCollection($rows)
 */
class MessageClosureTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_message_closure';
	}

	public static function getMap()
	{
		return array(
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
		);
	}

}
