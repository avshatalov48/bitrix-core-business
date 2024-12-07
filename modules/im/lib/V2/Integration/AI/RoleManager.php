<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\Param\Params;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class RoleManager
{
	protected const PROMPT_CATEGORY = 'chat';

	public static function getDefaultRoleCode(): ?string
	{
		if (!Loader::includeModule('ai'))
		{
			return null;
		}

		return \Bitrix\AI\Role\RoleManager::getUniversalRoleCode();
	}

	public function getRoles(array $roleCodes, int $userId): ?array
	{
		if (!Loader::includeModule('imbot') || !Loader::includeModule('ai'))
		{
			return null;
		}

		$roleCodes[] = self::getDefaultRoleCode();
		$roleManager = new \Bitrix\AI\Role\RoleManager($userId, LANGUAGE_ID);

		$roleData = [];
		foreach ($roleManager->getRolesByCode($roleCodes) as $role)
		{
			$roleData[$role['code']] = [
				'code' => $role['code'],
				'name' => $role['name'],
				'desc' => $role['description'],
				'avatar' => $role['avatar'],
				'default' => $role['code'] === \Bitrix\AI\Role\RoleManager::getUniversalRoleCode(),
				'prompts' => $this->getPrompts($roleManager, $role['code']),
			];
		}

		return !empty($roleData) ? $roleData : null;
	}

	protected function getPrompts(\Bitrix\AI\Role\RoleManager $roleManager, string $code): array
	{

		$prompts = $roleManager->getPromptsBy(self::PROMPT_CATEGORY, $code);
		if (empty($prompts))
		{
			$prompts = $roleManager->getPromptsBy(self::PROMPT_CATEGORY, \Bitrix\AI\Role\RoleManager::getUniversalRoleCode());
		}

		return $prompts;
	}

	public function getRolesInChat(int $chatId): array
	{
		if (!Loader::includeModule('ai'))
		{
			return [];
		}

		$params = Params::getInstance($chatId);

		if ($params->get(Params::COPILOT_ROLES) === null)
		{
			return [\Bitrix\AI\Role\RoleManager::getUniversalRoleCode()];
		}

		return $params->get(Params::COPILOT_ROLES)->getValue();
	}

	public function getMainRole(?int $chatId): ?string
	{
		if (!Loader::includeModule('ai'))
		{
			return null;
		}

		if (!isset($chatId))
		{
			return null;
		}

		$params = Params::getInstance($chatId);

		if ($params->get(Params::COPILOT_MAIN_ROLE) === null)
		{
			return \Bitrix\AI\Role\RoleManager::getUniversalRoleCode();
		}

		return $params->get(Params::COPILOT_MAIN_ROLE)->getValue();
	}

	public function updateRole(Chat $chat, int $userId, ?string $roleCode): Result
	{
		$result = new Result();

		if (!Loader::includeModule('ai'))
		{
			$result->addError(new CopilotError(CopilotError::AI_NOT_INSTALLED));

			return $result;
		}

		if ($chat->getType() !== Chat::IM_TYPE_COPILOT)
		{
			$result->addError(new CopilotError(CopilotError::WRONG_CHAT_TYPE));

			return $result;
		}

		$roleManager = new \Bitrix\AI\Role\RoleManager($userId, LANGUAGE_ID);

		if (!isset($roleCode))
		{
			$roleCode = \Bitrix\AI\Role\RoleManager::getUniversalRoleCode();
		}

		$roleData = $this->getRoles([$roleCode], $userId);
		if (empty($roleData))
		{
			$result->addError(new CopilotError(CopilotError::ROLE_NOT_FOUNT));

			return $result;
		}

		$params = Params::getInstance($chat->getChatId());
		if (
			$params->get(Params::COPILOT_MAIN_ROLE) !== null
			&& $params->get(Params::COPILOT_MAIN_ROLE)->getValue() === $roleCode
		)
		{
			$result->addError(new CopilotError(CopilotError::IDENTICAL_ROLE));

			return $result;
		}

		$params->addParamByName(Params::COPILOT_MAIN_ROLE, $roleCode);

		if (!isset($roleData[$roleCode]))
		{
			return $result;
		}
		$this->sendPushCopilotRole($chat, $roleData[$roleCode]);

		if ($chat instanceof Chat\CopilotChat)
		{
			$chat->sendBanner(null, $roleData[$roleCode]['name'], true);
		}

		return $result;
	}

	protected function sendPushCopilotRole(Chat $chat, array $roleData): array
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return [];
		}

		$pushMessage = [
			'module_id' => 'im',
			'command' => 'chatCopilotRoleUpdate',
			'params' => [
				'chatId' => $chat->getChatId(),
				'dialogId' => 'chat' . $chat->getChatId(),
				'copilotRole' => [
					'chats' => [['dialogId' => $chat->getDialogId(), 'role' => $this->getMainRole($chat->getChatId())]],
					'roles' => [$roleData['code'] => $roleData],
				],
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		\Bitrix\Pull\Event::add(array_values($chat->getRelations()->getUserIds()), $pushMessage);

		return $pushMessage;
	}

	public function getRecentKeyRoles(int $userId): array
	{
		$roles = $this->getRecommendedRoles($userId);

		$roleCodes = [];
		foreach ($roles as $role)
		{
			$roleCodes[] = $role['code'];
		}

		return $roleCodes;
	}

	protected function getRecommendedRoles(int $userId): array
	{
		if (!Loader::includeModule('ai'))
		{
			return [];
		}

		$roleManager = new \Bitrix\AI\Role\RoleManager($userId, LANGUAGE_ID);
		$roles = $roleManager->getRecommendedRoles(4);
		array_unshift($roles, $roleManager->getUniversalRole());

		return $roles;
	}
}