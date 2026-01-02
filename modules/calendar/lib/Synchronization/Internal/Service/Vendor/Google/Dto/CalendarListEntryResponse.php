<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;

class CalendarListEntryResponse
{
	/**
	 * @throws DtoValidationException
	 */
	public function __construct(
		public readonly string $etag,
		public readonly string $id,
		public readonly ?string $summary,
		public readonly ?string $description,
		public readonly ?string $backgroundColor,
		public readonly ?string $accessRole,
		public readonly bool $primary,
		public readonly bool $deleted,
	)
	{
		if (!$this->deleted)
		{
			if (!$this->summary)
			{
				throw new DtoValidationException('The field "summary" is required');
			}

			if (!$this->accessRole)
			{
				throw new DtoValidationException('The field "accessRole" is required');
			}
		}

		if ($this->accessRole && !in_array($this->accessRole, ['freeBusyReader', 'reader', 'writer', 'owner']))
		{
			throw new DtoValidationException('Value "' . $this->accessRole . '" is not a valid access role');
		}
	}
}
