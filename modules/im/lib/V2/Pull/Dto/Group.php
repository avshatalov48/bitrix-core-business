<?php

namespace Bitrix\Im\V2\Pull\Dto;

class Group
{
	/**
	 * @var int[]
	 */
	private array $recipients;
	private array $params;

	/**
	 * @param int[] $recipients
	 * @param array $params
	 */
	public function __construct(array $recipients = [], array $params = [])
	{
		$this->recipients = $recipients;
		$this->params = $params;
	}

	public function addUser(int $userId): void
	{
		$this->recipients[] = $userId;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @return int[]
	 */
	public function getRecipients(): array
	{
		return $this->recipients;
	}
}
