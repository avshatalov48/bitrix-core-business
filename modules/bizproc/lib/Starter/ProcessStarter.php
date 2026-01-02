<?php

namespace Bitrix\Bizproc\Starter;

use Bitrix\Bizproc\Starter\Enum\Scenario;
use Bitrix\Bizproc\Workflow\Template\WorkflowTemplateSettingsTable;

final class ProcessStarter extends BaseTypeStarter
{
	protected function checkFeature(?ModuleSettings $moduleSettings = null): bool
	{
		return \CBPRuntime::isFeatureEnabled();
	}

	protected function isOverLimited(?ModuleSettings $moduleSettings = null): bool
	{
		return false;
	}

	protected function runManualScenario(): bool
	{
		return $this->runMultiWorkflows([
			\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE => \CBPDocumentEventType::Manual,
			\CBPDocument::PARAM_TAGRET_USER => $this->getTargetUserForStartParameters(),
			\CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS => false,
			\CBPDocument::PARAM_DOCUMENT_TYPE => $this->getDocumentTypeForStartParameters(),
		]);
	}

	protected function runOnAddScenario(): bool
	{
		return $this->runMultiWorkflows([
			\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE => \CBPDocumentEventType::Create,
			\CBPDocument::PARAM_TAGRET_USER => $this->getTargetUserForStartParameters(),
			\CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS => false,
			\CBPDocument::PARAM_DOCUMENT_TYPE => $this->getDocumentTypeForStartParameters(),
		]);
	}

	protected function runOnUpdateScenario(): bool
	{
		return $this->runMultiWorkflows([
			\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE => \CBPDocumentEventType::Edit,
			\CBPDocument::PARAM_TAGRET_USER => $this->getTargetUserForStartParameters(),
			\CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS => (
				$this->document?->hasChangedFields() ? $this->document->getChangedFieldNames() : false
			),
			\CBPDocument::PARAM_DOCUMENT_TYPE => $this->getDocumentTypeForStartParameters(),
		]);
	}

	protected function runEventScenario(): bool
	{
		$result = true;
		foreach ($this->events as $event)
		{
			$document = $event->getDocument();
			if (!$document || !$document->getType())
			{
				continue;
			}

			// region temporary
			$code = $event->getCode();
			$triggers =
				WorkflowTemplateSettingsTable::query()
					->setSelect(['TEMPLATE.ID', 'TEMPLATE.PARAMETERS'])
					->where('NAME', "TRIGGER_$code")
					->where('VALUE', 'Y')
					->where('TEMPLATE.MODULE_ID', $document->getModuleId())
					->where('TEMPLATE.ENTITY', $document->getEntity())
					->where('TEMPLATE.DOCUMENT_TYPE', $document->getType())
					->exec()
					->fetchCollection()
			;
			// endregion

			foreach ($triggers as $trigger)
			{
				$template = $trigger->getTemplate();
				if ($template)
				{
					// no parameters, no check constants
					$workflowId = $this->startWorkflow(
						$template->getId(),
						$document->complexId,
						[
							\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE => \CBPDocumentEventType::None, // new type: event?
							\CBPDocument::PARAM_TAGRET_USER => 0, // no user or user from trigger settings, not current
							\CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS => false,
							\CBPDocument::PARAM_DOCUMENT_TYPE => $document->complexType ?: null,
						]
					);

					// no meta data

					if (!$workflowId)
					{
						$result = false;
					}
					else
					{
						$this->isTriggerApplied = true;
					}
				}
			}
		}

		return $result;
	}

	protected function runOnScriptScenario(): bool
	{
		// Script scenario is not supported, only as automation

		return true;
	}

	protected function getTemplatesByScenario(): array
	{
		// script scenario is not supported
		if (in_array($this->config->scenario, [Scenario::onEvent, Scenario::onScript], true))
		{
			return [];
		}

		// todo: static cache?
		$complexDocumentType = $this->document?->complexType;
		if (!$complexDocumentType)
		{
			return []; // no document, no templates for now
		}

		$filter = ['DOCUMENT_TYPE' => $complexDocumentType, 'ACTIVE' => 'Y'];

		switch ($this->config->scenario)
		{
			case Scenario::onManual:
				$filter['<AUTO_EXECUTE'] = \CBPDocumentEventType::Automation;
				break;
			case Scenario::onDocumentAdd:
			case Scenario::onDocumentInnerAdd:
				$filter['AUTO_EXECUTE'] = \CBPDocumentEventType::Create;
				break;
			case Scenario::onDocumentUpdate:
			case Scenario::onDocumentInnerUpdate:
				$filter['AUTO_EXECUTE'] = \CBPDocumentEventType::Edit;
				break;
			default:
				// nothing to add to filter
		}

		if ($this->templateIds)
		{
			if (count($this->templateIds) === 1)
			{
				$filter['=ID'] = current($this->templateIds);
			}
			else
			{
				$filter['@ID'] = $this->templateIds;
			}
		}

		$select = ['ID', 'PARAMETERS'];
		$list = \CBPWorkflowTemplateLoader::getList([], $filter, false, false, $select);

		$templates = [];
		while ($template = $list->fetch())
		{
			$templates[] = $template;
		}

		return $templates;
	}
}
