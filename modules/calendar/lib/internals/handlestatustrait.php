<?php

namespace Bitrix\Calendar\Internals;

trait HandleStatusTrait
{
	/** @var callable[] $statusHandlers*/
	protected array $statusHandlers = [];

	/**
	 * @param callable $handler
	 *
	 * @return $this
	 */
	public function addStatusHandler(callable $handler): self
	{
		$this->statusHandlers[] = $handler;

		return $this;
	}

	/**
	 * @param callable[] $handlers
	 *
	 * @return $this
	 */
	public function addStatusHandlerList(array $handlers): self
	{
		foreach ($handlers as $handler)
		{
			$this->statusHandlers[] = $handler;
		}

		return $this;
	}

	/**
	 * @return callable[]
	 */
	public function getStatusHandlerList(): array
	{
		return $this->statusHandlers;
	}

	/**
	 * @param $status
	 *
	 * @return void
	 */
	protected function sendStatus($status)
	{
		foreach ($this->statusHandlers as $statusHandler)
		{
			call_user_func($statusHandler, $status);
		}
	}
	//
}
