<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Generation\Request;
use Bitrix\Landing\Copilot\Generation\Type\RequestEntityDto;

abstract class RequestMultiple extends AIStep
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @var RequestEntityDto[]
	 */
	protected array $entities = [];

	/**
	 * @var array<Request>
	 */
	protected array $requests = [];

	abstract public static function getConnectorClass(): string;

	/**
	 * @inheritdoc
	 */
	protected function initialize(): void
	{
		$this->requests = Request::getByGeneration($this->generation->getId(), $this->stepId);
	}

	/**
	 * @inheritdoc
	 */
	public function execute(): bool
	{
		if (!parent::execute())
		{
			return false;
		}

		if (!isset($this->siteData))
		{
			return false;
		}

		if (
			$this->isStarted()
			&& $this->applyResponses()
		)
		{
			$this->changed = true;
		}

		if (!$this->isFinished())
		{
			$this->sendRequests();
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function isAsync(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function isStarted(): bool
	{
		foreach ($this->getEntitiesToRequest() as $entity)
		{
			if (isset($entity->requestId, $this->requests[$entity->requestId]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function isFinished(): bool
	{
		if (empty($this->getEntitiesToRequest()))
		{
			return true;
		}

		if (empty($this->requests))
		{
			return false;
		}

		foreach ($this->requests as $request)
		{
			if (!$request->isApplied())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function clearErrors(): void
	{
		foreach ($this->requests as $request)
		{
			if (
				$request
				&& $request->getError()
			)
			{
				$request->setDeleted();
				unset($this->requests[$request->getId()]);
			}
		}
	}

	/**
	 * Collect landing entities references to requests
	 * Should be overwritten in child classes
	 * @return array<RequestEntityDto>
	 */
	abstract protected function getEntitiesToRequest(): array;

	/**
	 * Send responses for all data
	 * Should be overwritten in child classes.
	 * @return bool - success flag
	 */
	abstract protected function sendRequests(): bool;

	/**
	 * Set requests result to site data
	 * Should be overwritten in child classes.
	 * @return bool - if change at least one entity
	 */
	abstract protected function applyResponses(): bool;
}