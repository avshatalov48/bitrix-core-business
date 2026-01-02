<?php

namespace Bitrix\Rest\V3\Interaction\Response;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Rest\V3\Interaction\Relation;
use Bitrix\Rest\V3\Interaction\Request\Request;

abstract class ResponseWithRelations extends Response
{
	protected ?Request $parentRequest = null;

	/**
	 * @var Relation[]
	 */
	protected array $relations = [];

	public function toArray(): array
	{
		$result = parent::toArray();
		foreach ($this->getRelations() as $relation)
		{
			$relationResponse = $relation->getResponse();

			if ($relationResponse === null)
			{
				continue;
			}

			$relationData = $relationResponse->toArray();

			$relationName = $relation->getName();
			$fromField = $relation->getFromField();
			$toField = $relation->getToField();

			$isFromFieldRequested = in_array($fromField, $relation->getRequest()->select->getList(), true);
			$isToFieldRequested = in_array($toField, $relation->getRequest()->select->getList(), true);

			if ($this instanceof ListResponse)
			{
				foreach ($result['items'] as &$item)
				{
					if (isset($item[$fromField]))
					{
						$this->mergeRelationData(
							$item,
							$relationName,
							$relationData,
							$relation->isMultiply(),
							$fromField,
							$toField,
							$item[$fromField],
							$isFromFieldRequested,
							$isToFieldRequested
						);
					}
				}
				unset($item);
			}
			elseif ($this instanceof GetResponse)
			{
				if (isset($result['item'][$fromField]))
				{
					$this->mergeRelationData(
						$result['item'],
						$relationName,
						$relationData,
						$relation->isMultiply(),
						$fromField,
						$toField,
						$result['item'][$fromField],
						$isFromFieldRequested,
						$isToFieldRequested
					);
				}
			}
		}
		return $result;
	}

	public function getParentRequest(): ?Request
	{
		return $this->parentRequest;
	}

	public function setParentRequest(Request $parentRequest): self
	{
		$this->parentRequest = $parentRequest;

		return $this;
	}

	/**
	 * @return Relation[]
	 */
	public function getRelations(): array
	{
		return $this->relations;
	}

	public function setRelations(array $relations): self
	{
		$this->relations = $relations;

		return $this;
	}

	/**
	 * Объединяет данные relation с основными данными
	 */
	private function mergeRelationData(
		array &$data,
		string $relationName,
		array $relationData,
		bool $isMultiply,
		string $fromField,
		string $toField,
		$currentValue,
		bool $isFromFieldRequested,
		bool $isToFieldRequested
	): void {
		$matchedItems = [];

		if (isset($relationData['items']))
		{
			// ListResponse
			foreach ($relationData['items'] as $relationItem)
			{
				if (isset($relationItem[$toField]) && $relationItem[$toField] === $currentValue)
				{
					if (!$isToFieldRequested)
					{
						unset($relationItem[$toField]);
					}
					$matchedItems[] = $relationItem;
				}
			}
		} elseif (isset($relationData['item']))
		{
			// GetResponse
			if (isset($relationData['item'][$toField]) && $relationData['item'][$toField] === $currentValue)
			{
				if (!$isToFieldRequested)
				{
					unset($relationData['item'][$toField]);
				}
				$matchedItems[] = $relationData['item'];
			}
		}

		if (!empty($matchedItems))
		{
			$data[$relationName] = $isMultiply ? $matchedItems : $matchedItems[0];
		}
		else
		{
			$data[$relationName] = $isMultiply ? [] : null;
		}
	}
}
