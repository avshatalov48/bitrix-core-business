<?php
namespace Bitrix\Iblock\Copy\Stepper;

use Bitrix\Iblock\Copy\Implement\Element as ElementImplementer;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Type\Dictionary;
use Bitrix\Main\Update\Stepper;

abstract class Entity extends Stepper
{
	protected static $moduleId = "iblock";

	protected function getContainerCollection($elementIds, array $sectionsRatio, array $enumRatio, $targetIblockId = 0)
	{
		$containerCollection = new ContainerCollection();

		foreach ($elementIds as $elementId)
		{
			$container = new Container($elementId);
			$dictionary = new Dictionary(
				[
					"targetIblockId" => $targetIblockId,
					"enumRatio" => $enumRatio,
					"sectionsRatio" => $sectionsRatio
				]
			);
			$container->setDictionary($dictionary);

			$containerCollection[] = $container;
		}

		return $containerCollection;
	}

	protected function getElementCopier()
	{
		$elementImplementer = new ElementImplementer();
		return new EntityCopier($elementImplementer);
	}

	protected function onAfterCopy(array $queueOption)
	{
		$this->saveErrorOption($queueOption);
	}

	protected function getErrorOffset(EntityCopier $elementCopier): int
	{
		$numberIds = count($elementCopier->getMapIdsCopiedEntity());
		$numberSuccessIds = count(array_filter($elementCopier->getMapIdsCopiedEntity()));
		return $numberIds - $numberSuccessIds;
	}

	private function saveErrorOption(array $queueOption)
	{
		$mapIdsCopiedElements = $queueOption["mapIdsCopiedElements"] ?: [];

		$mapIdsWithErrors = [];
		foreach ($mapIdsCopiedElements as $elementId => $copiedElementId)
		{
			if (!$copiedElementId)
			{
				$mapIdsWithErrors[] = $elementId;
			}
		}

		if ($mapIdsWithErrors)
		{
			Option::set(self::$moduleId, $this->errorName, serialize($mapIdsWithErrors));
		}
	}

	protected function getQueue(): array
	{
		return $this->getOptionData($this->queueName);
	}

	protected function setQueue(array $queue): void
	{
		$queueId = (string) current($queue);
		$this->checkerName = (mb_strpos($this->checkerName, $queueId) === false ?
			$this->checkerName.$queueId : $this->checkerName);
		$this->baseName = (mb_strpos($this->baseName, $queueId) === false ?
			$this->baseName.$queueId : $this->baseName);
		$this->errorName = (mb_strpos($this->errorName, $queueId) === false ?
			$this->errorName.$queueId : $this->errorName);
	}

	protected function getQueueOption()
	{
		return $this->getOptionData($this->baseName);
	}

	protected function saveQueueOption(array $data)
	{
		Option::set(static::$moduleId, $this->baseName, serialize($data));
	}

	protected function deleteQueueOption()
	{
		$queue = $this->getQueue();
		$this->setQueue($queue);
		$this->deleteCurrentQueue($queue);
		Option::delete(static::$moduleId, ["name" => $this->checkerName]);
		Option::delete(static::$moduleId, ["name" => $this->baseName]);
	}

	protected function deleteCurrentQueue(array $queue): void
	{
		$queueId = current($queue);
		$currentPos = array_search($queueId, $queue);
		if ($currentPos !== false)
		{
			unset($queue[$currentPos]);
			Option::set(static::$moduleId, $this->queueName, serialize($queue));
		}
	}

	protected function isQueueEmpty()
	{
		$queue = $this->getOptionData($this->queueName);
		return empty($queue);
	}

	protected function getOptionData($optionName)
	{
		$option = Option::get(static::$moduleId, $optionName);
		$option = ($option !== "" ? unserialize($option, ['allowed_classes' => false]) : []);
		return (is_array($option) ? $option : []);
	}

	protected function deleteOption($optionName)
	{
		Option::delete(static::$moduleId, ["name" => $optionName]);
	}
}