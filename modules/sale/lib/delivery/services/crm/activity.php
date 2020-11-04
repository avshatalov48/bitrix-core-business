<?php

namespace Bitrix\Sale\Delivery\Services\Crm;

/**
 * Class Activity
 * @package Bitrix\Sale\Delivery\Services\Crm
 * @internal
 */
final class Activity
{
	/** @var string|null */
	private $subject;

	/** @var bool */
	private $isHandleable = true;

	/** @var bool */
	private $isCompleted = false;

	/** @var string|null */
	private $status;

	/** @var int|null */
	private $responsibleId;

	/** @var int|null */
	private $priority;

	/** @var int|null */
	private $authorId;

	/** @var array */
	private $bindings = [];

	/** @var array */
	private $fields = [];

	/**
	 * @return string|null
	 */
	public function getSubject(): ?string
	{
		return $this->subject;
	}

	/**
	 * @param string|null $subject
	 * @return Activity
	 */
	public function setSubject(?string $subject): Activity
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isHandleable(): bool
	{
		return $this->isHandleable;
	}

	/**
	 * @param bool $isHandleable
	 * @return Activity
	 */
	public function setIsHandleable(bool $isHandleable): Activity
	{
		$this->isHandleable = $isHandleable;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isCompleted(): bool
	{
		return $this->isCompleted;
	}

	/**
	 * @param bool $isCompleted
	 * @return Activity
	 */
	public function setIsCompleted(bool $isCompleted): Activity
	{
		$this->isCompleted = $isCompleted;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return $this->status;
	}

	/**
	 * @param string|null $status
	 * @return Activity
	 */
	public function setStatus(?string $status): Activity
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getResponsibleId(): ?int
	{
		return $this->responsibleId;
	}

	/**
	 * @param int|null $responsibleId
	 * @return Activity
	 */
	public function setResponsibleId(?int $responsibleId): Activity
	{
		$this->responsibleId = $responsibleId;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getPriority(): ?int
	{
		return $this->priority;
	}

	/**
	 * @param int|null $priority
	 * @return Activity
	 */
	public function setPriority(?int $priority): Activity
	{
		$this->priority = $priority;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getAuthorId(): ?int
	{
		return $this->authorId;
	}

	/**
	 * @param int|null $authorId
	 * @return Activity
	 */
	public function setAuthorId(?int $authorId): Activity
	{
		$this->authorId = $authorId;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getBindings(): array
	{
		return $this->bindings;
	}

	/**
	 * @param array $bindings
	 * @return Activity
	 */
	public function setBindings(array $bindings): Activity
	{
		$this->bindings = $bindings;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param array $fields
	 * @return Activity
	 */
	public function setFields(array $fields): Activity
	{
		$this->fields = $fields;

		return $this;
	}
}
