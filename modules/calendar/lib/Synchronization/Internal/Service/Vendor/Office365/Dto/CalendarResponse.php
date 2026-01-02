<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto;

use Bitrix\Calendar\Sync\Office365\Converter\ColorConverter;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Dto\CalendarResponseInterface;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;

/**
 * @link https://learn.microsoft.com/en-us/graph/api/resources/calendar?view=graph-rest-1.0#properties
 */
class CalendarResponse implements CalendarResponseInterface
{
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly string $changeKey,
		public readonly string $color,
		public readonly ?string $hexColor = null,
		public readonly bool $isDefaultCalendar = false
	)
	{
	}

	public function getLocalColor(): ?string
	{
		return ColorConverter::fromOffice($this->color, $this->hexColor);
	}

	/**
	 * @throws DtoValidationException
	 */
	public static function fromArray(array $data): self
	{
		$required = ['id', 'name', 'changeKey', 'color'];

		foreach ($required as $key)
		{
			if (empty($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in CalendarResponse DTO', $key));
			}
		}

		return new self(
			$data['id'],
			$data['name'],
			$data['changeKey'],
			$data['color'],
			$data['hexColor'] ?? null,
			$data['isDefaultCalendar'] ?? false,
		);
	}
}
