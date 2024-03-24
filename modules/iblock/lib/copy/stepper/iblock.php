<?php

namespace Bitrix\Iblock\Copy\Stepper;

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

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

		$iterator = ElementTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
			],
			'order' => [
				'ID' => 'ASC',
			],
			'limit' => $limit,
			'offset' => $offset,
		]);
		while ($row = $iterator->fetch())
		{
			$elementIds[] = $row['ID'];
		}
		unset($row, $iterator);

		return [$elementIds, count($elementIds)];
	}

	private function getOffset(int $copiedIblockId): int
	{
		return ElementTable::getCount([
			'=IBLOCK_ID' => $copiedIblockId,
		]);
	}
}
