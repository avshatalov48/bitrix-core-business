<?php
namespace Bitrix\Main\Copy;

use Bitrix\Main\Result;
use Bitrix\Main\Type\Dictionary;

class EntityCopier implements Copyable
{
	protected $implementer;
	protected $implementerName;

	/**
	 * @var Result
	 */
	protected $result;

	/**
	 * @var Copyable[]
	 */
	private $entitiesToCopy = [];

	public function __construct(CopyImplementer $implementer)
	{
		$this->implementer = $implementer;
		$this->implementerName = get_class($this->implementer);

		$this->result = new Result();
	}

	/**
	 * Adding entities to be copied later by the parent.
	 *
	 * @param Copyable $entity
	 */
	public function addEntityToCopy(Copyable $entity)
	{
		$this->entitiesToCopy[] = $entity;
	}

	/**
	 * Starts copying added entities.
	 *
	 * @param ContainerCollection $containerManager
	 */
	protected function startCopyEntities(ContainerCollection $containerManager)
	{
		$results = [];

		foreach ($this->entitiesToCopy as $entity)
		{
			$results[] = $entity->copy($containerManager);
		}

		$data = [];
		foreach ($results as $result)
		{
			$data = $data + $result->getData();
			if ($result->getErrors())
			{
				$this->result->addErrors($result->getErrors());
			}
		}
		if ($data)
		{
			$this->result->setData($data);
		}
	}

	/**
	 * Copies entity.
	 *
	 * @param ContainerCollection $containerCollection
	 * @return Result
	 */
	public function copy(ContainerCollection $containerCollection)
	{
		$this->result->setData([]);

		$result = [];
		foreach ($containerCollection as $container)
		{
			$entityId = $this->getEntityIdToCopy($container);
			if (!$entityId)
			{
				continue;
			}

			$fields = $this->getFields($container, $entityId);

			if (empty($fields))
			{
				$result = $this->addToResultByName($result, [$entityId => false], $this->implementerName);
			}
			else
			{
				$dictionary = $this->getDictionary($container, $fields);

				$fields = $this->prepareFieldsToCopy($container, $fields);

				$copiedEntityId = $this->addEntity($container, $fields);
				if (!$copiedEntityId)
				{
					$result = $this->addToResultByName($result, [$entityId => false], $this->implementerName);
					$this->result->addErrors($this->implementer->getErrors());
					continue;
				}

				$copyChildrenResult = $this->copyChildren($container, $entityId, $copiedEntityId);
				if ($copyChildrenResult->getErrors())
				{
					$this->result->addErrors($copyChildrenResult->getErrors());
				}
				$result = $this->addToResult($result, $copyChildrenResult->getData());

				$result = $this->addToResultByName($result, [$entityId => $copiedEntityId], $this->implementerName);

				$this->setCopiedEntityId($container, $copiedEntityId);

				$this->setDictionary($container, $dictionary);
			}
		}

		$this->startCopyEntities($containerCollection);

		$result = $this->addToResult($result, $this->result->getData());

		$this->result->setData($result);

		return $this->result;
	}

	/**
	 * Returns the ids map of the parent copied entity.
	 *
	 * @return array
	 */
	public function getMapIdsCopiedEntity(): array
	{
		return $this->getMapIdsByImplementer($this->implementerName, $this->result->getData());
	}

	/**
	 * Returns the ids map of result by name implementer.
	 *
	 * @param string $implementerName Implementer name.
	 * @param array $data Result list.
	 * @return array
	 */
	public function getMapIdsByImplementer(string $implementerName, array $data): array
	{
		$mapIds = [];

		if (array_key_exists($implementerName, $data))
		{
			return $data[$implementerName];
		}

		return $mapIds;
	}

	protected function getEntityIdToCopy(Container $container)
	{
		return $container->getEntityId();
	}

	protected function getFields(Container $container, $entityId)
	{
		return $this->implementer->getFields($container, $entityId);
	}

	protected function getDictionary(Container $container, array $fields)
	{
		return $container->getDictionary();
	}

	protected function prepareFieldsToCopy(Container $container, $fields)
	{
		$fields = $this->implementer->prepareFieldsToCopy($container, $fields);

		return $fields;
	}

	protected function addEntity(Container $container, $fields)
	{
		return $this->implementer->add($container, $fields);
	}

	protected function copyChildren(Container $container, $entityIdToCopy, $copiedEntityId)
	{
		return $this->implementer->copyChildren($container, $entityIdToCopy, $copiedEntityId);
	}

	protected function setCopiedEntityId(Container $container, $copiedEntityId)
	{
		$container->setCopiedEntityId($copiedEntityId);
	}

	protected function setDictionary(Container $container, Dictionary $dictionary)
	{
		$container->setDictionary($dictionary);
	}

	private function addToResultByName(array $result, array $mapIds, string $implementerName): array
	{
		if (!array_key_exists($implementerName, $result))
		{
			$result[$implementerName] = [];
		}

		$result[$implementerName] += $mapIds;

		return $result;
	}

	private function addToResult(array $result, array $mapIds): array
	{
		foreach ($mapIds as $key => $values)
		{
			if (array_key_exists($key, $result))
			{
				$result[$key] += $values;
			}
			else
			{
				$result[$key] = $values;
			}
		}

		return $result;
	}
}