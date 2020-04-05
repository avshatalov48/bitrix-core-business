<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

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
