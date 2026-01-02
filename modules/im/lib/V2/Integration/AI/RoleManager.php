<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\Im\V2\Analytics\CopilotAnalytics;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\Param\Params;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;

class RoleManager
{
	use ContextCustomer;

	protected const PROMPT_CATEGORY = 'chat';

	protected static array $roles = [];
	protected static array $prompts = [];

	protected ?\Bitrix\AI\Role\RoleManager $aiManager = null;

	public static function getDefaultRoleCode(): ?string
	{
		if (!Loader::includeModule('ai'))
		{
			return null;
		}

		return \Bitrix\AI\Role\RoleManager::getUniversalRoleCode();
	}

	public function getRolesShort(array $roleCodes): ?array
	{
		if (!Loader::includeModule('imbot') || !Loader::includeModule('ai'))
		{
			return null;
		}

		$roleCodes[] = self::getDefaultRoleCode();
		$roleCodes = array_unique($roleCodes);

		$roleData = [];
		foreach ($this->getAiManager()->getRolesAvatarsFromCache($roleCodes) as $roleCode => $avatar)
		{
			$roleData[$roleCode] = $this->formatRoleDataShort([
				'code' => $roleCode,
				'avatar' => $avatar,
			]);
		}

		return !empty($roleData) ? $roleData : null;
	}

	public function getRoles(array $roleCodes, bool $withPrompts = true): ?array
	{
		if (!Loader::includeModule('imbot') || !Loader::includeModule('ai'))
		{
			return null;
		}

		$roleCodes[] = self::getDefaultRoleCode();
		$roleCodes = array_unique($roleCodes);
		$this->fillRoles($roleCodes);

		if ($withPrompts)
		{
			$this->fillPrompts($roleCodes);
		}

		$roleData = [];
		foreach ($roleCodes as $code)
		{
			$role = self::$roles[$code] ?? null;
			if (isset($role))
			{
				if ($withPrompts)
				{
					$role['prompts'] = self::$prompts[$code] ?? [];
				}
				$roleData[$code] = $role;
			}
		}

		return !empty($roleData) ? $roleData : null;
	}

	protected function fillRoles(array $roleCodes): void
	{
		$roleManager = $this->getAiManager();

		$codesWithoutCache = array_diff($roleCodes, array_keys(self::$roles));
		if (empty($codesWithoutCache))
		{
			return;
		}

		foreach ($roleManager->getRolesByCode($codesWithoutCache) as $role)
		{
			self::$roles[$role['code']] = $this->formatRoleData($role);
		}
	}

	protected function fillPrompts(array $roleCodes): void
	{
		$roleManager = $this->getAiManager();
		$rolesWithoutPrompts = array_diff($roleCodes, array_keys(self::$prompts));

		if (empty($rolesWithoutPrompts))
		{
			return;
		}

		$prompts = $roleManager->getPromptsByCategoryAndRoleCodes(self::PROMPT_CATEGORY, $rolesWithoutPrompts);
		foreach ($rolesWithoutPrompts as $code)
		{
			self::$prompts[$code] =
				$prompts[$code]
				?? $prompts[self::getDefaultRoleCode()]
				?? self::$prompts[self::getDefaultRoleCode()]
				?? []
			;
		}
	}

	protected function formatRoleData(array $role): array
	{
		return [
			'code' => $role['code'],
			'name' => $role['name'],
			'desc' => $role['description'],
			'avatar' => $role['avatar'],
			'default' => $role['code'] === self::getDefaultRoleCode(),
			'prompts' => [],
		];
	}

	protected function formatRoleDataShort(array $role): array
	{
		return [
			'code' => $role['code'],
			'avatar' => $role['avatar'],
			'default' => $role['code'] === self::getDefaultRoleCode(),
		];
	}

	public function getMainRole(?int $chatId): ?string
	{
		if (!isset($chatId) || !Loader::includeModule('ai'))
		{
			return null;
		}

		$params = Params::getInstance($chatId);

		if ($params->get(Params::COPILOT_MAIN_ROLE) === null)
		{
			return self::getDefaultRoleCode();
		}

		return (string)$params->get(Params::COPILOT_MAIN_ROLE)->getValue();
	}

	public function updateRole(Chat $chat, ?string $roleCode): Result
	{
		$result = new Result();

		if (!Loader::includeModule('ai'))
		{
			$result->addError(new CopilotError(CopilotError::AI_NOT_INSTALLED));

			return $result;
		}

		if (!isset($roleCode))
		{
			$roleCode = self::getDefaultRoleCode();
		}

		$roleData = $this->getRoles([$roleCode]);
		if (empty($roleData))
		{
			$result->addError(new CopilotError(CopilotError::ROLE_NOT_FOUND));

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

		$oldRole = $params->get(Params::COPILOT_MAIN_ROLE)?->getValue() ?? self::getDefaultRoleCode();
		$params->addParamByName(Params::COPILOT_MAIN_ROLE, $roleCode);
		(new CopilotAnalytics($chat))->addChangeRole((string)$oldRole);

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

	public function getRecentKeyRoles(): array
	{
		$roles = $this->getRecommendedRoles();

		$roleCodes = [];
		foreach ($roles as $role)
		{
			$roleCodes[] = $role['code'];
		}

		return $roleCodes;
	}

	protected function getRecommendedRoles(): array
	{
		if (!Loader::includeModule('ai'))
		{
			return [];
		}

		$roleManager = $this->getAiManager();
		$roles = $roleManager->getRecommendedRoles(4);
		array_unshift($roles, $roleManager->getUniversalRole());

		return $roles;
	}

	protected function getAiManager(): \Bitrix\AI\Role\RoleManager
	{
		$this->aiManager ??= new \Bitrix\AI\Role\RoleManager($this->getContext()->getUserId(), LANGUAGE_ID);

		return $this->aiManager;
	}
}
