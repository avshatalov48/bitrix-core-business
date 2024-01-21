<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2\Chat\NotifyChat;

class Notify extends BaseController
{
	/**
	 * @restMethod im.v2.Notify.deleteAll
	 */
	public function deleteAllAction(): ?array
	{
		$notifyChat = NotifyChat::getByUser();

		if ($notifyChat !== null)
		{
			$notifyChat->dropAll();
		}

		return ['result' => true];
	}
}