<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\V2\Message\CounterService;

class OpenLineCounter
{
	public static function removeInvalidCounterAgent()
	{
		global $DB;

		$DB->Query("
			DELETE bimu FROM b_im_message_unread bimu 
			WHERE NOT EXISTS(SELECT 1 FROM b_im_relation bir WHERE bir.CHAT_ID=bimu.CHAT_ID AND bir.USER_ID=bimu.USER_ID)
			", true);

		CounterService::clearCache();

		return '';
	}
}