<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\ChatTypeFilter;
use Bitrix\Im\V2\Integration\AI\EngineManager;
use Bitrix\Im\V2\Integration\AI\RoleManager;

class Copilot extends BaseController
{
	protected function getDefaultPreFilters(): array
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ChatTypeFilter([Chat\CopilotChat::class]),
			]
		);
	}

	/**
	 * @restMethod im.v2.Chat.Copilot.updateRole
	 */
	public function updateRoleAction(Chat $chat, ?string $role = null): ?array
	{
		$result = (new RoleManager())->updateRole($chat, $role);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.Copilot.updateEngine
	 */
	public function updateEngineAction(Chat $chat, string $engineCode): ?array
	{
		$result = (new EngineManager())->updateEngine($chat, $engineCode);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return ['result' => true];
	}
}
