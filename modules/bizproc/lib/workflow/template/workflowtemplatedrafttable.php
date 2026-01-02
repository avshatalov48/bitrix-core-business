<?php

namespace Bitrix\Bizproc\Workflow\Template;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * Class WorkflowTemplateDraftTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowTemplateDraft_Query query()
 * @method static EO_WorkflowTemplateDraft_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowTemplateDraft_Result getById($id)
 * @method static EO_WorkflowTemplateDraft_Result getList(array $parameters = [])
 * @method static EO_WorkflowTemplateDraft_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Template\EO_WorkflowTemplateDraft createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Template\EO_WorkflowTemplateDraft_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Template\EO_WorkflowTemplateDraft wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Template\EO_WorkflowTemplateDraft_Collection wakeUpCollection($rows)
 */
class WorkflowTemplateDraftTable extends DataManager
{
	public const STATUS_DRAFT = 0;
	public const STATUS_AUTOSAVE = 1;

	public static function getTableName(): string
	{
		return 'b_bp_workflow_template_draft';
	}

	public static function getMap(): array
	{
		return [
			(new ORM\Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new ORM\Fields\StringField('MODULE_ID'))
				->configureRequired()
			,
			(new ORM\Fields\StringField('ENTITY'))
				->configureRequired()
			,
			(new ORM\Fields\StringField('DOCUMENT_TYPE'))
				->configureRequired()
			,
			(new ORM\Fields\IntegerField('TEMPLATE_ID')),
			(new ORM\Fields\ArrayField('TEMPLATE_DATA'))
				->configureRequired()
				->configureSerializeCallback([Entity\WorkflowTemplateTable::class, 'toSerializedForm'])
				->configureUnserializeCallback([Entity\WorkflowTemplateTable::class, 'getFromSerializedForm'])
			,
			(new ORM\Fields\IntegerField('STATUS'))
				->configureRequired()
				->configureDefaultValue(0)
			,
			(new ORM\Fields\IntegerField('USER_ID'))
				->configureNullable(false)
			,
			(new ORM\Fields\DatetimeField('CREATED'))
				->configureNullable(false)
			,
			new ORM\Fields\Relations\Reference(
				'TEMPLATE',
				Entity\WorkflowTemplateTable::class,
				ORM\Query\Join::on('this.TEMPLATE_ID', 'ref.ID')
			),
		];
	}

	public static function getDraftsByTemplateId(int $templateId): array
	{
		return static::getList([
			'filter' => ['=TEMPLATE_ID' => $templateId],
		])->fetchAll();
	}
}
