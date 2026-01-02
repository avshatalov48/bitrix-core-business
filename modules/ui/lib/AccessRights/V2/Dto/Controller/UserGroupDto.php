<?php

namespace Bitrix\UI\AccessRights\V2\Dto\Controller;

final class UserGroupDto
{
	public function __construct(
		public ?string $id,
		public ?string $title,
		public ?string $type,
		/** @var AccessCodeDto[] $members */
		public array $accessCodes = [],
		/** @var AccessRightDto[] $accessCodes */
		public array $accessRights = [],
	)
	{
	}

	/**
	 * @param array $valuesList
	 * @return self[]
	 */
	public static function fromArrayList(array $valuesList): array
	{
		$userGroups = array_map(self::fromArray(...), $valuesList);

		return array_filter($userGroups, static fn (mixed $userGroup) => $userGroup !== null);
	}

	public static function fromArray(array $values): ?self
	{
		return new self(
			id: $values['id'] ?? null,
			title: $values['title'] ?? null,
			type:  $values['type'] ?? null,
			accessCodes: AccessCodeDto::fromAccessCodeTypeMap($values['accessCodes'] ?? []),
			accessRights: AccessRightDto::fromArrayList($values['accessRights'] ?? []),
		);
	}
}
