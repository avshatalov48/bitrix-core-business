<?php
namespace Bitrix\Lists\Copy\Integration;

use Bitrix\Iblock\Copy\Manager;
use Bitrix\Lists\Copy\Implement\Children\Field;
use Bitrix\Lists\Copy\Implement\Children\Workflow;
use Bitrix\Lists\Copy\Implement\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

class GroupStepper extends Stepper
{
	protected static $moduleId = "lists";

	protected $queueName = "ListsGroupQueue";
	protected $checkerName = "ListsGroupChecker_";
	protected $baseName = "ListsGroupStepper_";
	protected $errorName = "ListsGroupError_";

	/**
	 * Executes some action, and if return value is false, agent will be deleted.
	 * @param array $option Array with main data to show if it is necessary like {steps : 35, count : 7},
	 * where steps is an amount of iterations, count - current position.
	 * @return boolean
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
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

			$groupId = ($queueOption["groupId"] ?: 0);
			$copiedGroupId = ($queueOption["copiedGroupId"] ?: 0);
			$iblockTypeId = ($queueOption["iblockTypeId"] ?: "");

			$limit = 5;
			$offset = $this->getOffset($iblockTypeId, $copiedGroupId);

			$iblockIds = $this->getIblockIdsToCopy($iblockTypeId, $groupId);
			$count = count($iblockIds);
			$iblockIds = array_slice($iblockIds, $offset, $limit);
			$features = ($queueOption["features"] ?: []);

			if ($iblockIds)
			{
				$option["count"] = $count;

				$copyManager = new Manager($iblockTypeId, $iblockIds, $groupId);
				$copyManager->setTargetLocation($iblockTypeId, $copiedGroupId);
				$copyManager->setIblockImplementer(new Iblock());
				$copyManager->setFieldImplementer(new Field());
				$copyManager->setWorkflowImplementer(new Workflow($iblockTypeId));

				if (!in_array("field", $features))
				{
					$copyManager->removeFeature("field");
				}
				if (!in_array("section", $features))
				{
					$copyManager->removeFeature("section");
				}
				if (!in_array("element", $features))
				{
					$copyManager->removeFeature("element");
				}
				if (!in_array("workflow", $features))
				{
					$copyManager->removeFeature("workflow");
				}

				$copyManager->startCopy();

				$option["steps"] = $offset;

				return true;
			}
			else
			{
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

	private function getIblockIdsToCopy($iblockTypeId, $groupId)
	{
		$iblockIds = [];

		$filter = [
			"ACTIVE" => "Y",
			"TYPE" => $iblockTypeId,
			"CHECK_PERMISSIONS" => "N",
			"=SOCNET_GROUP_ID" => $groupId
		];

		$queryObject = \CIBlock::getList([], $filter);
		while ($iblock = $queryObject->fetch())
		{
			$iblockIds[] = $iblock["ID"];
		}

		return $iblockIds;
	}

	private function getOffset(string $iblockTypeId, int $copiedGroupId): int
	{
		$iblockIds = $this->getIblockIdsToCopy($iblockTypeId, $copiedGroupId);
		return count($iblockIds);
	}
}