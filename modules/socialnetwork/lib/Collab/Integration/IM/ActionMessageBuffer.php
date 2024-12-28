<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM;

use Bitrix\Socialnetwork\Helper\InstanceTrait;

class ActionMessageBuffer
{
	use InstanceTrait;

	protected static array $stack = [];

	public function put(ActionType $action, $collabId, $senderId, array $recipients = [], array $parameters = []): static
	{
		$key = "{$action->value}:{$collabId}:{$senderId}";
		if (!isset(static::$stack[$key]))
		{
			static::$stack[$key] = [
				'recipients' => [],
				'parameters' => [],
			];
		}

		static::$stack[$key]['recipients'] = array_merge(static::$stack[$key]['recipients'], $recipients);
		static::$stack[$key]['parameters'] = array_merge(static::$stack[$key]['parameters'], $parameters);

		return $this;
	}

	public function flush(): void
	{
		$factory = ActionMessageFactory::getInstance();

		foreach (static::$stack as $key => $data)
		{
			[$action, $collabId, $senderId] = explode(':', $key);

			$recipients = $data['recipients'];
			$parameters = $data['parameters'];

			$factory
				->getActionMessage(ActionType::from($action), (int)$collabId, (int)$senderId)
				->runAction($recipients, $parameters);
		}

		static::$stack = [];
	}
}