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

class WorkgroupTemplateRightTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_template_right';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'TEMPLATE_ID' => array(
				'data_type' => 'integer',
			),
			'TEMPLATE' => array(
				'data_type' => '\Bitrix\Socialnetwork\WorkgroupTemplate',
				'reference' => array('=this.TEMPLATE_ID' => 'ref.ID')
			),
			'GROUP_CODE' => array(
				'data_type' => 'string'
			),
		);

		return $fieldsMap;
	}
}
