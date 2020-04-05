<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */
namespace Bitrix\Seo\Retargeting\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class ServiceLogTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_seo_service_log';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime()
			),
			'GROUP_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'MESSAGE' => array(
				'data_type' => 'string',
				'required' => true,
			)
		);

		return $fieldsMap;
	}
}
