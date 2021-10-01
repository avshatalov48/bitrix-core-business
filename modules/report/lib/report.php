<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage report
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Report;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class ReportTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Report_Query query()
 * @method static EO_Report_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Report_Result getById($id)
 * @method static EO_Report_Result getList(array $parameters = array())
 * @method static EO_Report_Entity getEntity()
 * @method static \Bitrix\Report\EO_Report createObject($setDefaultValues = true)
 * @method static \Bitrix\Report\EO_Report_Collection createCollection()
 * @method static \Bitrix\Report\EO_Report wakeUpObject($row)
 * @method static \Bitrix\Report\EO_Report_Collection wakeUpCollection($rows)
 */
class ReportTable extends Entity\DataManager
{
	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'OWNER_ID' => array(
				'data_type' => 'string'
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'DESCRIPTION' => array(
				'data_type' => 'string'
			),
			'CREATED_DATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer'
			),
			'CREATED_BY_USER' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.CREATED_BY' => 'ref.ID')
			),
			'SETTINGS' => array(
				'data_type' => 'string'
			),
			'MARK_DEFAULT' => array(
				'data_type' => 'integer'
			)
		);

		return $fieldsMap;
	}

}
