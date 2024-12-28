<?php

namespace Bitrix\Socialnetwork\Collab\Controller\Trait;

use Bitrix\Main\HttpRequest;
use Bitrix\Socialnetwork\Collab\Integration\IM\Chat;

trait GetCollabIdBySourceTrait
{
	public function resolveCollabId(HttpRequest $request, string $collabIdKey = 'collabId'): int
	{
		$collabId = (int)$request->get($collabIdKey);
		if ($collabId > 0)
		{
			return $collabId;
		}

		if ($collabIdKey !== 'collabId')
		{
			$collabId = (int)$request->get('collabId');
			if ($collabId > 0)
			{
				return $collabId;
			}
		}

		$dialogId = (string)$request->get('dialogId');
		if (!empty($dialogId))
		{
			return Chat::getCollabIdByDialog($dialogId);
		}

		return 0;
	}
}