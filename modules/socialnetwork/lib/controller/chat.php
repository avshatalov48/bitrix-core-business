<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Im;
use Bitrix\Main\Loader;

class Chat extends Base
{
	public function createAction(array $userIds): int
	{
		if (!Loader::includeModule('im'))
		{
			return 0;
		}

		$result = Im\V2\Chat\ChatFactory::getInstance()->addChat([
			'TYPE' => Im\V2\Chat::IM_TYPE_CHAT,
			'SKIP_ADD_MESSAGE' => 'Y',
			'AUTHOR_ID' => $this->userId,
			'USERS' => $userIds,
		]);

		return $result->getData()['RESULT']['CHAT_ID'];
	}
}