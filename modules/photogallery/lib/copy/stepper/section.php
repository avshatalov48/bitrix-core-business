<?php
namespace Bitrix\Photogallery\Copy\Stepper;

use Bitrix\Iblock\Copy\Implement\Element as ElementImplementer;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Dictionary;
use Bitrix\Main\Update\Stepper;

class Section extends Stepper
{
	protected static $moduleId = "photogallery";

	protected $queueName = "SectionGroupQueue";
	protected $checkerName = "SectionGroupChecker_";
	protected $baseName = "SectionGroupStepper_";
	protected $errorName = "SectionGroupError_";

	/**
	 * Executes some action, and if return value is false, agent will be deleted.
	 * @param array $option Array with main data to show if it is necessary like {steps : 35, count : 7},
	 * where steps is an amount of iterations, count - current position.
	 * @return boolean
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function execute(array &$option)
	{
		if (!Loader::includeModule("iblock") || !Loader::includeModule("photogallery"))
		{
			return false;
		}

		try
		{
			$queue = $this->getQueue();
			$this->setQueue($queue);
			$queueOption = $this->getQueueOption();
			if (empty($queueOption))
			{
				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
			}

			$sectionId = ($queueOption["sectionId"] ?: 0);
			$copiedSectionId = ($queueOption["copiedSectionId"] ?: 0);
			$errorOffset = ($queueOption["errorOffset"] ?: 0);

			$limit = 5;
			$offset = $this->getOffset($copiedSectionId) + $errorOffset;

			$enumRatio = ($queueOption["enumRatio"] ?: []);
			$sectionsRatio = ($queueOption["sectionsRatio"] ?: []);
			$mapIdsCopiedElements = ($queueOption["mapIdsCopiedElements"] ?: []);

			if ($sectionId)
			{
				list($elementIds, $selectedRowsCount) = $this->getElementIds($sectionId, $limit, $offset);

				$elementCopier = $this->getElementCopier();
				$containerCollection = $this->getContainerCollection($elementIds, $sectionsRatio, $enumRatio);
				$result = $elementCopier->copy($containerCollection);
				if (!$result->isSuccess())
				{
					$queueOption["errorOffset"] += $this->getErrorOffset($elementCopier);
				}

				$mapIdsCopiedElements = $elementCopier->getMapIdsCopiedEntity() + $mapIdsCopiedElements;
				$queueOption["mapIdsCopiedElements"] = $mapIdsCopiedElements;
				$this->saveQueueOption($queueOption);

				if ($selectedRowsCount < $limit)
				{
					$this->onAfterQueueCopy($queueOption);
					$this->deleteQueueOption();
					return !$this->isQueueEmpty();
				}
				else
				{
					$option["steps"] = $offset;
					return true;
				}
			}
			else
			{
				$this->onAfterQueueCopy($queueOption);
				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
			}
		}
		catch (\Exception $exception)
		{
			$this->writeToLog($exception);
			$this->deleteQueueOption();
			return false;
		}
	}

	private function getElementIds(int $sectionId, int $limit, int $offset): array
	{
		$elementIds = [];

		$connection = Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$queryObject = $connection->query("SELECT ID FROM `b_iblock_element` WHERE `IBLOCK_SECTION_ID` = '".
			$sqlHelper->forSql($sectionId)."' ORDER BY ID ASC LIMIT ".$limit." OFFSET ".$offset);
		$selectedRowsCount = $queryObject->getSelectedRowsCount();
		while ($element = $queryObject->fetch())
		{
			$elementIds[] = $element["ID"];
		}

		return [$elementIds, $selectedRowsCount];
	}

	private function getOffset(int $copiedSectionId): int
	{
		$elementIds = [];

		$connection = Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$queryObject = $connection->query("SELECT ID FROM `b_iblock_element` WHERE `IBLOCK_SECTION_ID` = '".
			$sqlHelper->forSql($copiedSectionId)."' ORDER BY ID");
		while ($element = $queryObject->fetch())
		{
			$elementIds[] = $element["ID"];
		}

		return count($elementIds);
	}

	private function getErrorOffset(EntityCopier $elementCopier): int
	{
		$numberIds = count($elementCopier->getMapIdsCopiedEntity());
		$numberSuccessIds = count(array_filter($elementCopier->getMapIdsCopiedEntity()));
		return $numberIds - $numberSuccessIds;
	}

	private function getContainerCollection($elementIds, array $sectionsRatio, array $enumRatio, $targetIblockId = 0)
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

	private function getElementCopier()
	{
		$elementImplementer = new ElementImplementer();
		return new EntityCopier($elementImplementer);
	}

	private function onAfterQueueCopy(array $queueOption): void
	{
		$copiedSectionId = ($queueOption["copiedSectionId"] ?: 0);
		if (!$copiedSectionId)
		{
			return;
		}

		$queryObject = \CIBlockSection::getList([], [
			"ID" => $copiedSectionId, "CHECK_PERMISSIONS" => "N"], false, ["IBLOCK_ID"]);
		if ($fields = $queryObject->fetch())
		{
			$iblockId = $fields["IBLOCK_ID"];
			if (!$iblockId)
			{
				return;
			}

			$sectionIds = [];
			$queryObject = \CIBlockSection::getTreeList(["IBLOCK_ID" => $iblockId], ["ID"]);
			while ($section = $queryObject->fetch())
			{
				$sectionIds[] = $section["ID"];
			}

			PClearComponentCacheEx($iblockId, $sectionIds);
		}
	}

	protected function getQueue(): array
	{
		return $this->getOptionData($this->queueName);
	}

	protected function setQueue(array $queue): void
	{
		$queueId = (string) current($queue);
		$this->checkerName = (strpos($this->checkerName, $queueId) === false ?
			$this->checkerName.$queueId : $this->checkerName);
		$this->baseName = (strpos($this->baseName, $queueId) === false ?
			$this->baseName.$queueId : $this->baseName);
		$this->errorName = (strpos($this->errorName, $queueId) === false ?
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
		$option = ($option !== "" ? unserialize($option) : []);
		return (is_array($option) ? $option : []);
	}

	protected function deleteOption($optionName)
	{
		Option::delete(static::$moduleId, ["name" => $optionName]);
	}
}