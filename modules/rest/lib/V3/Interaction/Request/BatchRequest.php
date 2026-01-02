<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Rest\V3\Exceptions\InvalidSelectException;

class BatchRequest
{
	/**
	 * @var BatchRequestItem[]
	 */
	private array $items = [];

	private array $itemsByAlias = [];

	public function __construct(array $data)
	{
		foreach ($data as $item)
		{
			if (!isset($item['method']) || !isset($item['query']))
			{
				throw new InvalidSelectException('Each request item must have a "method" and "query" attribute');
			}

			$batchRequest = new BatchRequestItem($item['method'], $item['query'], $item['as'] ?? null, $item['parallel'] ?? false);
			$this->items[] = $batchRequest;
			if ($batchRequest->getAlias())
			{
				if (isset($this->itemsByAlias[$batchRequest->getAlias()]))
				{
					throw new InvalidSelectException('You are can not have two aliases with same name: ' . $batchRequest->getAlias());
				}
				$this->itemsByAlias[$batchRequest->getAlias()] = $batchRequest;
			}
		}
	}

	public function getItems(): array
	{
		return $this->items;
	}
}