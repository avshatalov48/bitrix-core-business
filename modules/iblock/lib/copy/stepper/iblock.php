<?php

namespace Bitrix\Iblock\Copy\Stepper;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Dictionary;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;

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
			$queueOption["errorOffset"] = (int)($queueOption["errorOffset"] ?? 0);

			$iblockId = (int)($queueOption["iblockId"] ?? 0);
			$copiedIblockId = (int)($queueOption["copiedIblockId"] ?? 0);
			$errorOffset = $queueOption["errorOffset"];
			$queueOption["errorOffset"] ??= 0;

			$limit = 5;
			$offset = $this->getOffset($copiedIblockId) + $errorOffset;

			$enumRatio = ($queueOption["enumRatio"] ?? []);
			if (!is_array($enumRatio))
			{
				$enumRatio = [];
			}
			$sectionsRatio = ($queueOption["sectionsRatio"] ?? []);
			if (!is_array($sectionsRatio))
			{
				$sectionsRatio = [];
			}
			$mapIdsCopiedElements = ($queueOption["mapIdsCopiedElements"] ?? []);
			if (!is_array($mapIdsCopiedElements))
			{
				$mapIdsCopiedElements = [];
			}
			$fieldRatio = ($queueOption['fieldRatio'] ?? []);
			if (!is_array($fieldRatio))
			{
				$fieldRatio = [];
			}

			if ($iblockId)
			{
				list($elementIds, $selectedRowsCount) = $this->getElementIds($iblockId, $limit, $offset);

				$elementCopier = $this->getElementCopier();

				if (empty($fieldRatio))
				{
					$fieldRatio = $this->compileFieldRatio($iblockId, $copiedIblockId);
					if (!empty($fieldRatio))
					{
						$queueOption['fieldRatio'] = $fieldRatio;
					}
				}

				$dictionary = new Dictionary([
					'targetIblockId' => $copiedIblockId,
					'enumRatio' => $enumRatio,
					'sectionsRatio' => $sectionsRatio,
					'fieldRatio' => $fieldRatio,
				]);

				$containerCollection = $this->fillContainerCollection($elementIds, $dictionary);

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

	private function compileFieldRatio(int $iblockId, int $copiedIblockId): array
	{
		$result = [];

		$source = $this->getPropertyList($iblockId);
		$destination = $this->getPropertyList($copiedIblockId);

		foreach ($source as $hash => $sourceId)
		{
			if (isset($destination[$hash]))
			{
				$result[$sourceId] = $destination[$hash];
			}
		}

		return $result;
	}

	private function getPropertyHash(array $property): string
	{
		$property['USER_TYPE'] = (string)$property['USER_TYPE'];

		return
			$property['NAME'] . '|'
			. $property['PROPERTY_TYPE'] . '|' . $property['USER_TYPE'] . '|'
			. $property['MULTIPLE']
		;
	}

	private function getPropertyList(int $iblockId): array
	{
		$result = [];
		$iterator = PropertyTable::getList([
			'select' => [
				'ID',
				'NAME',
				'PROPERTY_TYPE',
				'MULTIPLE',
				'USER_TYPE',
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
			],
			'order' => [
				'ID' => 'ASC',
			]
		]);
		while ($row = $iterator->fetch())
		{
			$result[$this->getPropertyHash($row)] = (int)$row['ID'];
		}
		unset($iterator);

		return $result;
	}
}
