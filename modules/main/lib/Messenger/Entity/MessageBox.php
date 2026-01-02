<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Messenger\Internals\Config\QueueConfigRegistry;
use Bitrix\Main\Type\DateTime;

/**
 * @internal
 */
final class MessageBox
{
	private ?int $id = null;

	private string $queueId;

	private int|string|null $itemId = null;

	private string $className;

	private DateTime $createdAt;

	private DateTime $availableAt;

	private int $ttl = 3;

	private bool $rejected = false;

	public function __construct(private readonly MessageInterface $message)
	{
		$this->className = get_class($this->message);
		$this->createdAt = new DateTime();
		$this->availableAt = new DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getQueueId(): string
	{
		return $this->queueId;
	}

	public function setQueueId(string $queueId): self
	{
		$this->queueId = $queueId;

		return $this;
	}

	public function getMessage(): MessageInterface
	{
		return $this->message;
	}

	public function getCreatedAt(): DateTime
	{
		return $this->createdAt;
	}

	public function setCreatedAt(DateTime $createdAt): self
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getItemId(): int|string|null
	{
		return $this->itemId;
	}

	public function setItemId(int|string|null $itemId): self
	{
		$this->itemId = $itemId;

		return $this;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

	public function getAvailableAt(): DateTime
	{
		return $this->availableAt;
	}

	public function setAvailableAt(DateTime $availableAt): self
	{
		$this->availableAt = $availableAt;

		return $this;
	}

	public function getTtl(): int
	{
		return $this->ttl;
	}

	/**
	 * @param int $ttl Not negative number
	 *
	 * @throws ArgumentException
	 */
	public function setTtl(int $ttl): self
	{
		if ($ttl < 0)
		{
			throw new ArgumentException('Ttl value should be a not negative number');
		}

		$this->ttl = $ttl;

		return $this;
	}

	public function isRejected(): bool
	{
		return $this->rejected;
	}

	public function isDied(): bool
	{
		return $this->ttl < 1;
	}

	public function reject(): void
	{
		$this->rejected = true;

		$this->ttl--;

		if ($this->isDied())
		{
			return;
		}

		if ($this->availableAt > new DateTime())
		{
			return;
		}

		$retryStrategy = ServiceLocator::getInstance()
			->get(QueueConfigRegistry::class)
			->getQueueConfig($this->queueId)
			->retryStrategy
		;

		$retry = $retryStrategy->getMaxRetryCount() - $this->ttl;

		$availableAt = new DateTime();

		$this->availableAt = $availableAt->add(
			sprintf(
				'+%s seconds',
				intval($retryStrategy->getWaitingTime($retry) / 1000)
			)
		);
	}

	public function kill(): self
	{
		$this->ttl = 0;

		$this->rejected = true;

		return $this;
	}

	public function requeue(?int $retryDelay): self
	{
		$this->ttl++;

		if ($retryDelay > 0)
		{
			$availableAt = new DateTime();

			$this->availableAt = $availableAt->add(sprintf('+%s seconds', $retryDelay));
		}

		return $this;
	}
}
