<?php

namespace Bitrix\Calendar\Core\Queue\Examples;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIMNotify;
use Bitrix\Calendar\Core\Queue;

class ProcessorExample implements Queue\Interfaces\Processor
{

	/**
	 * @throws LoaderException
	 */
	public function __construct()
	{
		Loader::includeModule('im');
	}

    /**
     * @inheritDoc
     */
    public function process(Queue\Interfaces\Message $message): string
    {
		$body = $message->getBody();
		if ($userId = $body['userId'])
		{
			CIMNotify::Add([
				'TO_USER_ID' => $userId,
				'FROM_USER_ID' => $userId,
				'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
				'NOTIFY_MODULE' => 'calendar',
				'NOTIFY_TAG' => 'CALENDAR|QUEUE|EXAMPLE|'.$userId,
				'NOTIFY_SUB_TAG' => 'CALENDAR|QUEUE|EXAMPLE|'.$userId,
				'NOTIFY_MESSAGE' => $body['content'],
			]);
			return self::ACK;
		}
		else
		{
			return self::REJECT;
		}
    }
}