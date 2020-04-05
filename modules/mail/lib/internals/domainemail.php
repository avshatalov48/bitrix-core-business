<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

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
