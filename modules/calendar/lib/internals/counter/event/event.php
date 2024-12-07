<?php

namespace Bitrix\Calendar\Internals\Counter\Event;

class Event
{
	private int $id = 0;
	private string $hitId;
	private string $type;
	private array $data = [];

	/**
	 * CounterEvent constructor.
	 * @param string $type
	 * @param array $data
	 */
	public function __construct(string $hitId, string $type)
	{
		$this->hitId = $hitId;
		$this->type = $type;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data): self
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getHitId(): string
	{
		return $this->hitId;
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}
}