<?php

namespace Bitrix\Rest\V3\Attributes;

#[\Attribute]
class RelationToMany
{
	/**
	 * @param string $thisField
	 * @param string $refField
	 * @param array{
	 * 		string: array<string, string>
	 * } $sort
	 * @example
	 * $sort = ['order' => ['id' => 'desc']];
	 */
	public function __construct(
		public readonly string $thisField,
		public readonly string $refField,
		public readonly array $sort
	)
	{
	}
}