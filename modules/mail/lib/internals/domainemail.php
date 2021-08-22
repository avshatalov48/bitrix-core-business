<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class DomainEmailTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DomainEmail_Query query()
 * @method static EO_DomainEmail_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_DomainEmail_Result getById($id)
 * @method static EO_DomainEmail_Result getList(array $parameters = array())
 * @method static EO_DomainEmail_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_DomainEmail createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_DomainEmail_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_DomainEmail wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_DomainEmail_Collection wakeUpCollection($rows)
 */
class DomainEmailTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_domain_email';
	}

	public static function getMap()
	{
		return array(
			'DOMAIN' => array(
				'data_type' => 'string',
				'primary'   => true,
			),
			'LOGIN' => array(
				'data_type' => 'string',
				'primary'   => true,
			),
		);
	}

}
