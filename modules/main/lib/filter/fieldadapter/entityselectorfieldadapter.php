<?php
namespace Bitrix\Main\Filter\FieldAdapter;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\UI\EntitySelector\Entity;
use Bitrix\UI\EntitySelector\Item;

class EntitySelectorFieldAdapter
{
	private $isMultiple;
	private $isValueContainEntityId;
	private $dialogOptions = [];

	public function __construct(array $fieldOptions = [])
	{
		$this->isValueContainEntityId = (bool)($fieldOptions['ADD_ENTITY_ID_TO_RESULT'] ?? false);
		$this->isMultiple = (bool)($fieldOptions['MULTIPLE'] ?? false);
		$this->dialogOptions =
			isset($fieldOptions['DIALOG_OPTIONS']) && is_array($fieldOptions['DIALOG_OPTIONS'])
				? $fieldOptions['DIALOG_OPTIONS']
				: []
		;
	}

	/**
	 * Get label (title) of entity selected in filter
	 *
	 * @param string $value  Entity id or JSON with entity type id and entity id
	 * @return string
	 */
	public function getLabel(string $value): string
	{
		if (empty($value))
		{
			return '';
		}
		$result = $this->getLabels([$value]);

		return ($result[0] ?? '');
	}

	/**
	 * Get labels (titles) of entities selected in filter
	 *
	 * @param array $values Array of entity ids or array of JSON with entity type id and entity id
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getLabels(array $values): array
	{
		$result = [];

		$entities = $this->dialogOptions['entities'] ?? [];
		$staticItems = $this->dialogOptions['items'] ?? [];

		$hasEntities = (is_array($entities) && !empty($entities));
		$hasStaticItems = (is_array($staticItems) && !empty($staticItems));

		if (
			(!$hasEntities && !$hasStaticItems)
			|| empty($values)
			|| !Loader::includeModule('ui')
		)
		{
			return $result;
		}

		if ($hasEntities)
		{
			$result = array_merge($result, $this->getLabelsFromEntities($entities, $values));
		}

		if ($hasStaticItems)
		{
			$result = array_merge($result, $this->getLabelsFromStaticItems($staticItems, $values));
		}

		return $result;
	}

	private function getLabelsFromEntities(array $entities, array $values): array
	{
		$result = [];
		foreach ($entities as $entityOptions)
		{
			$entity = Entity::create($entityOptions);
			if ($entity)
			{
				$entityValues = $this->getSupposedEntityValues($entity->getId(), $values);

				$itemTitles = [];
				/** @var $item Item */
				foreach ($entity->getProvider()->getItems($entityValues) as $item)
				{
					$itemTitles[$this->getItemId($item)] = $item->getTitle();
				}
				foreach ($values as $valueId)
				{
					$result['_labels'][] = $itemTitles[$valueId] ?? ('#' . $valueId);
				}

				if (!$this->isValueContainEntityId)
				{
					break;
				}
			}
		}

		return $result;
	}

	private function getLabelsFromStaticItems(array $staticItems, $values)
	{
		$result = [];

		$itemsByEntity = [];
		foreach ($staticItems as $itemOptions)
		{
			$item = new Item($itemOptions);
			$itemEntityId = $item->getEntityId();
			if (!isset($itemsByEntity[$itemEntityId]))
			{
				$itemsByEntity[$itemEntityId] = $this->getSupposedEntityValues($item->getEntityId(), $values);
			}
			if (in_array((string)$item->getId(), $itemsByEntity[$itemEntityId], true))
			{
				$result[] = $item->getTitle();
			}
		}

		return $result;
	}

	private function getSupposedEntityValues(string $entityId, array $values): array
	{
		$result = [];
		foreach ($values as $value)
		{
			if ($this->isValueContainEntityId)
			{
				$entityValue = $this->extractEntityValue((string)$value, $entityId);
				if ($entityValue)
				{
					$result[] = $entityValue;
				}
			}
			else
			{
				$result[] = $value;
			}
		}

		return $result;
	}

	private function extractEntityValue(string $value, string $entityId)
	{
		try
		{
			$parsedValue = Json::decode($value);
			if (
				isset($parsedValue[0])
				&& isset($parsedValue[1])
				&& $parsedValue[0] === $entityId
			)
			{
				return $parsedValue[1];
			}
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
		}

		return null;
	}

	private function getItemId(Item $item): string
	{
		if ($this->isValueContainEntityId)
		{
			return Json::encode([(string)$item->getEntityId(), (string)$item->getId()]);
		}
		else
		{
			return (string)$item->getId();
		}
	}
}