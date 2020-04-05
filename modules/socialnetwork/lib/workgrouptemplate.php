<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;


class WorkgroupTemplateTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_template';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'OWNER_ID' => array(
				'data_type' => 'integer',
			),
			'TYPE' => array(
				'data_type' => 'string'
			),
			'PARAMS' => array(
				'data_type' => 'text'
			),
		);

		return $fieldsMap;
	}
}
