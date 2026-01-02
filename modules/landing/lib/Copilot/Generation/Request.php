<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

use Bitrix\Landing\Copilot\Connector\AI\IConnector;
use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Connector\AI\RequestLimiter;
use Bitrix\Landing\Copilot\Converter;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Generation\Type\Errors;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Model\EO_Requests;
use Bitrix\Landing\Copilot\Model\RequestsTable;
use Bitrix\Landing\Copilot\Model\RequestToStepTable;
use Bitrix\Main;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web;
use Exception;

/**
 * Class Request
 *
 * Handles the lifecycle of a single AI request within a generation step,
 * including sending, result/error processing, persistence, and status management.
 */
class Request
{
	// todo: get individual time from step
	private const MAX_EXPECTED_TIME = 75;

	private int $generationId;
	private int $stepId;
	private ?int $id;
	private ?string $hash = null;
	private ?array $result = null;
	private ?Generation\Error $error = null;
	private bool $isDeleted = false;
	private ?int $stepRelationId;
	private DateTime $dateCreate;
	private DateTime $dateReceive;

	private Type\RequestStatus $status = Type\RequestStatus::New;
	private RequestLimiter $requestLimiter;

	/**
	 * Request constructor.
	 *
	 * @param int $generationId The ID of the generation this request belongs to.
	 * @param int $stepId The step ID within the generation.
	 */
	public function __construct(int $generationId, int $stepId)
	{
		$this->generationId = $generationId;
		$this->stepId = $stepId;
	}

	/**
	 * Sends a request to the AI provider using the given prompt and connector.
	 *
	 * @param Prompt $prompt The prompt to send.
	 * @param IConnector $connector The AI connector to use.
	 *
	 * @return bool True on successful send and save, false otherwise.
	 *
	 * @throws GenerationException If the request fails or a quota/limit is exceeded.
	 */
	public function send(Prompt $prompt, IConnector $connector): bool
	{
		if (!Generation::checkExists($this->generationId))
		{
			return false;
		}

		if ($this->status->value >= Type\RequestStatus::Sent->value)
		{
			return false;
		}

		$result = $connector->request($prompt);
		if (!$result->isSuccess())
		{
			$this->processError($result->getError());
		}

		$this->status = Type\RequestStatus::Sent;
		$data = $result->getData();

		// is queued
		if (isset($data['hash']) && $data['hash'])
		{
			$this->hash = $data['hash'];

			return $this->save();
		}

		// is realtime answer
		if (isset($data['result']) && $data['result'])
		{
			$result = null;
			if (is_string($data['result']))
			{
				try
				{
					$result = Converter\Json::expandJsonString($data['result']);
					$result = Web\Json::decode($result);
				}
				catch (Exception)
				{
					$error = Generation\Error::createError(Errors::requestError);

					return $this->saveError($error);
				}
			}

			return $this->saveResult($result);
		}

		if (isset($data['error']) && $data['error'])
		{
			$error = Generation\Error::createError(Errors::requestError);
			$error->message .= ': ' . $data['error'];

			return $this->saveError($error);
		}

		return false;
	}

	/**
	 * Processes an error from the AI provider and throws a GenerationException.
	 *
	 * @param Main\Error|null $error The error object from the provider.
	 *
	 * @return void
	 *
	 * @throws GenerationException Always throws; either for quota exceeded or generic request error.
	 */
	private function processError(?Main\Error $error): void
	{
		if ($error === null)
		{
			throw new GenerationException(GenerationErrors::notSendRequest);
		}

		if ($this->getRequestLimiter()->checkError($error))
		{
			$message = $this->getRequestLimiter()->getCheckResultMessage();
			if (is_string($message))
			{
				throw new GenerationException(
					GenerationErrors::requestQuotaExceeded,
					$error->getMessage(),
					[
						'errorText' => $message,
						'metrikaStatus' => $this->getRequestLimiter()->getCheckResult()?->getMetrikaStatus(),
					]
				);
			}
		}

		throw new GenerationException(GenerationErrors::errorInRequest, $error->getMessage());
	}

	/**
	 * Retrieves the RequestLimiter instance, initializing it if not already set.
	 *
	 * @return RequestLimiter The RequestLimiter instance.
	 */
	private function getRequestLimiter(): RequestLimiter
	{
		if (empty($this->requestLimiter))
		{
			$this->requestLimiter = new RequestLimiter();
		}

		return $this->requestLimiter;
	}

	/**
	 * Marks the request as applied to the step, updating the status and persistence.
	 *
	 * @return bool True if successfully applied, false otherwise.
	 */
	public function setApplied(): bool
	{
		if ($this->status->value < Type\RequestStatus::Received->value)
		{
			return false;
		}

		if (!isset($this->stepRelationId))
		{
			return false;
		}

		if ($this->status === Type\RequestStatus::Applied)
		{
			return true;
		}

		$res = RequestToStepTable::update($this->stepRelationId, [
			'APPLIED' => true,
		]);
		if (!$res->isSuccess())
		{
			return false;
		}

		$this->status = Type\RequestStatus::Applied;

		return $this->save();
	}

	/**
	 * Saves the result of the AI request.
	 *
	 * @param array $result The result data array.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function saveResult(array $result): bool
	{
		if ($this->status !== Type\RequestStatus::Sent)
		{
			return false;
		}

		$this->result = $result;
		$this->status = Type\RequestStatus::Received;
		$this->dateReceive = new DateTime();

		return $this->save();
	}

	/**
	 * Saves an error code and message for this request.
	 *
	 * @param Generation\Error $error The error DTO.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function saveError(Generation\Error $error): bool
	{
		if ($this->status > Type\RequestStatus::Sent)
		{
			return false;
		}

		$this->error = $error;
		$this->status = Type\RequestStatus::Received;
		$this->dateReceive = new DateTime();

		return $this->save();
	}

	/**
	 * Marks this request as deleted and persists the change.
	 *
	 * @return void
	 */
	public function setDeleted(): void
	{
		$this->isDeleted = true;
		$this->save();
	}

	/**
	 * Saves the current state of the request to the database.
	 *
	 * @return bool True on success, false otherwise.
	 */
	private function save(): bool
	{
		if ($this->status->value < Type\RequestStatus::Sent->value)
		{
			return false;
		}

		$fields = [
			'GENERATION_ID' => $this->generationId,
			'HASH' => $this->hash,
			'RESULT' => $this->result,
			'ERROR' => $this->error?->toArray(),
			'DELETED' => $this->isDeleted,
		];

		if (isset($this->dateReceive))
		{
			$fields['DATE_RECEIVE'] = $this->dateReceive;
		}

		if (isset($this->id) && $this->id)
		{
			$res = RequestsTable::update($this->id, $fields);
			if (!$res->isSuccess())
			{
				return false;
			}
		}
		else
		{
			$res = RequestsTable::add($fields);
			if (!$res->isSuccess())
			{
				return false;
			}
			$this->id = $res->getId();
		}

		if (!isset($this->stepRelationId))
		{
			$res = RequestToStepTable::add([
				'REQUEST_ID' => $this->id,
				'GENERATION_ID' => $this->generationId,
				'STEP' => $this->stepId,
			]);
			if (!$res->isSuccess())
			{
				return false;
			}

			$this->stepRelationId = $res->getId();
		}

		return true;
	}

	/**
	 * Returns the ID of the current generation.
	 *
	 * @return int Generation ID.
	 */
	public function getGenerationId(): int
	{
		return $this->generationId;
	}

	/**
	 * Returns the result data if the request was received, or null otherwise.
	 *
	 * @return array|null Result data array or null.
	 */
	public function getResult(): ?array
	{
		return $this->result;
	}

	/**
	 * Returns the error DTO if the request finished with an error, or null otherwise.
	 *
	 * @return Generation\Error|null Error DTO or null.
	 */
	public function getError(): ?Generation\Error
	{
		return $this->error;
	}

	/**
	 * Returns true if the request has received an answer (result or error).
	 *
	 * @return bool
	 */
	public function isReceived(): bool
	{
		return $this->status === Type\RequestStatus::Received;
	}

	/**
	 * Returns true if the request answer was applied to the step.
	 *
	 * @return bool
	 */
	public function isApplied(): bool
	{
		return $this->status === Type\RequestStatus::Applied;
	}

	/**
	 * Returns the database ID of this request.
	 *
	 * @return int|null Request ID or null if not persisted.
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Returns all existing requests for a given generation and step.
	 *
	 * @param int $generationId Generation ID.
	 * @param int $stepId Step ID.
	 *
	 * @return Request[] Array of existing Request objects.
	 */
	public static function getByGeneration(int $generationId, int $stepId): array
	{
		$filter =
			Query::filter()
				->where('GENERATION_ID', '=', $generationId)
				->where('STEP_REF.STEP', '=', $stepId)
				->where('DELETED', '=', 'N')
		;

		return self::getExists($filter);
	}

	/**
	 * Returns the request by its hash, or null if not found.
	 *
	 * @param string $hash Request hash.
	 *
	 * @return Request|null The found Request object or null.
	 */
	public static function getByHash(string $hash): ?self
	{
		$filter =
			Query::filter()
				->where('HASH', '=', $hash)
				->where('DELETED', '=', 'N')
		;
		$exists = self::getExists($filter);

		return array_shift($exists);
	}

	/**
	 * Returns the request by its database ID, or null if not found.
	 *
	 * @param int $id Request ID.
	 *
	 * @return Request|null The found Request object or null.
	 */
	public static function getById(int $id): ?self
	{
		$filter =
			Query::filter()
				->where('ID', '=', $id)
				->where('DELETED', '=', 'N')
		;
		$exists = self::getExists($filter);

		return array_shift($exists);
	}

	/**
	 * Returns all existing requests matching the given ORM filter.
	 *
	 * @param Filter\ConditionTree $filter ORM filter object.
	 *
	 * @return Request[] Array of Request objects.
	 */
	private static function getExists(Filter\ConditionTree $filter): array
	{
		$exists = [];
		$res = RequestsTable::query()
			->setSelect([
				'ID',
				'GENERATION_ID',
				'HASH',
				'RESULT',
				'ERROR',
				'DELETED',
				'DATE_CREATE',
				'DATE_RECEIVE',
				'STEP' => 'STEP_REF.STEP',
			])
			->where($filter)
			->exec()
		;
		while ($entity = $res->fetchObject())
		{
			if (
				!$entity->getGenerationId()
				|| !$entity->getStepRef()->getStep()
			)
			{
				continue;
			}

			$request = new Request(
				$entity->getGenerationId(),
				$entity->getStepRef()->getStep(),
			);
			$request->initByEntity($entity);

			if ($request->isTimeIsOver())
			{
				$request->setDeleted();

				continue;
			}

			$exists[$entity->getId()] = $request;
		}

		return $exists;
	}

	/**
	 * Initializes the Request object from an EO_Requests entity.
	 *
	 * @param EO_Requests $request The ORM entity object.
	 *
	 * @return self The initialized Request object.
	 */
	private function initByEntity(EO_Requests $request): self
	{
		$this->id = $request->getId();
		$this->isDeleted = $request->getDeleted();

		$hash = $request->getHash();
		if ($hash)
		{
			$this->hash = $hash;
			$this->status = Type\RequestStatus::Sent;
		}

		$result = $request->getResult();
		$error = $request->getError();
		if (!empty($result))
		{
			$this->result = $result;
			$this->status = Type\RequestStatus::Received;
		}
		elseif (!empty($error))
		{
			$this->error = Generation\Error::fromArray($error);
			$this->status = Type\RequestStatus::Received;
		}

		$this->dateCreate = $request->getDateCreate();
		$dateReceive = $request->getDateReceive();
		if ($dateReceive)
		{
			$this->dateReceive = $dateReceive;
		}

		$step = $request->getStepRef();
		if ($step)
		{
			$step->fillApplied();
			$this->stepRelationId = $step->getId();
			if (!empty($this->result) && $step->getApplied())
			{
				$this->status = Generation\Type\RequestStatus::Applied;
			}
		}

		return $this;
	}

	/**
	 * Checks if the request has exceeded the maximum expected time without a result or error.
	 *
	 * @return bool True if time is over, false otherwise.
	 */
	private function isTimeIsOver(): bool
	{
		if (
			isset($this->dateReceive)
			|| isset($this->result)
			|| isset($this->error)
		)
		{
			return false;
		}

		return ((new \DateTime())->getTimestamp() - $this->dateCreate->getTimestamp()) > self::MAX_EXPECTED_TIME;
	}
}