<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;

class CalendarListEntryResponse
{
	/**
	 * @throws DtoValidationException
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $etag,
		public readonly string $name,
		public readonly ?string $description,
		public readonly ?string $type,
		public readonly ?string $backgroundColor,
	)
	{
		if ($this->type && $this->type !== 'VEVENT')
		{
			throw new DtoValidationException('Value "' . $this->type . '" is not a valid type.');
		}
	}
}
