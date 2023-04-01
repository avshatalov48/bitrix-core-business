<?php

namespace Bitrix\Calendar\Core\Queue\Producer;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\Dictionary;
use Bitrix\Calendar\Core\Queue\Message\MessageMapper;
use Bitrix\Calendar\Core\Queue\Rule\RuleMaster;
use Bitrix\Calendar\Internals\QueueMessageTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

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
	 * @param array $messages
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sendBatch(array $messages): void
	{
		$dateCreate = new DateTime();
		$realMessagesData = [];

		foreach ($messages as $message)
		{
			if ($message instanceof Interfaces\Message)
			{
				$realMessagesData[] = [
					'MESSAGE' => [
						Dictionary::MESSAGE_PARTS['body'] => $message->getBody(),
						Dictionary::MESSAGE_PARTS['headers'] => $message->getHeaders(),
						Dictionary::MESSAGE_PARTS['properties'] => $message->getProperties(),
					],
					'DATE_CREATE' => $dateCreate,
				];
			}
		}

		if (!empty($realMessagesData))
		{
			QueueMessageTable::addMulti($realMessagesData, true);
			$this->throwEventForQueue();
		}
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
	private function throwEventForQueue(Interfaces\Message $message = null): void
	{
		// it's one of ways. We can use agent for delayed start of RuleMaster
		(new RuleMaster())->run();
	}
}