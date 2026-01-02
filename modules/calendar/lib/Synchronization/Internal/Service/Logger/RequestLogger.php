<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Logger;

use Bitrix\Main\Config\Option;

class RequestLogger extends DatabaseLogger
{
	private ?int $userId = null;

	private ?string $type = null;

	private mixed $entityId = null;

	public function log($level, string|\Stringable $message, array $context = []): void
	{
		if (Option::get('calendar', 'log_enabled', 'N') !== 'Y')
		{
			return;
		}

		$context = $this->formatContext($context);

		parent::log($level, $message, $context);
	}

	public function setUserId(int $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	public function setEntityId(mixed $entityId): self
	{
		$this->entityId = $entityId;

		return $this;
	}

	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	private function formatContext(array $context): array
	{
		if (!isset($context['userId']) && $this->userId)
		{
			$context['userId'] = $this->userId;
		}

		if (!isset($context['type']) && $this->type)
		{
			$context['type'] = $this->type;
		}

		if (!isset($context['entityId']) && $this->entityId)
		{
			$context['entityId'] = $this->entityId;
		}

		return $context;
	}
}
