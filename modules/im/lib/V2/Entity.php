<?php

namespace Bitrix\Im\V2;

interface Entity
{
	/**
	 * Returns the id of the entity
	 * @return int|null
	 */
	public function getId(): ?int;
}