<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\Validators\RangeValidator;

/**
 * Class WorkflowMetaTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowMetadata_Query query()
 * @method static EO_WorkflowMetadata_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowMetadata_Result getById($id)
 * @method static EO_WorkflowMetadata_Result getList(array $parameters = [])
 * @method static EO_WorkflowMetadata_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection wakeUpCollection($rows)
 */
class WorkflowMetadataTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_bp_workflow_meta';
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configureAutocomplete()
				->configurePrimary()
			,
			(new StringField('WORKFLOW_ID'))
				->configureRequired()
				->configureSize(32)
				->addValidator(new LengthValidator(1, 32))
			,
			(new IntegerField('START_DURATION'))
				->addValidator(new RangeValidator())
			,
		];
	}

	public static function deleteByWorkflowId(string $workflowId): void
	{
		$iterator = static::query()->setFilter(['=WORKFLOW_ID' => $workflowId])->exec();

		while ($metadata = $iterator->fetchObject())
		{
			$metadata->delete();
		}
	}
}