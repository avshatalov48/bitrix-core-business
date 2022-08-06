<?php

namespace Bitrix\Bizproc\Debugger\Session\Entity;

/**
 * Class DebuggerSessionDocumentsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DebuggerSessionDocument_Query query()
 * @method static EO_DebuggerSessionDocument_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DebuggerSessionDocument_Result getById($id)
 * @method static EO_DebuggerSessionDocument_Result getList(array $parameters = [])
 * @method static EO_DebuggerSessionDocument_Entity getEntity()
 * @method static \Bitrix\Bizproc\Debugger\Session\Document createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection createCollection()
 * @method static \Bitrix\Bizproc\Debugger\Session\Document wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection wakeUpCollection($rows)
 */
class DebuggerSessionDocumentTable extends \Bitrix\Main\ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_bp_debugger_session_document';
	}

	public static function getObjectClass()
	{
		return \Bitrix\Bizproc\Debugger\Session\Document::class;
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'SESSION_ID' => [
				'data_type' => 'string',
				'required' => true,
			],
			new \Bitrix\Main\ORM\Fields\Relations\Reference(
				'SESSION',
				DebuggerSessionTable::class,
				\Bitrix\Main\ORM\Query\Join::on('this.SESSION_ID', 'ref.ID')
			),
			'DOCUMENT_ID' => [
				'data_type' => 'string',
				'required' => true,
			],
			'DATE_EXPIRE' => [
				'data_type' => 'datetime',
				'default_value' => (new \Bitrix\Main\Type\DateTime())->add('1 MINUTE'),
			]
		];
	}
}