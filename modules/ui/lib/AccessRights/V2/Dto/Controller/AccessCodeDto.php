<?php

namespace Bitrix\UI\AccessRights\V2\Dto\Controller;

final class AccessCodeDto
{
	public function __construct(
		public ?string $accessCode,
		public ?string $type,
	)
	{
	}

	/**
	 * @param array $valuesList
	 * @return self[]
	 */
	public static function fromAccessCodeTypeMap(array $valuesList): array
	{
		$accessCodes = [];
		foreach ($valuesList as $accessCode => $type)
		{
			$accessCodes[] = new self($accessCode, $type);
		}

		return $accessCodes;
	}
}
