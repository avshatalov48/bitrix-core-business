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

/**
 * Class ServiceLogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ServiceLog_Query query()
 * @method static EO_ServiceLog_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ServiceLog_Result getById($id)
 * @method static EO_ServiceLog_Result getList(array $parameters = array())
 * @method static EO_ServiceLog_Entity getEntity()
 * @method static \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog_Collection createCollection()
 * @method static \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog wakeUpObject($row)
 * @method static \Bitrix\Seo\Retargeting\Internals\EO_ServiceLog_Collection wakeUpCollection($rows)
 */
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
