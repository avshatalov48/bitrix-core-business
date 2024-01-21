<?php

namespace Bitrix\Im\V2\Message\Attach;

use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Main\Engine\CurrentUser;

class AttachService
{
	public function deleteRichUrl(Message $message): void
	{
		$params = $message->getParams();

		$urlIds = $params->get('URL_ID')->getValue();
		if (empty($urlIds))
		{
			return;
		}

		$params->get('URL_ID')->unsetValue();
		$params->get('URL_ONLY')->unsetValue();
		$params->save();

		(new Message\Param\PushService())->sendPull($message, ['URL_ID', 'ATTACH', 'URL_ONLY']);
	}
}