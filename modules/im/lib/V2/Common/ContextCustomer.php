<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Im\User;
use Bitrix\Im\V2\Service\Context;

/**
 * Provides context for the action.
 */
trait ContextCustomer
{
	protected ?Context $context = null;

	/**
	 * Provides local context for the action.
	 * @param Context|null $context
	 * @return static
	 */
	public function withContext(?Context $context): self
	{
		$copy = clone $this;

		return $copy->setContext($context);
	}

	/**
	 * Provides local context for the action.
	 * @param int|User $user
	 * @return static
	 */
	public function withContextUser($user): self
	{
		if ($this->context)
		{
			$context = clone $this->context;
		}
		else
		{
			$context = new Context;
		}

		if ($user instanceof User)
		{
			$context->setUser($user);
		}
		elseif (is_numeric($user))
		{
			$context->setUserId((int)$user);
		}

		return $this->withContext($context);
	}

	/**
	 * Sets new context for operations.
	 * @param Context|null $context
	 * @return self
	 */
	public function setContext(?Context $context): self
	{
		$this->context = $context;

		return $this;
	}

	/**
	 * Returns the local or global context for the action.
	 * @return Context
	 */
	public function getContext(): Context
	{
		if ($this->context)
		{
			return $this->context;
		}

		return \Bitrix\Im\V2\Service\Locator::getContext();
	}
}