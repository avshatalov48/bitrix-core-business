<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;

class UserWelltoryTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sonet_user_welltory';
	}

	/**
	 * Returns entity map definition
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'STRESS' => array(
				'data_type' => 'integer',
			),
			'STRESS_TYPE' => array(
				'data_type' => 'string',
			),
			'STRESS_COMMENT' => array(
				'data_type' => 'string',
			),
			'DATE_MEASURE' => array(
				'data_type' => 'datetime'
			),
			'HASH' => array(
				'data_type' => 'string',
			),
		);
	}
}
