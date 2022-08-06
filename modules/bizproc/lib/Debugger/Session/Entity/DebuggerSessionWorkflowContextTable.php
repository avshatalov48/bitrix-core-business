<?php

namespace Bitrix\Bizproc\Debugger\Session\Entity;

use Bitrix\Bizproc\Debugger\Session\WorkflowContext;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class DebuggerSessionWorkflowContextTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DebuggerSessionWorkflowContext_Query query()
 * @method static EO_DebuggerSessionWorkflowContext_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DebuggerSessionWorkflowContext_Result getById($id)
 * @method static EO_DebuggerSessionWorkflowContext_Result getList(array $parameters = [])
 * @method static EO_DebuggerSessionWorkflowContext_Entity getEntity()
 * @method static \Bitrix\Bizproc\Debugger\Session\WorkflowContext createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection createCollection()
 * @method static \Bitrix\Bizproc\Debugger\Session\WorkflowContext wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection wakeUpCollection($rows)
 */
class DebuggerSessionWorkflowContextTable extends \Bitrix\Main\ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_bp_debugger_session_workflow_context';
	}

	public static function getObjectClass()
	{
		return WorkflowContext::class;
	}

	public static function getMap()
	{
		return [
			(new Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new Entity\StringField('SESSION_ID'))
				->addValidator(new LengthValidator(1, 32)),
			(new Reference(
				'SESSION',
				DebuggerSessionTable::class,
				Join::on('this.SESSION_ID', 'ref.ID'),
			))->configureJoinType(Join::TYPE_INNER),

			(new Entity\StringField('WORKFLOW_ID'))
				->addValidator(new LengthValidator(1, 32)),

			(new Entity\IntegerField('TEMPLATE_SHARDS_ID')),
			new Reference(
				'TEMPLATE_SHARDS',
				DebuggerSessionTemplateShardsTable::class,
				Join::on('this.TEMPLATE_SHARDS_ID', 'ref.ID')
			),
		];
	}
}