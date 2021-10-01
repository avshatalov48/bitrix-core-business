<?php
namespace Bitrix\Report\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SharingTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Sharing_Query query()
 * @method static EO_Sharing_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Sharing_Result getById($id)
 * @method static EO_Sharing_Result getList(array $parameters = array())
 * @method static EO_Sharing_Entity getEntity()
 * @method static \Bitrix\Report\Internals\EO_Sharing createObject($setDefaultValues = true)
 * @method static \Bitrix\Report\Internals\EO_Sharing_Collection createCollection()
 * @method static \Bitrix\Report\Internals\EO_Sharing wakeUpObject($row)
 * @method static \Bitrix\Report\Internals\EO_Sharing_Collection wakeUpCollection($rows)
 */
class SharingTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_sharing';
	}

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
			'REPORT_ID' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'LINK_REPORT' => array(
				'data_type' => 'Bitrix\Report\ReportTable',
				'reference' => array(
					'=this.REPORT_ID' => 'ref.ID'
				),
			),
			'ENTITY' => array(
				'data_type' => 'string',
				'required' => true
			),
			'RIGHTS' => array(
				'data_type' => 'string',
				'required' => true
			)
		);

		return $fieldsMap;
	}

}
