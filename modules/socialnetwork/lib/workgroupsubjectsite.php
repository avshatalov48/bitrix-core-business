<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

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
