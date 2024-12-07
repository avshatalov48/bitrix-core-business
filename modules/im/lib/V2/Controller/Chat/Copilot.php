<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Integration\AI\CopilotData;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Main\Engine\CurrentUser;

class Copilot extends BaseController
{
	/**
	 * @restMethod im.v2.Chat.Copilot.updateRole
	 */
	public function updateRoleAction(Chat $chat, CurrentUser $user, ?string $role = null): ?array
	{
		$result = (new RoleManager())->updateRole($chat, $user->getId(), $role);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return [];
	}
}