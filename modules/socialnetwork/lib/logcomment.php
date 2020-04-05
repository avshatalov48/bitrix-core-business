<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

class LogCommentTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_comment';
	}

	public static function getUfId()
	{
		return 'SONET_COMMENT';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
			),
			'LOG' => array(
				'data_type' => 'Bitrix\Socialnetwork\LogTable',
				'reference' => array('=this.LOG_ID' => 'ref.ID'),
			),
			'EVENT_ID' => array(
				'data_type' => 'string',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			),
			'SOURCE_ID' => array(
				'data_type' => 'integer',
			),
			'LOG_DATE' => array(
				'data_type' => 'datetime',
			),
		);

		return $fieldsMap;
	}
}
