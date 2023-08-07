<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\MessageService\Internal\Entity\RestrictionTable;
use Bitrix\MessageService\Message;

abstract class Base
{
	abstract public function getEntityId(): string;
	abstract protected function getOptionLimitName(): string;
	abstract protected function getEntity(): string;
	abstract protected function getDefaultLimit(): int;

	protected Message $message;
	protected int $counter;
	protected int $limit = 0;
	/** @var string[] */
	protected array $additionalParams = [];


	public function __construct(Message $message)
	{
		$this->message = $message;
		$this->limit = $this->initLimit();
	}

	public function setCounter(int $counter): Base
	{
		$this->counter = $counter;

		return $this;
	}

	/**
	 * @param string[] $additionalParams
	 * @return $this
	 */
	public function setAdditionalParams(array $additionalParams): Base
	{
		$this->additionalParams = $additionalParams;

		return $this;
	}

	public function canUse(): bool
	{
		return $this->limit > 0;
	}

	public function lock(): void
	{
		Application::getConnection()->lock($this->getEntityId(), 60);
	}

	public function unlock(): void
	{
		Application::getConnection()->unlock($this->getEntityId());
	}

	public function isCanSend(): bool
	{
		if (isset($this->counter))
		{
			return $this->counter < $this->limit;
		}

		return true;
	}

	public function increase(): bool
	{
		if (isset($this->counter))
		{
			return $this->updateCounter();
		}

		$this->insertCounter();

		return true;
	}

	protected function updateCounter(): bool
	{
		return RestrictionTable::updateCounter($this->getEntityId(), $this->limit);
	}

	protected function insertCounter(): void
	{
		RestrictionTable::insertCounter($this->getEntityId());
	}

	private function initLimit(): int
	{
		return (int)Option::get('messageservice', $this->getOptionLimitName(), $this->getDefaultLimit());
	}

	public function log()
	{
		if (Option::get('messageservice', 'event_log_message_send', 'N') === 'Y')
		{
			$restrictionType = mb_strtoupper($this->getOptionLimitName());
			$userId = CurrentUser::get()->getId() ?: $this->message->getAuthorId();
			$phone = $this->message->getTo();

			$description = "Restriction: $restrictionType. Phone: $phone. CurrentCounter: $this->counter. Limit: $this->limit.";

			\CEventLog::Log(
				'INFO',
				'MESSAGE_BLOCK',
				'messageservice',
				$userId,
				$description
			);
		}
	}

}