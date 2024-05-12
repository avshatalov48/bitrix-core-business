<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;

/**
 * Class WorkflowFilterTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowFilter_Query query()
 * @method static EO_WorkflowFilter_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowFilter_Result getById($id)
 * @method static EO_WorkflowFilter_Result getList(array $parameters = [])
 * @method static EO_WorkflowFilter_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection wakeUpCollection($rows)
 */
class WorkflowFilterTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_bp_workflow_filter';
	}

	public static function getMap()
	{
		return [
			(new StringField('WORKFLOW_ID'))
				->configureSize(32)
				->configurePrimary()
			,
			(new StringField('MODULE_ID'))
				->configureSize(32)
			,
			(new StringField('ENTITY'))
				->configureSize(64)
			,
			(new StringField('DOCUMENT_ID'))
				->configureSize(128)
			,
			(new IntegerField('TEMPLATE_ID'))
				->configureNullable(false)
			,
			(new DatetimeField('STARTED'))
				->configureNullable(false)
			,
		];
	}
}
