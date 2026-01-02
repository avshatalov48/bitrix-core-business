<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Connector\AI\Prompt;
use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Request;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;

abstract class RequestSingle extends AIStep
{
	protected ?Request $request = null;

	/**
	 * @inheritdoc
	 */
	public function initialize(): void
	{
		$existsRequests = Request::getByGeneration($this->generation->getId(), $this->stepId);
		if (!empty($existsRequests))
		{
			$this->request = array_shift($existsRequests);
		}
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

		if (!isset($this->siteData, $this->stepId))
		{
			return false;
		}

		if (!isset($this->request))
		{
			$this->request = new Request($this->generation->getId(), $this->stepId);
			$this->request->send($this->getPrompt(), $this->connector);
		}
		elseif ($this->request->getError())
		{
			throw new GenerationException(
				GenerationErrors::notCorrectResponse,
				$this->request->getError()->getMessage(),
			);
		}

		if ($this->request->isReceived())
		{
			$this->verifyResponse();
			if ($this->applyResponse())
			{
				$this->request->setApplied();
			}
			$this->changed = true;
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function isStarted(): bool
	{
		return isset($this->request);
	}

	/**
	 * @inheritdoc
	 */
	public function isFinished(): bool
	{
		return
			isset($this->request)
			&& $this->request->isApplied();
	}

	/**
	 * @inheritdoc
	 */
	public function clearErrors(): void
	{
		if (
			$this->request
			&& $this->request->getError()
		)
		{
			$this->request->setDeleted();
			$this->request = null;
		}
	}

	/**
	 * Create prompt for request by existing data
	 * Should be overwritten in child classes
	 * @return Prompt
	 */
	abstract protected function getPrompt(): Prompt;

	/**
	 * Set request result to data
	 * Should be overwritten in child classes.
	 * @return bool
	 */
	abstract protected function applyResponse(): bool;

	/**
	 * Check request result for completeness
	 * Should be overwritten in child classes.
	 * @return void
	 */
	abstract public function verifyResponse(): void;

	/**
	 * @inheritdoc
	 */
	abstract public static function getRequestQuota(Site $siteData): ?RequestQuotaDto;
}