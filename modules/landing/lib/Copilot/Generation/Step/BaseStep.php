<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Metrika;

abstract class BaseStep implements IStep
{
	protected Generation $generation;
	protected Data\Site $siteData;
	protected ?int $stepId;

	protected bool $initied = false;
	protected bool $changed = false;

	public function __construct()
	{
	}

	/**
	 * @inheritdoc
	 */
	public function init(Generation $generation, int $stepId): static
	{
		if ($this->initied)
		{
			return $this;
		}

		if (!Generation::checkExists($generation->getId()))
		{
			throw new GenerationException(
				GenerationErrors::dataValidation,
				"Generation {$generation->getId()} does not exist",
			);
		}

		$this->generation = $generation;
		$this->siteData = $generation->getSiteData();
		$this->stepId = $stepId;
		$this->initied = true;

		$this->initialize();

		return $this;
	}

	/**
	 * Internal actions for init current step. It will be executed only once.
	 * @return void
	 */
	abstract protected function initialize(): void;

	/**
	 * @inheritdoc
	 */
	public function execute(): bool
	{
		if (!isset($this->generation, $this->siteData))
		{
			return false;
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function isAsync(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function isChanged(): bool
	{
		return $this->changed;
	}

	/**
	 * @inheritdoc
	 */
	public function clearErrors(): void
	{}

	/**
	 * Get object for send front and backend events
	 * @return Generation\Event
	 */
	protected function getEvent(): Generation\Event
	{
		return $this->generation->getEvent();
	}

	/**
	 * Step may, or not may send analytic event.
	 * The Scenarist decides when to send the event.
	 * @return Metrika\Events|null
	 */
	public function getAnalyticEvent(): ?Metrika\Events
	{
		return null;
	}
}