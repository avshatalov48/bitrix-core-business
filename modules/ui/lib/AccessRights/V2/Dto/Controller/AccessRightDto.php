<?php

namespace Bitrix\UI\AccessRights\V2\Dto\Controller;

final class AccessRightDto
{
	public function __construct(
		public ?string $id,
		public mixed $value,
	)
	{
	}

	/**
	 * @param array $valuesList
	 * @return self[]
	 */
	public static function fromArrayList(array $valuesList): array
	{
		$values = array_map(static fn (array $values) => self::fromArray($values), $valuesList);

		return array_filter($values, static fn (?self $member) => $member !== null);
	}

	public static function fromArray(array $values): ?self
	{
		$id = $values['id'] ?? null;
		$value = $values['value'] ?? null;

		return new self($id, $value);
	}
}
