<?php

namespace Bitrix\Bizproc\Debugger\Session;

use Bitrix\Bizproc\Automation\Engine\Template;
use Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTemplateShardsTable;
use Bitrix\Iblock\Template\Functions\FunctionConcat;

class WorkflowContext extends Entity\EO_DebuggerSessionWorkflowContext
{
	/**
	 * @param Template | array $template
	 * @return void
	 */
	public function addTemplateShards($template): self
	{
		if (is_a($template, Template::class))
		{
			$this->addAutomationShards($template);
		}

		return $this;
	}

	private function addAutomationShards(Template $template)
	{
		$lastSavedTemplateShards = $this->findTemplateShards($template->getId());

		if (
			$lastSavedTemplateShards
			&& $template->getModified()
			&& $lastSavedTemplateShards->getModified()->toString() === $template->getModified()->toString()
		)
		{
			$this->setTemplateShardsId($lastSavedTemplateShards->getId());
		}
		else
		{
			$shards = [];
			foreach ($template->getRobots() as $robot)
			{
				$shards[] = $robot->toArray();
			}

			$templateShards = DebuggerSessionTemplateShardsTable::createObject();
			$templateShards
				->setShards($shards)
				->setModified($template->getModified())
				->setTemplateId($template->getId())
				->setTemplateType(TemplateShards::TEMPLATE_TYPE_ROBOTS)
			;

			$savingResult = $templateShards->save();
			$this->setTemplateShardsId($savingResult->getId());
		}
	}

	private function findTemplateShards(int $templateId): ?TemplateShards
	{
		return DebuggerSessionTemplateShardsTable::getList([
			'select' => ['ID', 'MODIFIED'],
			'filter' => ['TEMPLATE_ID' => $templateId],
			'order' => ['MODIFIED' => 'DESC'],
			'limit' => 1,
		])->fetchObject();
	}
}