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

Loc::loadMessages(__FILE__);

/**
 * Class WorkgroupSiteTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkgroupSite_Query query()
 * @method static EO_WorkgroupSite_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkgroupSite_Result getById($id)
 * @method static EO_WorkgroupSite_Result getList(array $parameters = [])
 * @method static EO_WorkgroupSite_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSite createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSite wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection wakeUpCollection($rows)
 */
class WorkgroupSiteTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_site';
	}

	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'GROUP' => array(
				'data_type' => '\Bitrix\Socialnetwork\Workgroup',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'SITE' => array(
				'data_type' => '\Bitrix\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID')
			),
		);
	}
}
