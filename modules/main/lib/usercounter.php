<?php
namespace Bitrix\Main;

use Bitrix\Main\Entity;

class UserCounterTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_user_counter';
	}

	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSiteId'),
				'primary' => true
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCode'),
				'primary' => true
			),
			'TAG' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTag'),
			),
			'PARAMS' => array(
				'data_type' => 'text'
			),
			'SENT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSent'),
			),
			'CNT' => array(
				'data_type' => 'integer'
			),
			'LAST_DATE' => array(
				'data_type' => 'datetime'
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime'
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
		);
	}

	public static function validateProviderId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	public static function validateSiteId()
	{
		return array(
			new Entity\Validator\Length(null, 2),
		);
	}

	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	public static function validateSent()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}

	public static function validateTag()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	public static function validateAccessCode()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}
}