<?php

namespace Bitrix\Im\V2\Service;

use Bitrix\Im\V2\Entity\User\User;

class Context
{
	protected ?int $actionContextUserId = null;

	/**
	 * Provides the user context for the action.
	 * If it is null the current user will be used.
	 * @param int|User|null $contextUser
	 */
	public function setUser($contextUser): self
	{
		if (is_numeric($contextUser))
		{
			$this->setUserId((int)$contextUser);
		}
		elseif ($contextUser instanceof User)
		{
			$this->setUserId($contextUser->getId());
		}
		elseif ($contextUser === null)
		{
			$this->resetUser();
		}

		return $this;
	}

	/**
	 * Provides the user Id for the action.
	 * If it is null the current user will be used.
	 *
	 * @param int|null $contextUserId
	 * @return Context
	 */
	public function setUserId(?int $contextUserId): self
	{
		if (is_numeric($contextUserId))
		{
			$this->actionContextUserId = (int)$contextUserId;
		}
		elseif ($contextUserId === null)
		{
			$this->resetUser();
		}

		return $this;
	}

	/**
	 * Resets current context state.
	 * @return self
	 */
	public function resetUser(): self
	{
		$this->actionContextUserId = null;

		return $this;
	}

	/**
	 * Returns current user Id.
	 * @return int
	 */
	public function getUserId(): int
	{
		global $USER;
		if (
			$this->actionContextUserId === null
			&& ($USER instanceof \CUser)
		)
		{
			return (int)$USER->getId();
		}

		return (int)$this->actionContextUserId;
	}

	/**
	 * Returns current user.
	 * @return User
	 */
	public function getUser(): User
	{
		return User::getInstance($this->getUserId());
	}
}