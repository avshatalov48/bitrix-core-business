<?php

namespace Bitrix\Calendar\Core\Queue\Message;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Queue\Queue\QueueFactory;
use Bitrix\Calendar\Internals\EO_QueueHandledMessage;
use Bitrix\Main\ObjectException;

class BuilderHandledMessageFromDataManager implements Builder
{
	private EO_QueueHandledMessage $data;

	public function __construct(EO_QueueHandledMessage $data)
	{
		$this->data = $data;
	}

	/**
	 * @return mixed
	 *
	 * @throws ObjectException
	 */
	public function build(): ?HandledMessage
	{
		$message = (new BuilderMessageFromDataManager($this->data->getMessage()))->build();
		$queue = (new QueueFactory())->getById($this->data->getQueueId());
		return (new HandledMessage())
			->setId($this->data->getId())
			->setMessage($message)
			->setHash($this->data->getHash())
			->setQueue($queue)
			->setDateCreate(new Date($this->data->getDateCreate())) // TODO: check date is seted
		;
	}
}