<?php

namespace Bitrix\Im\V2\Chat\Copilot;

use Bitrix\Im\V2\Integration\AI\AIHelper;
use Bitrix\Im\V2\Integration\AI\CopilotData;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Im\V2\Rest\PopupDataItem;
use Bitrix\Main\Engine\CurrentUser;

class CopilotPopupItem implements PopupDataItem
{
	public const ENTITIES = [
		'chat' => 'chat',
		'messageCollection' => 'messageCollection',
	];

	/**
	 * @var string[]
	 */
	private array $roleCodes;
	private array $messages = [];
	private array $chats = [];

	public function __construct(array $copilotRoles, string $entity)
	{
			$this->roleCodes = array_unique($copilotRoles);

			if ($entity === self::ENTITIES['chat'])
			{
				$this->chats = $copilotRoles;
			}

			if ($entity === self::ENTITIES['messageCollection'])
			{
				$this->messages = $copilotRoles;
			}
	}

	public function merge(PopupDataItem $item): self
	{
		if ($item instanceof self)
		{
			$this->roleCodes = array_unique(array_merge($this->roleCodes, $item->roleCodes));

			$this->messages += $item->messages;
		}

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'copilot';
	}

	public function toRestFormat(array $option = []): ?array
	{
		if (empty($this->roleCodes))
		{
			return null;
		}

		return [
			'chats' => !empty($this->chats) ? self::convertArrayData($this->chats, self::ENTITIES['chat']) : null,
			'messages' => !empty($this->messages)
				? self::convertArrayData($this->messages, self::ENTITIES['messageCollection'])
				: null,
			'aiProvider' => AIHelper::getProviderName(),
			'roles' => (new RoleManager())->getRoles($this->roleCodes, CurrentUser::get()->getId()),
		];
	}

	public static function convertArrayData(array $data, string $entity): array
	{
		$result = [];

		foreach ($data as $id => $item)
		{
			if ($entity === self::ENTITIES['chat'])
			{
				$result[] = ['dialogId' => $id, 'role' => $item];
			}
			elseif ($entity === self::ENTITIES['messageCollection'])
			{
				$result[] = ['id' => $id, 'role' => $item];
			}
		}

		return $result;
	}
}