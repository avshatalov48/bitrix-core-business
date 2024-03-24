<?php

namespace Bitrix\Calendar\Service\Command;

class Result
{
	private ?string $entityId;
	private array $errors;

	public function __construct(?string $entityId, array $errors = [])
	{
		$this->entityId = $entityId;
		$this->errors = $errors;
	}

	/**
	 * @return string
	 */
	public function getEntityId(): ?string
	{
		return $this->entityId;
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}
}