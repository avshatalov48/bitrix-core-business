<?php

namespace Bitrix\Main;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;

Loc::loadMessages(__FILE__);

/**
 * Class GroupTaskTable
 * 
 * Fields:
 * <ul>
 * <li> GROUP_ID int mandatory
 * <li> TASK_ID int mandatory
 * <li> EXTERNAL_ID string(50) optional
 * <li> GROUP reference to {@link \Bitrix\Main\GroupTable}
 * <li> TASK reference to {@link \Bitrix\Main\TaskTable}
 * </ul>
 *
 * @package Bitrix\Main
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupTask_Query query()
 * @method static EO_GroupTask_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_GroupTask_Result getById($id)
 * @method static EO_GroupTask_Result getList(array $parameters = [])
 * @method static EO_GroupTask_Entity getEntity()
 * @method static \Bitrix\Main\EO_GroupTask createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_GroupTask_Collection createCollection()
 * @method static \Bitrix\Main\EO_GroupTask wakeUpObject($row)
 * @method static \Bitrix\Main\EO_GroupTask_Collection wakeUpCollection($rows)
 */

class GroupTaskTable extends Main\Entity\DataManager
{
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_group_task';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'TASK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExternalId'),
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Main\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'TASK' => array(
				'data_type' => 'Bitrix\Main\TaskTable',
				'reference' => array('=this.TASK_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
}
