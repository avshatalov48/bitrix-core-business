<?php

namespace Bitrix\Im\V2\Rest;

interface RestConvertible
{
	/**
	 * Returns the name of the entity that will be used in the rest response
	 * @return string
	 */
	public static function getRestEntityName(): string;

	/**
	 * Returns an array in JSON like format to return the entity as a rest response.
	 * @param array $option
	 * @return array|null
	 */
	public function toRestFormat(array $option = []): ?array;
}