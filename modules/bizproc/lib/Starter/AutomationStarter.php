<?php

namespace Bitrix\Bizproc\Starter;

use Bitrix\Bizproc\Api\Enum\ErrorMessage;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Bizproc\Automation\Trigger\BaseTrigger;
use Bitrix\Bizproc\Runtime\Starter\Context;
use Bitrix\Bizproc\Starter\Enum\Scenario;

final class AutomationStarter extends BaseTypeStarter
{
	private bool $isTriggerAppliedOnCurrentDocument = false;

	protected function checkFeature(?ModuleSettings $moduleSettings = null): bool
	{
		$moduleSettings = $moduleSettings ?: $this->moduleSettings;

		if (!$moduleSettings)
		{
			return true; // no way to check feature
		}

		if ($this->config->scenario === Scenario::onScript)
		{
			return $moduleSettings->isScriptFeatureEnabled();
		}

		return $moduleSettings->isAutomationFeatureEnabled();
	}

	protected function isOverLimited(?ModuleSettings $moduleSettings = null): bool
	{
		$moduleSettings = $moduleSettings ?: $this->moduleSettings;

		if (
			$this->config->scenario === Scenario::onScript
			|| !$moduleSettings // no way to check limit
		)
		{
			// no limit
			return false;
		}

		if ($moduleSettings->isAutomationLimited())
		{
			return $moduleSettings->isAutomationOverLimited();
		}

		return false;
	}

	protected function runManualScenario(): bool
	{
		// not supported

		return true;
	}

	protected function runOnAddScenario(): bool
	{
		if ($this->isTriggerAppliedOnCurrentDocument)
		{
			return true;
		}

		$target = $this->getTarget();
		if (!$target)
		{
			return true;
		}

		$context = new Context();
		if ($this->config->scenario === Scenario::onDocumentAdd)
		{
			$context->setIsManual();
		}

		if ($this->context)
		{
			if ($this->context->moduleId)
			{
				$context->setModuleId($this->context->moduleId);
			}

			$context->setFace($this->context->face->value);
		}

		if ($this->userId > 0)
		{
			$context->setUserId($this->userId);
		}

		$workflowId = $target->getRuntime()->onDocumentAdd($context);
		if ($workflowId)
		{
			$this->addWorkflowIdToCache($workflowId);
		}

		return true;
	}

	protected function runOnUpdateScenario(): bool
	{
		$target = $this->getTarget();
		if (!$target)
		{
			return true;
		}

		if ($this->document?->hasChangedFields())
		{
			$target->getRuntime()->onFieldsChanged($this->document->getChangedFieldNames());
		}

		if ($this->isTriggerAppliedOnCurrentDocument)
		{
			return true;
		}

		$statusFieldName = $this->moduleSettings?->getDocumentStatusFieldName();
		if ($statusFieldName && $this->document?->isFieldChanged($statusFieldName))
		{
			$workflowId = $target->getRuntime()->onDocumentStatusChanged();
			if ($workflowId)
			{
				$this->addWorkflowIdToCache($workflowId);
			}
		}

		return true;
	}

	protected function runEventScenario(): bool
	{
		$result = true;
		foreach ($this->events as $event)
		{
			if (!$event->getDocument())
			{
				continue;
			}

			$moduleSettings =
				$this->document?->complexType === $event->getDocument()->complexType
					? $this->moduleSettings
					: \CBPRuntime::getRuntime()->getDocumentService()
						->getStarterModuleSettings($event->getDocument()->complexType)
			;

			$allDocuments = $moduleSettings?->getTriggerRelatedDocuments($event->getCode(), $event->getDocument()) ?? [];
			foreach ($allDocuments as $document)
			{
				if (!$this->checkFeatureAndLimitForDocument($document))
				{
					$result = false;

					continue;
				}

				$target = $this->getTarget($document);
				if (!$target)
				{
					continue;
				}

				/** @var BaseTrigger $trigger  */
				$trigger =
					(new ($event->getTrigger()))
						->setTarget($target)
						->setInputData($event->getParameters())
				;
				if ($trigger->send())
				{
					$this->isTriggerApplied = true;
					if (
						$this->document
						&& \CBPHelper::isEqualDocument($this->document->complexId, $document->complexId)
					)
					{
						$this->isTriggerAppliedOnCurrentDocument = true;
					}
				}
			}
		}

		return $result;
	}

	private function checkFeatureAndLimitForDocument(Document $document): bool
	{
		if (!$document->complexType)
		{
			return true; // no way to check feature/limit
		}

		$moduleSettings =
			$this->document?->complexType === $document->complexType
				? $this->moduleSettings
				: \CBPRuntime::getRuntime()->getDocumentService()->getStarterModuleSettings($document->complexType)
		;

		if (!$moduleSettings)
		{
			return true;  // no way to check feature/limit
		}

		if ($this->config->checkFeature && !$this->checkFeature($moduleSettings))
		{
			$this->errorCollection->add([ErrorMessage::FEATURE_DISABLED->getError()]);

			return false;
		}

		if ($this->config->checkLimits && $this->isOverLimited($moduleSettings))
		{
			$this->errorCollection->add([ErrorMessage::OVER_LIMITED->getError()]);

			return false;
		}

		return true;
	}

	protected function runOnScriptScenario(): bool
	{
		return $this->runMultiWorkflows([
			\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE => \CBPDocumentEventType::Script,
			\CBPDocument::PARAM_TAGRET_USER => $this->getTargetUserForStartParameters(),
			\CBPDocument::PARAM_USE_FORCED_TRACKING => true,
			\CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT => true,
			\CBPDocument::PARAM_DOCUMENT_TYPE => $this->getDocumentTypeForStartParameters(),
		]);
	}

	protected function getTemplatesByScenario(): array
	{
		$complexDocumentType = $this->document?->complexType;
		if (!$complexDocumentType)
		{
			return []; // no document, no templates for now
		}

		$filter = ['DOCUMENT_TYPE' => $complexDocumentType, 'ACTIVE' => 'Y'];
		$filter['AUTO_EXECUTE'] = match ($this->config->scenario)
		{
			Scenario::onScript => \CBPDocumentEventType::Script,
			default => \CBPDocumentEventType::Automation,
		};

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

	private function getTarget(?Document $document = null): ?BaseTarget
	{
		$document = $document ?? $this->document;

		if (!$document || !$document->complexType)
		{
			return null;
		}

		/** @var BaseTarget $target */
		$target = \CBPRuntime::getRuntime()->getDocumentService()->createAutomationTarget($document->complexType, $document->getId());
		if (!$target)
		{
			return null;
		}

		$target->setDocumentId($document->getId());

		return $target;
	}
}
