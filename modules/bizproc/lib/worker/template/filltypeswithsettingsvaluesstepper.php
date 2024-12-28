<?php

namespace Bitrix\Bizproc\Worker\Template;

use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Main;

class FillTypesWithSettingsValuesStepper extends Main\Update\Stepper
{
	protected static $moduleId = 'bizproc';
	private const STEP_ROWS_LIMIT = 100;

	public function execute(array &$option)
	{
		$lastId = (int)($this->getOuterParams()[0] ?? 0);
		$newLastId = null;

		$result = \CBPWorkflowTemplateLoader::getList(
			['ID'=>'ASC'],
			['>ID' => $lastId],
			false,
			['nTopCount' => self::STEP_ROWS_LIMIT],
			['ID', 'AUTO_EXECUTE', 'TEMPLATE', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE']
		);

		$loader = \CBPWorkflowTemplateLoader::GetLoader();

		while ($row = $result->fetch())
		{
			try
			{
				$newLastId = (int)$row['ID'];
				$loader->getTemplateType($row);
				$loader->setTemplateType($row);

				WorkflowTemplateTable::update(
					$row['ID'],
					['TYPE' => $row['TYPE']],
				);
			}
			catch (\Throwable $e)
			{

			}
		}

		if ($newLastId && $newLastId !== $lastId)
		{
			$this->setOuterParams([$newLastId]);

			return self::CONTINUE_EXECUTION;
		}

		return self::FINISH_EXECUTION;
	}
}