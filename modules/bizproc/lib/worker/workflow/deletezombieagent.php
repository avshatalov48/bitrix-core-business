<?php

namespace Bitrix\Bizproc\Worker\Workflow;

use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;

class DeleteZombieAgent
{
	protected const CLEAR_LOG_SELECT_LIMIT = 2000;

	public static function getName()
	{
		return static::class . '::execute();';
	}

	public static function execute(): string
	{
		static::clear();

		return static::getName();
	}

	private static function clear(): void
	{
		$ids = WorkflowStateTable::getIdsByDocument(
			WorkflowStateTable::ZOMBIE_DOCUMENT_ID,
			static::CLEAR_LOG_SELECT_LIMIT,
		);

		foreach ($ids as $id)
		{
			\CBPDocument::killWorkflow($id, false);
		}
	}
}
