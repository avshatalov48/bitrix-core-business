<?php

namespace Bitrix\Bizproc\Debugger\Session\Entity;

use Bitrix\Bizproc\Debugger\Session\Session;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class DebuggerSessionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DebuggerSession_Query query()
 * @method static EO_DebuggerSession_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DebuggerSession_Result getById($id)
 * @method static EO_DebuggerSession_Result getList(array $parameters = [])
 * @method static EO_DebuggerSession_Entity getEntity()
 * @method static \Bitrix\Bizproc\Debugger\Session\Session createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection createCollection()
 * @method static \Bitrix\Bizproc\Debugger\Session\Session wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection wakeUpCollection($rows)
 */
class DebuggerSessionTable extends \Bitrix\Main\ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_bp_debugger_session';
	}

	public static function getObjectClass()
	{
		return Session::class;
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'string',
				'primary' => true,
			],
			'MODULE_ID' => [
				'data_type' => 'string',
				'required' => true,
			],
			'ENTITY' => [
				'data_type' => 'string',
				'required' => true,
			],
			'DOCUMENT_TYPE' => [
				'data_type' => 'string',
				'required' => true,
			],
			'DOCUMENT_CATEGORY_ID' => [
				'data_type' => 'integer',
			],
			'MODE' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'TITLE' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_ENTITY_DEBUGGER_SESSION_FIELD_TITLE'),
				'required' => false,
				'validation' => fn () => [new LengthValidator(1, 255)],
			],
			'STARTED_BY' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'STARTED_DATE' => [
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => function()
					{
						return new \Bitrix\Main\Type\DateTime();
					},
			],
			'FINISHED_DATE' => [
				'data_type' => 'datetime',
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'required' => true,
				'values' => ['N', 'Y'],
			],
			'FIXED' => [
				'data_type' => 'boolean',
				'required' => true,
				'values' => ['N', 'Y'],
				'default_value' => 'N',
			],
			'DEBUGGER_STATE' => [
				'data_type' => 'integer',
				'default_value' => -1,
			],
			new OneToMany(
				'DOCUMENTS',
				DebuggerSessionDocumentTable::class,
				'SESSION'
			),
			(new OneToMany(
				'WORKFLOW_CONTEXTS',
				DebuggerSessionWorkflowContextTable::class,
				'SESSION',
			))->configureJoinType(Join::TYPE_LEFT),
		];
	}
}