<?php

namespace Bitrix\Bizproc\Debugger\Session\Entity;

use Bitrix\Bizproc\Debugger\Session\TemplateShards;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class DebuggerSessionTemplateShardsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DebuggerSessionTemplateShards_Query query()
 * @method static EO_DebuggerSessionTemplateShards_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DebuggerSessionTemplateShards_Result getById($id)
 * @method static EO_DebuggerSessionTemplateShards_Result getList(array $parameters = [])
 * @method static EO_DebuggerSessionTemplateShards_Entity getEntity()
 * @method static \Bitrix\Bizproc\Debugger\Session\TemplateShards createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection createCollection()
 * @method static \Bitrix\Bizproc\Debugger\Session\TemplateShards wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection wakeUpCollection($rows)
 */
class DebuggerSessionTemplateShardsTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_bp_debugger_session_template_shards';
	}

	public static function getObjectClass(): string
	{
		return TemplateShards::class;
	}

	public static function getMap(): array
	{
		return [
			(new Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new IntegerField('TEMPLATE_ID'))
				->configureRequired(),
			new Reference(
				'TEMPLATE',
				WorkflowTemplateTable::class,
				Join::on('this.TEMPLATE_ID', 'ref.ID')
			),

			(new ArrayField('SHARDS'))
				->configureSerializeCallback([WorkflowTemplateTable::class, 'toSerializedForm'])
				->configureUnserializeCallback([WorkflowTemplateTable::class, 'getFromSerializedForm']),
			(new EnumField('TEMPLATE_TYPE'))
				->configureRequired()
				->configureValues([TemplateShards::TEMPLATE_TYPE_ACTIVITIES, TemplateShards::TEMPLATE_TYPE_ROBOTS]),

			(new DatetimeField('MODIFIED'))
				->configureRequired(),
		];
	}
}