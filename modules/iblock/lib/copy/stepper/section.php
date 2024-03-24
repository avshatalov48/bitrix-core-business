<?php

namespace Bitrix\Iblock\Copy\Stepper;

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;

class Section extends Entity
{
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
		if (!Loader::includeModule(self::$moduleId))
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
		$section = SectionTable::getRow([
			'select' => [
				'ID',
				'IBLOCK_ID',
			],
			'filter' => [
				'=ID' => $sectionId,
			],
		]);

		if ($section === null)
		{
			return [[], 0];
		}

		$elementIds = [];
		$iterator = ElementTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=IBLOCK_ID' => (int)$section['IBLOCK_ID'],
				'=IBLOCK_SECTION_ID' => (int)$section['ID'],
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

	private function getOffset(int $copiedSectionId): int
	{
		$section = SectionTable::getRow([
			'select' => [
				'ID',
				'IBLOCK_ID',
			],
			'filter' => [
				'=ID' => $copiedSectionId,
			],
		]);

		if ($section === null)
		{
			return 0;
		}

		return ElementTable::getCount([
			'=IBLOCK_ID' => (int)$section['IBLOCK_ID'],
			'=IBLOCK_SECTION_ID' => (int)$section['ID'],
		]);
	}
}
