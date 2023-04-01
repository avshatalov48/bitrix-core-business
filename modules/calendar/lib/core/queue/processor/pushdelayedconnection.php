<?php

namespace Bitrix\Calendar\Core\Queue\Processor;

use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Internals\PushTable;
use Bitrix\Calendar\Sync\Builders\BuilderPushFromDM;
use Bitrix\Calendar\Sync\Managers\PushManager;
use Bitrix\Calendar\Sync;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class PushDelayedConnection implements Interfaces\Processor
{
	/**
	 * @param Interfaces\Message $message
	 *
	 * @return string
	 */
	public function process(Interfaces\Message $message): string
	{
		$data = $message->getBody();

		$connectionId = $data[Sync\Push\Dictionary::PUSH_TYPE['connection']] ?? null;

		if (empty($connectionId))
		{
			return self::REJECT;
		}

		try
		{
			$push = PushTable::getById([
				'ENTITY_TYPE' => PushManager::TYPE_CONNECTION,
				'ENTITY_ID' => $connectionId,
			])->fetchObject();
			if ($push)
			{
				$result = (new PushManager())->handlePush(
					$push->getChannelId(),
					$push->getResourceId()
				);
			}

			return self::ACK;
		}
		catch (\Exception $e)
		{
			return self::REJECT;
		}
	}
}