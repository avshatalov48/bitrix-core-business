<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Entity;

/**
 * Class SiteDomainTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SiteDomain_Query query()
 * @method static EO_SiteDomain_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SiteDomain_Result getById($id)
 * @method static EO_SiteDomain_Result getList(array $parameters = [])
 * @method static EO_SiteDomain_Entity getEntity()
 * @method static \Bitrix\Main\EO_SiteDomain createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_SiteDomain_Collection createCollection()
 * @method static \Bitrix\Main\EO_SiteDomain wakeUpObject($row)
 * @method static \Bitrix\Main\EO_SiteDomain_Collection wakeUpCollection($rows)
 */
class SiteDomainTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_lang_domain';
	}

	public static function getMap()
	{
		return array(
			'LID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'DOMAIN' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Main\Site',
				'reference' => array('=this.LID' => 'ref.LID'),
			),
		);
	}
}
