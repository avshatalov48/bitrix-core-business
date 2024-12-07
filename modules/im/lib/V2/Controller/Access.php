<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;

class Access extends BaseController
{
	/**
	 * @restMethod im.v2.Access.check
	 */
	public function checkAction(?Chat $chat = null, ?Message $message = null): ?array
	{
		return ['result' => true]; // All checks are done in prefilters
	}
}