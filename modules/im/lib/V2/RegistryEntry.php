<?php

namespace Bitrix\Im\V2;

interface RegistryEntry
{
	/**
	 * Provides the link to the registry object.
	 * @param Registry $registry
	 * @return self
	 */
	public function setRegistry(Registry $registry): self;

	/**
	 * Return link of the object's registry.
	 * @return Registry|null
	 */
	public function getRegistry(): ?Registry;
}