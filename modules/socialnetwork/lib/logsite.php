<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\ORM;

class LogSiteTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_site';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'LOG_ID' => [
				'data_type' => 'integer',
				'primary' => true
			],
			'SITE_ID' => [
				'data_type' => 'string',
				'primary' => true
			],
		);

		return $fieldsMap;
	}
}
