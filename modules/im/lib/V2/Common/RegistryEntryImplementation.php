<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\ActiveRecord;

/**
 * Implementation of the interface @see \Bitrix\Im\V2\RegistryEntry
 */
trait RegistryEntryImplementation
{
	protected ?Registry $registry = null;

	/**
	 * @param Registry $registry
	 * @return self
	 */
	public function setRegistry(Registry $registry): self
	{
		$this->registry = $registry;

		if (
			$this instanceof ActiveRecord
			&& $this->getPrimaryId()
		)
		{
			$this->registry[$this->getPrimaryId()] = $this;
		}

		return $this;
	}

	/**
	 * Return link of the object's registry.
	 * @return Registry|null
	 */
	public function getRegistry(): ?Registry
	{
		return $this->registry;
	}
}