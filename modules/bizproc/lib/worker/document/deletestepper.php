<?php

namespace Bitrix\Bizproc\Worker\Document;

use Bitrix\Main;

class DeleteStepper extends Main\Update\Stepper
{
	protected static $moduleId = 'bizproc';
	private static $delay = 0;

	private const STEP_ROWS_LIMIT = 10;

	public function execute(array &$option)
	{
		$documentId = $this->getOuterParams();
		$ids = \CBPStateService::getIdsByDocument($documentId, self::STEP_ROWS_LIMIT);

		if (empty($ids))
		{
			\CBPHistoryService::DeleteByDocument($documentId);
			return self::FINISH_EXECUTION;
		}

		foreach ($ids as $id)
		{
			\CBPDocument::killWorkflow($id, false);
		}

		return self::CONTINUE_EXECUTION;
	}

	public static function bindDocument(array $documentId): void
	{
		$ids = \CBPStateService::getIdsByDocument($documentId, 1);
		if (empty($ids))
		{
			return;
		}

		self::$delay += 60;
		static::bind(self::$delay, $documentId);
	}
}
