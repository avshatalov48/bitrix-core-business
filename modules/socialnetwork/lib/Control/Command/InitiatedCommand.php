<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Main\Validation\Rule\Min;
use Bitrix\Socialnetwork\Collab\User\User;
use Bitrix\Socialnetwork\Permission\AbstractAccessController;

/**
 * @method int getInitiatorId()
 * @method self setInitiatorId(int $initiatorId)
 * @method bool hasInitiatorId()
 */
abstract class InitiatedCommand extends AbstractCommand
{
	#[Min(0)]
	protected int $initiatorId = 0;

	public function getAccessControllerByInitiator(): ?AbstractAccessController
	{
		return $this->getAccessController($this->initiatorId);
	}

	public function getAccessController(int $userId): ?AbstractAccessController
	{
		$class = $this->getAccessControllerClass();
		if ($class === null)
		{
			return null;
		}

		/** @var AbstractAccessController $class */
		return $class::getInstance($userId);
	}

	public function getInitiator(): User
	{
		return new User($this->initiatorId);
	}
}