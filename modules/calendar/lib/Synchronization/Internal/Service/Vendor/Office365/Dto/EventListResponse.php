<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto;

use Bitrix\Calendar\Sync\Office365\Dto\DateTimeDto;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;

/**
 * @link https://learn.microsoft.com/en-us/graph/api/event-list-instances?view=graph-rest-1.0&tabs=http
 */
class EventListResponse
{
	private ?string $syncToken;

	private ?string $pageToken;

	private array $items;

	public function __construct(?string $deltaLink, ?string $nextLink, array $items)
	{
		$this->syncToken = $this->extractSyncToken($deltaLink);
		$this->pageToken = $this->extractPageToken($nextLink);

		$events = [];

		foreach ($items as $item)
		{
			if (!empty($item['@removed']))
			{
				$events[$item['id']][Helper::EVENT_TYPES['deleted']] = new EventDto($item);
			}
			elseif ($item['type'] === Helper::EVENT_TYPES['single'])
			{
				$events[$item['id']][$item['type']] = new EventDto($item);
			}
			elseif ($item['type'] === Helper::EVENT_TYPES['series'])
			{
				$events[$item['id']][$item['type']] = new EventDto($item);
			}
			elseif ($item['type'] === Helper::EVENT_TYPES['exception'])
			{
				$events[$item['seriesMasterId']][Helper::EVENT_TYPES['exception']][$item['id']] = new EventDto($item);
				$events[$item['seriesMasterId']][Helper::EVENT_TYPES['occurrence']][] = new DateTimeDto($item['start']);
			}
			elseif ($item['type'] === Helper::EVENT_TYPES['occurrence'])
			{
				$events[$item['seriesMasterId']][Helper::EVENT_TYPES['occurrence']][] = new DateTimeDto($item['start']);
			}
		}

		$this->items = $events;
	}

	/**
	 * @throws DtoValidationException
	 */
	public static function fromArray(array $data): self
	{
		$required = ['@odata.context'];

		foreach ($required as $key)
		{
			if (!isset($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in EventListResponse DTO', $key));
			}
		}

		return new self(
			$data['@odata.deltaLink'] ?? null,
			$data['@odata.nextLink'] ?? null,
			$data['value'] ?? [],
		);
	}

	private function extractPageToken(?string $uri): ?string
	{
		return !empty($uri) ? $this->getUriParam($uri, '$skiptoken') : null;
	}

	private function extractSyncToken(?string $uri): ?string
	{
		return !empty($uri) ? $this->getUriParam($uri, '$deltatoken') : null;
	}

	private function getUriParam(string $uri, string $name): ?string
	{
		if ($urlData = parse_url($uri))
		{
			parse_str($urlData['query'], $params);

			return $params[$name] ?? null;
		}

		return null;
	}

	public function getSyncToken(): ?string
	{
		return $this->syncToken;
	}

	public function getPageToken(): ?string
	{
		return $this->pageToken;
	}

	public function getItems(): array
	{
		return $this->items;
	}
}
