<?php
namespace Bitrix\Iblock\Copy\Stepper;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

class Iblock extends Entity
{
	protected $queueName = "IblockGroupQueue";
	protected $checkerName = "IblockGroupChecker_";
	protected $baseName = "IblockGroupStepper_";
	protected $errorName = "IblockGroupError_";

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
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		try
		{
			$queue = $this->getQueue();
			$this->setQueue($queue);
			$queueOption = $this->getOptionData($this->baseName);
			if (empty($queueOption))
			{
				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
			}

			$iblockId = ($queueOption["iblockId"] ?: 0);
			$copiedIblockId = ($queueOption["copiedIblockId"] ?: 0);
			$errorOffset = ($queueOption["errorOffset"] ?: 0);

			$limit = 5;
			$offset = $this->getOffset($copiedIblockId) + $errorOffset;

			$enumRatio = ($queueOption["enumRatio"] ?: []);
			$sectionsRatio = ($queueOption["sectionsRatio"] ?: []);
			$mapIdsCopiedElements = ($queueOption["mapIdsCopiedElements"] ?: []);

			if ($iblockId)
			{
				list($elementIds, $selectedRowsCount) = $this->getElementIds($iblockId, $limit, $offset);

				$elementCopier = $this->getElementCopier();
				$containerCollection = $this->getContainerCollection(
					$elementIds, $sectionsRatio, $enumRatio, $copiedIblockId);
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
					$this->deleteQueueOption();
					$this->onAfterCopy($queueOption);
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

	private function getElementIds(int $iblockId, int $limit, int $offset): array
	{
		$elementIds = [];

		$connection = Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$queryObject = $connection->query("SELECT ID FROM `b_iblock_element` WHERE `IBLOCK_ID` = '".
			$sqlHelper->forSql($iblockId)."' ORDER BY ID ASC LIMIT ".$limit." OFFSET ".$offset);
		$selectedRowsCount = $queryObject->getSelectedRowsCount();
		while ($element = $queryObject->fetch())
		{
			$elementIds[] = $element["ID"];
		}

		return [$elementIds, $selectedRowsCount];
	}

	private function getOffset(int $copiedIblockId): int
	{
		$elementIds = [];

		$connection = Application::getInstance()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$queryObject = $connection->query("SELECT ID FROM `b_iblock_element` WHERE `IBLOCK_ID` = '".
			$sqlHelper->forSql($copiedIblockId)."' ORDER BY ID");
		while ($element = $queryObject->fetch())
		{
			$elementIds[] = $element["ID"];
		}

		return count($elementIds);
	}
}