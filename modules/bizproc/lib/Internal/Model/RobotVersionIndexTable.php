<?php

namespace Bitrix\Bizproc\Internal\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * Class RobotVersionIndexTable
 *
 * @package Bitrix\Bizproc\Service\Entity
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RobotVersionIndex_Query query()
 * @method static EO_RobotVersionIndex_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RobotVersionIndex_Result getById($id)
 * @method static EO_RobotVersionIndex_Result getList(array $parameters = [])
 * @method static EO_RobotVersionIndex_Entity getEntity()
 * @method static \Bitrix\Bizproc\Internal\Model\EO_RobotVersionIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Internal\Model\EO_RobotVersionIndex_Collection createCollection()
 * @method static \Bitrix\Bizproc\Internal\Model\EO_RobotVersionIndex wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Internal\Model\EO_RobotVersionIndex_Collection wakeUpCollection($rows)
 */
class RobotVersionIndexTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_robot_version_index';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configureAutocomplete()
				->configurePrimary()
			,
			(new StringField('ROBOT_CODE'))
				->configureRequired()
			,
			(new IntegerField('VERSION',))
				->configureRequired()
				->configureSize(50)
			,
			(new DateField('DATE_CHANGED'))
				->configureRequired()
			,
		];
	}
}