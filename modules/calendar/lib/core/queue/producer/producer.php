<?php

namespace Bitrix\Calendar\Core\Queue\Producer;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\MessageMapper;
use Bitrix\Calendar\Core\Queue\Rule\RuleMaster;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

class Producer implements Interfaces\Producer
{
	private ?MessageMapper $mapper = null;

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws SystemException
	 */
	public function send(Interfaces\Message $message): void
	{
		$this->getMapper()->create($message);
		$this->throwEventForQueue($message);
	}

	/**
	 * @return MessageMapper|null
	 */
	private function getMapper(): ?MessageMapper
	{
		if ($this->mapper === null)
		{
			$this->mapper = new MessageMapper();
		}

		return $this->mapper;
	}

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return void
	 *
	 */
	private function throwEventForQueue(Interfaces\Message $message): void
	{
		// it's one of ways. We can use agent for delayed start of RuleMaster
		(new RuleMaster())->run();
	}
}