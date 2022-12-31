<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

/**
 * Class WorkgroupSubjectSiteTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkgroupSubjectSite_Query query()
 * @method static EO_WorkgroupSubjectSite_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkgroupSubjectSite_Result getById($id)
 * @method static EO_WorkgroupSubjectSite_Result getList(array $parameters = [])
 * @method static EO_WorkgroupSubjectSite_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection wakeUpCollection($rows)
 */
class WorkgroupSubjectSiteTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_subject_site';
	}

	public static function getMap()
	{
		return array(
			'SUBJECT_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'SUBJECT' => array(
				'data_type' => '\Bitrix\Socialnetwork\WorkgroupSubject',
				'reference' => array('=this.SUBJECT_ID' => 'ref.ID')
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
