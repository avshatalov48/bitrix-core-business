<?php

namespace Bitrix\Rest\V3\Interaction\Response;

class BatchResponse extends Response
{
	/**
	 * @var Response[]
	 */
	private array $items = [];

	private array $context = [];

	public function addItem(int|string $alias, Response $item): void
	{
		$responseData = $item->toArray();
		if ($responseData['item'])
		{
			$this->context[$alias] = $responseData['item'];
		}
		else if ($responseData['items'])
		{
			$this->context[$alias] = $responseData['items'];
		}
		$this->items[] = $item;
	}

	public function toArray(): array
	{
		$result = [];
		foreach ($this->items as $item)
		{
			$result[] = $item->toArray();
		}
		return $result;
	}

	public function getContext(int|string|null $alias = null): array
	{
		return ($alias !== null && isset($this->context[$alias])) ? $this->context[$alias] : $this->context;
	}
}