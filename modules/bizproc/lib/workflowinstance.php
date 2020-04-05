<?php

namespace Bitrix\Bizproc;

use Bitrix\Main;
use Bitrix\Main\Entity;

class WorkflowInstanceTable extends Entity\DataManager
{
	const LOCKED_TIME_INTERVAL = 300;

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_workflow_instance';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'WORKFLOW' => array(
				'data_type' => 'string'
			),
			'STATUS' => array(
				'data_type' => 'integer'
			),
			'MODIFIED' => array(
				'data_type' => 'datetime'
			),
			'OWNER_ID' => array(
				'data_type' => 'string'
			),
			'OWNED_UNTIL' => array(
				'data_type' => 'datetime'
			),
			'STATE' => array(
				'data_type' => '\Bitrix\Bizproc\WorkflowStateTable',
				'reference' => array(
					'=this.ID' => 'ref.ID'
				),
				'join_type' => 'LEFT',
			),
		);
	}

	public static function getIdsByDocument(array $documentId)
	{
		$documentId = \CBPHelper::ParseDocumentId($documentId);
		$rows = static::getList([
			'select' => ['ID'],
			'filter' => [
				'=STATE.MODULE_ID' => $documentId[0],
				'=STATE.ENTITY' => $documentId[1],
				'=STATE.DOCUMENT_ID' => $documentId[2]
			]
		])->fetchAll();

		return array_column($rows, 'ID');
	}

	/**
	 * @param array $data Entity data.
	 * @throws Main\NotImplementedException
	 * @return void
	 */
	public static function add(array $data)
	{
		throw new Main\NotImplementedException("Use CBPStateService class.");
	}

	/**
	 * @param mixed $primary Primary key.
	 * @param array $data Entity data.
	 * @throws Main\NotImplementedException
	 * @return void
	 */
	public static function update($primary, array $data)
	{
		throw new Main\NotImplementedException("Use CBPStateService class.");
	}
}
