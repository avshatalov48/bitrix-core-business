<?php

namespace Bitrix\Bizproc\Starter;

use Bitrix\Bizproc\Api\Enum\ErrorMessage;
use Bitrix\Bizproc\Error;
use Bitrix\Bizproc\Starter\Dto\StarterConfigDto;
use Bitrix\Bizproc\Starter\Enum\Scenario;
use Bitrix\Bizproc\Starter\Result\StartResult;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;

abstract class BaseTypeStarter
{
	use ErrorableImplementation;

	protected ?ModuleSettings $moduleSettings = null;

	public readonly StarterConfigDto $config;
	protected ?Document $document = null;
	protected ?Parameters $parameters = null;
	protected ?MetaData $metaData = null;
	protected ?Context $context = null;
	/** @var array<int, Event>  */
	protected array $events = [];
	protected array $templateIds = [];
	protected int $userId = 0;
	protected bool $isTriggerApplied = false;

	protected array $startedWorkflows = [];

	public function __construct(StarterConfigDto $dto)
	{
		$this->errorCollection = new ErrorCollection();
		$this->config = $dto;
	}

	public function setDocument(Document $document): self
	{
		$this->document = $document;

		if ($this->document->complexType)
		{
			$this->moduleSettings =
				\CBPRuntime::getRuntime()->getDocumentService()
					->getStarterModuleSettings($this->document->complexType)
			;
		}

		return $this;
	}

	public function setParameters(Parameters $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}

	public function setUser(int $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	public function setMetaData(MetaData $metaData): self
	{
		$this->metaData = $metaData;

		return $this;
	}

	public function setTemplateIds(array $templateIds): self
	{
		$this->templateIds = $templateIds;

		return $this;
	}

	public function setContext(Context $context): self
	{
		$this->context = $context;

		return $this;
	}

	public function addEvent(Event $event): self
	{
		$this->events[] = $event;

		return $this;
	}

	public function run(): StartResult
	{
		$result = $this->runEventScenario();
		if ($this->config->scenario === Scenario::onEvent)
		{
			return $result ? $this->getSuccessResult() : $this->getFailedResult();
		}

		if ($this->config->checkFeature && !$this->checkFeature())
		{
			$this->errorCollection->add([ErrorMessage::FEATURE_DISABLED->getError()]);

			return $this->getFailedResult();
		}

		if ($this->config->checkLimits && $this->isOverLimited())
		{
			$this->errorCollection->add([ErrorMessage::OVER_LIMITED->getError()]);

			return $this->getFailedResult();
		}

		$result = match ($this->config->scenario)
		{
			Scenario::onManual => $this->runManualScenario(),
			Scenario::onDocumentAdd,
			Scenario::onDocumentInnerAdd
				=> $this->runOnAddScenario(),
			Scenario::onDocumentUpdate,
			Scenario::onDocumentInnerUpdate
				=> $this->runOnUpdateScenario(),
			Scenario::onScript => $this->runOnScriptScenario(),
			default => true,
		};

		return $result ? $this->getSuccessResult() : $this->getFailedResult();
	}

	abstract protected function checkFeature(?ModuleSettings $moduleSettings = null): bool;
	abstract protected function isOverLimited(?ModuleSettings $moduleSettings = null): bool;

	abstract protected function runManualScenario(): bool;

	abstract protected function runOnAddScenario(): bool;

	abstract protected function runOnUpdateScenario(): bool;

	abstract protected function runEventScenario(): bool;

	abstract protected function runOnScriptScenario(): bool;

	abstract protected function getTemplatesByScenario(): array;

	protected function checkConstantsTuned(int $templateId): bool
	{
		if (!$this->config->checkConstants || $templateId <= 0)
		{
			return true;
		}

		if (\CBPWorkflowTemplateLoader::isConstantsTuned($templateId))
		{
			return true;
		}

		$this->errorCollection->add([ErrorMessage::CONSTANTS_NOT_TUNED->getError()]);

		return false;
	}

	protected function validateParameters(int $templateId, array $templateParameters): ?array
	{
		if (!$this->parameters || !$this->document?->complexType)
		{
			// todo: default values or empty?
			return [];
		}

		if (!$this->config->validateParameters)
		{
			return $this->parameters->getValues($templateId, $templateParameters);
		}

		$result = $this->parameters->getValidatedValues($templateId, $templateParameters, $this->document->complexType);
		if ($result->isSuccess())
		{
			return $result->getData()['values'];
		}

		$this->errorCollection->add($result->getErrors());

		return null;
	}

	protected function runMultiWorkflows(array $startParameters = []): bool
	{
		$templates = $this->getTemplatesByScenario();

		$result = true;
		foreach ($templates as $template)
		{
			$workflowId = $this->runWorkflow($template, $startParameters);
			if ($workflowId === null)
			{
				$result = false;
			}
		}

		return $result;
	}

	protected function runWorkflow(array $template, array $startParameters = []): ?string
	{
		if (!$this->document?->complexId)
		{
			return null;
		}

		$templateId = $template['ID'];
		if (!$this->checkConstantsTuned($templateId))
		{
			return null;
		}

		$templateParameters = $template['PARAMETERS'];

		$parameters = $this->validateParameters($templateId, $templateParameters);
		if ($parameters === null)
		{
			return null;
		}

		$parameters = array_merge($parameters, $startParameters);

		$workflowId = $this->startWorkflow($templateId, $this->document->complexId, $parameters);
		if ($workflowId)
		{
			$this->sendMetaData($workflowId); // only for processes? or automation too
		}

		return $workflowId;
	}

	protected function startWorkflow(int $templateId, array $complexDocumentId, array $parameters): ?string
	{
		$errors = [];
		$workflowId = \CBPDocument::startWorkflow($templateId, $complexDocumentId, $parameters, $errors);
		if ($errors)
		{
			$this->errorCollection->add(
				array_map(static fn($error) => new Error($error['message'], $error['code']), $errors)
			);
		}

		if ($workflowId)
		{
			$this->addWorkflowIdToCache($workflowId);
		}

		return $workflowId;
	}

	protected function addWorkflowIdToCache(string $workflowId): void
	{
		$this->startedWorkflows[$workflowId] = true;
	}

	protected function sendMetaData(string $workflowId): void
	{
		$this->metaData?->saveToWorkflowId($workflowId);
	}

	protected function getTargetUserForStartParameters(): ?string
	{
		return $this->userId > 0 ? 'user_' . $this->userId : null;
	}

	protected function getDocumentTypeForStartParameters(): ?array
	{
		return $this->document?->complexType ?: null;
	}

	protected function getFailedResult(): StartResult
	{
		return
			(new StartResult())
				->addErrors($this->getErrors())
				->setWorkflowIds($this->unsetWorkflowIds())
				->setTriggerApplied($this->isTriggerApplied)
		;
	}

	protected function getSuccessResult(): StartResult
	{
		return
			StartResult::createOk()
				->setWorkflowIds(array_keys($this->unsetWorkflowIds()))
				->setTriggerApplied($this->isTriggerApplied)
		;
	}

	protected function unsetWorkflowIds(): array
	{
		$workflowIds = $this->startedWorkflows;
		$this->startedWorkflows = [];

		return $workflowIds;
	}
}
