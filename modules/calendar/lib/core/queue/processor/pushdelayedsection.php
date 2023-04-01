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

class PushDelayedSection implements Interfaces\Processor
{
	/**
	 * @param Interfaces\Message $message
	 *
	 * @return string
	 */
	public function process(Interfaces\Message $message): string
	{
		$data = $message->getBody();

		$sectionConnectionId = $data[Sync\Push\Dictionary::PUSH_TYPE['sectionConnection']] ?? null;
		if (empty($sectionConnectionId))
		{
			return self::REJECT;
		}

		try
		{
			$push = PushTable::getById([
				'ENTITY_TYPE' => PushManager::TYPE_SECTION_CONNECTION,
				'ENTITY_ID' => $sectionConnectionId,
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