<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

class LogFavoritesTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_favorites';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);

		return $fieldsMap;
	}
}
