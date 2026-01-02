<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Scenario;

use Bitrix\Landing\Copilot\Connector;
use Bitrix\Landing\Copilot\Connector\AI\RequestLimiter;
use Bitrix\Landing\Copilot\Connector\Chat\ChatBotMessageDto;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;
use Bitrix\Landing\Copilot\Generation\Type\ScenarioStepDto;
use Bitrix\Landing\Copilot\Generation\Type\StepStatuses;
use Bitrix\Landing\Copilot\Model\StepsTable;
use Bitrix\Landing\Metrika;
use Bitrix\Main\Loader;

/**
 * Class Scenarist
 *
 * Manages the execution flow of a scenario consisting of multiple steps for AI-powered content generation.
 * Handles step status tracking, error handling, quota checks, analytics, and callback hooks for step changes and
 * completion.
 */
class Scenarist
{
	private const EVENT_STEP = 'onExecuteStep';

	private IScenario $scenario;
	private Generation $generation;

	private ?int $stepId;

	/**
	 * @var ScenarioStepDto[] Array of scenario steps indexed by step ID.
	 */
	private array $steps;

	/**
	 * @var callable|null Callback invoked when the step ID changes and must be saved.
	 */
	private $onStepChangeCallback;

	/**
	 * @var callable|null Callback invoked when site data changes and must be saved.
	 */
	private $onSaveCallback;

	/**
	 * @var callable|null Callback invoked when the scenario is finished.
	 */
	private $onFinishCallback;

	private RequestLimiter $requestLimiter;

	/**
	 * Scenarist constructor.
	 *
	 * @param IScenario $scenario The scenario instance to execute.
	 * @param Generation $generation The generation context.
	 */
	public function __construct(IScenario $scenario, Generation $generation)
	{
		$this->scenario = $scenario;
		$this->generation = $generation;

		$this->stepId = $this->generation->getStep();

		$this->initSteps();
	}

	/**
	 * Initializes the steps array from the scenario map and loads persisted step statuses.
	 *
	 * @return void
	 */
	private function initSteps(): void
	{
		foreach ($this->scenario->getMap() as $stepId => $step)
		{
			$step->init($this->generation, $stepId);
			$scenarioStep = new ScenarioStepDto(
				$stepId,
				$step,
				StepStatuses::New,
			);
			$this->steps[$stepId] = $scenarioStep;
		}

		$query = StepsTable::query()
			->setSelect(['ID', 'STEP_ID', 'STATUS'])
			->where('GENERATION_ID', $this->generation->getId())
			->exec()
		;
		while ($step = $query->fetch())
		{
			if (
				isset($this->steps[(int)$step['STEP_ID']])
				&& StepStatuses::tryFrom((int)$step['STATUS'])
			)
			{
				$this->steps[(int)$step['STEP_ID']]->status =
					StepStatuses::from((int)$step['STATUS']);
				$this->steps[(int)$step['STEP_ID']]->entityId = (int)$step['ID'];
			}
		}
	}

	/**
	 * Returns the current step ID.
	 *
	 * @return int|null
	 */
	public function getStep(): ?int
	{
		return $this->stepId;
	}

	/**
	 * Checks if all scenario steps are finished.
	 *
	 * @return bool True if all steps are finished, false otherwise.
	 */
	public function isFinished(): bool
	{
		foreach ($this->steps as $step)
		{
			if ($step->status !== StepStatuses::Finished)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Marks all scenario steps as finished.
	 *
	 * @return void
	 */
	public function finish(): void
	{
		foreach ($this->steps as $step)
		{
			if ($step->status !== StepStatuses::Finished)
			{
				$this->saveStepStatus($step, StepStatuses::Finished);
			}
		}
	}

	/**
	 * Checks if at least one scenario step has an error and is not executed.
	 *
	 * @return bool True if any step is in error state, false otherwise.
	 */
	public function isError(): bool
	{
		foreach ($this->steps as $step)
		{
			if ($step->status === StepStatuses::Error)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Prepares the scenario to restart generation after an error by clearing errors in all steps.
	 *
	 * @return void
	 */
	public function clearErrors(): void
	{
		foreach ($this->steps as $step)
		{
			if (
				$step->status->value > StepStatuses::New->value
				&& $step->status->value < StepStatuses::Finished->value
			)
			{
				$step->step->clearErrors();
				$this->saveStepStatus($step, StepStatuses::New);
			}
		}
	}

	/**
	 * Sets a callback to be called when the step ID changes.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function onStepChange(callable $callback): self
	{
		$this->onStepChangeCallback = $callback;

		return $this;
	}

	/**
	 * Invokes the step change callback if set.
	 *
	 * @return void
	 */
	private function callOnStepChange(): void
	{
		if (isset($this->onStepChangeCallback) && is_int($this->stepId))
		{
			call_user_func($this->onStepChangeCallback, $this->stepId);
		}
	}

	/**
	 * Sets a callback to be called when the scenario state should be saved.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function onSave(callable $callback): self
	{
		$this->onSaveCallback = $callback;

		return $this;
	}

	/**
	 * Invokes the save callback if set.
	 *
	 * @return void
	 */
	private function callOnSave(): void
	{
		if (isset($this->onSaveCallback))
		{
			call_user_func($this->onSaveCallback);
		}
	}

	/**
	 * Sets a callback to be called when the scenario is finished.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function onFinish(callable $callback): self
	{
		$this->onFinishCallback = $callback;

		return $this;
	}

	/**
	 * Invokes the finish callback if set.
	 *
	 * @return void
	 */
	private function callOnFinish(): void
	{
		if (isset($this->onFinishCallback))
		{
			call_user_func($this->onFinishCallback);
		}
	}

	/**
	 * Executes the scenario, running steps in order and handling errors, quotas, and analytics.
	 *
	 * @return void
	 *
	 * @throws GenerationException If a step fails or a quota is exceeded.
	 */
	public function execute(): void
	{
		if (!$this->scenario->checkStep($this->stepId))
		{
			return;
		}

		$this->stepId = $this->stepId ?? $this->scenario->getFirstStepId();
		if (!$this->stepId)
		{
			return;
		}

		if ($this->stepId === $this->scenario->getFirstStepId())
		{
			$this->sendMetrikaStart();
		}

		foreach ($this->steps as $stepId => $stepDto)
		{
			if ($stepId > $this->stepId)
			{
				break;
			}

			if (!$this->isNeedExecuteStep($stepDto))
			{
				continue;
			}

			try
			{
				$this->executeStep($stepDto);
			}
			catch (GenerationException $e)
			{
				$this->saveStepStatus($stepDto, StepStatuses::Error);
				throw $e;
			}

			if (
				$this->changeStep($stepDto)
				|| $stepDto->step->isFinished()
			)
			{
				$this->callOnSave();
			}
		}

		if ($this->isFinished())
		{
			$this->scenario->onFinish($this->generation);
			$this->callOnFinish();
		}
	}

	private function isNeedExecuteStep(ScenarioStepDto $stepDto): bool
	{
		if (
			$stepDto->status === StepStatuses::Finished
			|| $stepDto->status === StepStatuses::Error
		)
		{
			return false;
		}

		$relations = $this->scenario->getAsyncRelations();
		if ($relations)
		{
			foreach ($relations as $parent => $dependents)
			{
				if (
					in_array($stepDto->stepId, $dependents, true)
					&& !$this->steps[$parent]?->step->isFinished()
				)
				{
					return false;
				}
			}
		}

		return true;
	}

	private function changeStep(ScenarioStepDto $executedStep): bool
	{
		if (
			$executedStep->step->isFinished()
			|| $executedStep->step->isAsync()
		)
		{
			$newStep = $this->getNextStep($executedStep->stepId);
			if (!$newStep)
			{
				return false;
			}
			if ($newStep > $this->stepId)
			{
				$this->stepId = $newStep;
				$this->callOnStepChange();

				return true;
			}
		}

		return false;
	}

	private function getNextStep(int $stepId): ?int
	{
		$scenario = $this->scenario;
		$recursiveGetNext = function ($currentId) use ($scenario, &$recursiveGetNext)
		{
			$nextId = $scenario->getNextStepId($currentId);
			$relations = $scenario->getAsyncRelations();
			if ($relations)
			{
				foreach ($relations as $parent => $dependents)
				{
					if (
						in_array($nextId, $dependents, true)
						&& !$this->steps[$parent]?->step->isFinished()
					)
					{
						return $recursiveGetNext($nextId);
					}
				}
			}

			return $nextId;
		};

		return $recursiveGetNext($stepId);
	}

	/**
	 * Get step DTO for current step ID
	 * @return ScenarioStepDto|null
	 */
	public function getCurrentStep(): ?ScenarioStepDto
	{
		if (!$this->scenario->checkStep($this->stepId))
		{
			return null;
		}

		return $this->steps[$this->stepId];
	}

	/**
	 * Executes a single scenario step, handling quota checks and status updates.
	 *
	 * @param ScenarioStepDto $step The step to execute.
	 *
	 * @return void
	 *
	 * @throws GenerationException If the step fails or a quota is exceeded.
	 */
	private function executeStep(ScenarioStepDto $step): void
	{
		if (
			!$step->step->isStarted()
			&& $step->stepId === $this->scenario->getQuotaCalculateStep()
			&& $this->checkRequestQuotas()
		)
		{
			$requestLimiter = $this->getRequestLimiter();
			$message = $requestLimiter->getCheckResultMessage();
			if (is_string($message))
			{
				throw new GenerationException(
					GenerationErrors::requestQuotaExceeded,
					$message,
					[
						'errorText' => $message,
						'metrikaStatus' => $requestLimiter->getCheckResult()?->getMetrikaStatus(),
					]
				);
			}
		}

		$step->step->execute();

		if ($step->step->isStarted())
		{
			$this->saveStepStatus($step, StepStatuses::Started);
		}

		if ($step->step->isFinished())
		{
			$this->saveStepStatus($step, StepStatuses::Finished);
			if ($step->step->isChanged())
			{
				$this->sendMetrikaStepSuccess($step);
			}
		}

		if ($step->step->isChanged() || $step->step->isFinished())
		{
			$this->callOnSave();
		}

		$this->generation->getEvent()->send(self::EVENT_STEP);
	}

	/**
	 * Saves the status of a scenario step to the database.
	 *
	 * @param ScenarioStepDto $step The step whose status is being saved.
	 * @param StepStatuses $status The new status.
	 *
	 * @return void
	 */
	private function saveStepStatus(ScenarioStepDto $step, StepStatuses $status): void
	{
		$step->status = $status;

		if (!isset($step->entityId))
		{
			$resAdd = StepsTable::add([
				'GENERATION_ID' => $this->generation->getId(),
				'STEP_ID' => $step->stepId,
				'CLASS' => $step->step::class,
				'STATUS' => $status->value,
			]);

			if ($resAdd->isSuccess())
			{
				$step->entityId = $resAdd->getId();
			}

			return;
		}

		StepsTable::update($step->entityId, [
			'STATUS' => $status->value,
		]);
	}

	/**
	 * Checks if the request quota for the scenario is exceeded.
	 *
	 * @return bool True if the request quota is exceeded, false otherwise.
	 */
	private function checkRequestQuotas(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		if (
			!$this->generation->getChatId()
			|| $this->generation->getChatId() <= 0
		)
		{
			return false;
		}

		$requestLimiter = $this->getRequestLimiter();

		$isRequestQuotaExceeded = true;
		if (!$requestLimiter->checkQuota($this->getRequestQuotasSum()))
		{
			$isRequestQuotaExceeded = false;
			// todo: can change to this->scenario?
			$this->generation->getScenario()?->getChatbot()?->onRequestQuotaOk(
				new ChatBotMessageDto(
					$this->generation->getChatId(),
					$this->generation->getId(),
				)
			);
		}

		return $isRequestQuotaExceeded;
	}

	/**
	 * Returns an array of request quotas for all steps in the scenario.
	 *
	 * @return RequestQuotaDto[] Array of request quota DTOs.
	 */
	private function getRequestQuotas(): array
	{
		/**
		 * @var RequestQuotaDto[] $quotas
		 */
		$quotas = [];
		foreach ($this->scenario->getMap() as $step)
		{
			$stepQuota = $step::getRequestQuota($this->generation->getSiteData());
			if (!$stepQuota)
			{
				continue;
			}

			if (isset($quotas[$stepQuota->connectorClass]))
			{
				$quotas[$stepQuota->connectorClass]->requestCount += $stepQuota->requestCount;
			}
			else
			{
				$quotas[$stepQuota->connectorClass] = $stepQuota;
			}
		}

		return $quotas;
	}

	/**
	 * Returns the sum of all request quotas, ignoring types.
	 *
	 * @return int Total request count.
	 */
	private function getRequestQuotasSum(): int
	{
		$requestCount = 0;
		foreach ($this->getRequestQuotas() as $quota)
		{
			$requestCount += $quota->requestCount;
		}

		return $requestCount;
	}

	/**
	 * Retrieves the RequestLimiter instance, initializing it if not already set.
	 *
	 * @return RequestLimiter
	 */
	private function getRequestLimiter(): RequestLimiter
	{
		if (!isset($this->requestLimiter))
		{
			$this->requestLimiter = new RequestLimiter();
		}

		return $this->requestLimiter;
	}

	/**
	 * Sends a Metrika analytics event for the start of the scenario.
	 *
	 * @return void
	 */
	private function sendMetrikaStart(): void
	{
		$metrika = $this->generation->getMetrika(Metrika\Events::start);
		$metrika->send();
	}

	/**
	 * Sends a Metrika analytics event for a successful step execution.
	 *
	 * @param ScenarioStepDto $step The step for which to send analytics.
	 *
	 * @return void
	 */
	private function sendMetrikaStepSuccess(ScenarioStepDto $step): void
	{
		$event = $step->step->getAnalyticEvent();
		if (isset($event))
		{
			$metrika = $this->generation->getMetrika($event);
			$metrika->send();
		}
	}
}