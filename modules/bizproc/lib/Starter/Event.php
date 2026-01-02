<?php

namespace Bitrix\Bizproc\Starter;

use Bitrix\Bizproc\Automation\Trigger\BaseTrigger;

final class Event
{
	protected ?Document $document = null;
	protected string $trigger;
	protected array $parameters;

	public function __construct(string $trigger, array $parameters = [])
	{
		$this->trigger = $trigger;
		$this->parameters = $parameters;
	}

	public function setDocument(Document $document): self
	{
		$this->document = $document;

		return $this;
	}

	public function getCode(): string
	{
		/** @var BaseTrigger $trigger  */
		$trigger = $this->trigger;

		return $trigger::getCode();
	}

	public function getDocument(): ?Document
	{
		return $this->document;
	}

	public function getTrigger(): string
	{
		return $this->trigger;
	}

	/**
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}
}
