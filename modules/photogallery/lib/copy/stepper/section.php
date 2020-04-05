<?php
namespace Bitrix\Photogallery\Copy\Stepper;

use Bitrix\Iblock\Copy\Implement\Element as ElementImplementer;
use Bitrix\Main\Application;
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

			$limit = 5;
			$offset = $this->getOffset($copiedSectionId);

			$enumRatio = ($queueOption["enumRatio"] ?: []);
			$sectionsRatio = ($queueOption["sectionsRatio"] ?: []);

			if ($sectionId)
			{
				list($elementIds, $selectedRowsCount) = $this->getElementIds($sectionId, $limit, $offset);

				$elementCopier = $this->getElementCopier();
				$containerCollection = $this->getContainerCollection($elementIds, $sectionsRatio, $enumRatio);
				$elementCopier->copy($containerCollection);

				if ($selectedRowsCount < $limit)
				{
					$this->afterQueueCopy($queueOption);
					$this->deleteCurrentQueue($queue);
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
				$this->afterQueueCopy($queueOption);
				$this->deleteCurrentQueue($queue);
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

	protected function afterQueueCopy(array $queueOption): void
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
}