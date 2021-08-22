<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class OAuthTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OAuth_Query query()
 * @method static EO_OAuth_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_OAuth_Result getById($id)
 * @method static EO_OAuth_Result getList(array $parameters = array())
 * @method static EO_OAuth_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_OAuth createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_OAuth_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_OAuth wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_OAuth_Collection wakeUpCollection($rows)
 */
class OAuthTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_oauth';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'UID' => array(
				'data_type' => 'string',
			),
			'TOKEN' => array(
				'data_type' => (static::cryptoEnabled('TOKENS') ? 'crypto' : 'text'),
			),
			'REFRESH_TOKEN' => array(
				'data_type' => (static::cryptoEnabled('TOKENS') ? 'crypto' : 'text'),
			),
			'TOKEN_EXPIRES' => array(
				'data_type'    => 'integer',
			),
			'SECRET' => array(
				'data_type' => 'string',
			),
		);
	}

}
