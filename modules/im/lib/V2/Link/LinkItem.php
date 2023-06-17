<?php

namespace Bitrix\Im\V2\Link;

use Bitrix\Im\V2\Rest\RestEntity;

interface LinkItem extends LinkRestConvertible
{
	/**
	 * Returns the ID of the entity that the link contains.
	 * @return int|null
	 */
	public function getEntityId(): ?int;

	/**
	 * Returns the class name of the entity that the link contains.
	 * @return string|RestEntity
	 */
	public static function getEntityClassName(): string;
}