<?php

namespace Bitrix\Calendar\Core\Queue\Message;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Internals\EO_QueueMessage;

class BuilderMessageFromDataManager implements Builder
{
	private EO_QueueMessage $data;

	public function __construct(EO_QueueMessage $data)
	{
		$this->data = $data;
	}

	/**
	 * @return mixed
	 */
	public function build(): ?Message
	{
		$message = $this->data->getMessage();
		return (new Message())
			->setId($this->data->getId())
			->setBody($message[Dictionary::MESSAGE_PARTS['body']] ?? [])
			->setHeaders($message[Dictionary::MESSAGE_PARTS['headers']] ?? [])
			->setProperties($message[Dictionary::MESSAGE_PARTS['properties']] ?? [])
		;
	}
}