<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;

class CalendarListResponse
{
	/**
	 * @var CalendarResponse[]
	 */
	private array $items = [];

	public function __construct(array $items)
	{
		foreach ($items as $item)
		{
			if (isset($item['canShare']) && $item['canShare'])
			{
				$this->items[] = new CalendarResponse(
					$item['id'],
					$item['name'],
					$item['changeKey'],
					$item['color'],
					$item['hexColor'] ?? null,
					$item['isDefaultCalendar'] ?? false
				);
			}
		}
	}

	/**
	 * @return CalendarResponse[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @throws DtoValidationException
	 */
	public static function fromArray(array $responseData): self
	{
		$required = ['value'];

		foreach ($required as $key)
		{
			if (!isset($responseData[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in CalendarListResponse DTO', $key));
			}
		}

		return new self($responseData['value']);
	}
}
