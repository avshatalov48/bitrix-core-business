<?php

namespace Bitrix\Bizproc\Starter;

use Bitrix\Bizproc\Starter\Dto\ContextDto;
use Bitrix\Bizproc\Starter\Dto\DocumentDto;
use Bitrix\Bizproc\Starter\Dto\MetaDataDto;
use Bitrix\Bizproc\Starter\Dto\StarterDto;
use Bitrix\Bizproc\Starter\Dto\StarterConfigDto;
use Bitrix\Bizproc\Starter\Enum\Scenario;
use Bitrix\Bizproc\Starter\Result\StartResult;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\ModuleManager;

final class Starter
{
	private ?ProcessStarter $processStarter;
	private ?AutomationStarter $automationStarter;

	public static function isEnabled(): bool
	{
		return Option::get('bizproc', 'enable_starter', 'N') === 'Y';
	}

	public static function getByScenario(Scenario $scenario): Starter
	{
		$processDto = new StarterConfigDto(scenario: $scenario);
		$automationDto = new StarterConfigDto(scenario: $scenario, validateParameters: false, checkConstants: false);

		return match ($scenario)
		{
			Scenario::onDocumentInnerAdd,
			Scenario::onDocumentInnerUpdate
				=> new self(
					new StarterDto(automation: $automationDto),
				),
			Scenario::onManual => new self(
				new StarterDto(process: $processDto),
			),
			Scenario::onDocumentAdd,
			Scenario::onDocumentUpdate,
				=> new self(
					new StarterDto(process: $processDto, automation: $automationDto)
				),
			Scenario::onEvent => new self(
				new StarterDto(process: $automationDto, automation: $automationDto)
			),
			Scenario::onScript => new self(
				new StarterDto(automation: $automationDto)
			),
			default => new self(new StarterDto())
		};
	}

	public function __construct(StarterDto $dto)
	{
		$this->processStarter = $dto->process ? new ProcessStarter($dto->process) : null;
		$this->automationStarter = $dto->automation ? new AutomationStarter($dto->automation) : null;
	}

	public function setDocument(DocumentDto $dto): self
	{
		$documentId = \CBPHelper::normalizeComplexDocumentId($dto->complexDocumentId);
		if (!$documentId)
		{
			return $this;
		}

		$complexType = null;
		if (
			$dto->complexDocumentType
			&& $documentId[0] === (string)$dto->complexDocumentType[0]
			&& $documentId[1] === (string)$dto->complexDocumentType[1]
		)
		{
			$complexType = \CBPHelper::normalizeComplexDocumentId($dto->complexDocumentType);
		}

		$document = new Document($documentId, $complexType);
		if ($dto->changedFieldNames)
		{
			$document->setChangedFieldNames($dto->changedFieldNames);
		}

		$this->processStarter?->setDocument($document);
		$this->automationStarter?->setDocument($document);

		return $this;
	}

	public function setParameters(array | string $values): self
	{
		if (is_string($values))
		{
			$values = \CBPDocument::unSignParameters($values);
		}

		if (!$values)
		{
			return $this;
		}

		$parameters = new Parameters($values);
		$this->processStarter?->setParameters($parameters);
		$this->automationStarter?->setParameters($parameters);

		return $this;
	}

	public function setValidateParameters(bool $validateParameters = true): self
	{
		if ($this->processStarter)
		{
			$this->processStarter->config->validateParameters = $validateParameters;
		}

		if ($this->automationStarter)
		{
			$this->automationStarter->config->validateParameters = $validateParameters;
		}

		return $this;
	}

	public function setUser(int $userId): self
	{
		if ($userId <= 0)
		{
			return $this;
		}

		$this->processStarter?->setUser($userId);
		$this->automationStarter?->setUser($userId);

		return $this;
	}

	public function setMetaData(MetaDataDto $metaDataDto): self
	{
		$metaData = new MetaData(
			timeToStart: $metaDataDto->timeToStart,
		);

		$this->processStarter?->setMetaData($metaData);
		$this->automationStarter?->setMetaData($metaData);

		return $this;
	}
	public function setTemplateIds(array $templateIds): self
	{
		Collection::normalizeArrayValuesByInt($templateIds, false);

		$this->processStarter?->setTemplateIds($templateIds);
		$this->automationStarter?->setTemplateIds($templateIds);

		return $this;
	}

	public function setCheckConstants(bool $checkConstants = true): self
	{
		if ($this->processStarter)
		{
			$this->processStarter->config->checkConstants = $checkConstants;
		}

		// for automation always disable check constants

		return $this;
	}

	public function setContext(ContextDto $contextDto): self
	{
		$moduleId =
			ModuleManager::isValidModule($contextDto->moduleId) && ModuleManager::isModuleInstalled($contextDto->moduleId)
				? $contextDto->moduleId
				: ''
		;

		$context = new Context(
			$moduleId,
			$contextDto->face,
		);

		$this->processStarter?->setContext($context);
		$this->automationStarter?->setContext($context);

		return $this;
	}

	/** @var DocumentDto[] $documents */
	public function addEvent(string $code, array $documents, array $parameters = []): self
	{
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();

		foreach ($documents as $document)
		{
			$documentType = $document->complexDocumentType;
			if (
				!$documentType
				|| !(
					$documentType[0] === $document->complexDocumentId[0]
					&& $documentType[1] === $document->complexDocumentId[1]
				)
			)
			{
				try
				{
					$documentType = $document->complexDocumentType ?: $documentService->getDocumentType($document->complexDocumentId);
				}
				catch (\Exception $exception)
				{
					// document not found
				}
			}
			if (!$documentType)
			{
				continue;
			}

			$trigger = $documentService->getTriggerByCode($code, $documentType);
			if ($trigger)
			{
				$event =
					(new Event($trigger, $parameters))
						->setDocument(new Document($document->complexDocumentId, $documentType))
				;

				$this->processStarter?->addEvent($event);
				$this->automationStarter?->addEvent($event);
			}
		}

		return $this;
	}

	public function start(): StartResult
	{
		$result = new StartResult();

		if (!self::isEnabled())
		{
			return $result;
		}

		// todo: separate errors?

		if ($this->processStarter)
		{
			$processResult = $this->processStarter->run();
			if (!$processResult->isSuccess())
			{
				$result->addErrors($processResult->getErrors());
			}
			$result->addWorkflowIds($processResult->getWorkflowIds());
			$result->setProcessTriggerApplied($result->isTriggerApplied());
		}

		if ($this->automationStarter)
		{
			$automationResult = $this->automationStarter->run();
			if (!$automationResult->isSuccess())
			{
				$result->addErrors($automationResult->getErrors());
			}
			$result->addWorkflowIds($automationResult->getWorkflowIds());
			$result->setAutomationTriggerApplied($result->isTriggerApplied());
		}

		return $result;
	}
}
