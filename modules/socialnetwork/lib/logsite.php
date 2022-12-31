<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\ORM;

/**
 * Class LogSiteTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogSite_Query query()
 * @method static EO_LogSite_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogSite_Result getById($id)
 * @method static EO_LogSite_Result getList(array $parameters = [])
 * @method static EO_LogSite_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_LogSite createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_LogSite_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_LogSite wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_LogSite_Collection wakeUpCollection($rows)
 */
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
			'LOG' => array(
				'data_type' => '\Bitrix\Socialnetwork\Log',
				'reference' => array('=this.LOG_ID' => 'ref.ID')
			),
			'SITE_ID' => [
				'data_type' => 'string',
				'primary' => true
			],
		);

		return $fieldsMap;
	}
}
