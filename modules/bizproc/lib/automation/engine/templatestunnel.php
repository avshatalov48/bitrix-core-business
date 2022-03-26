<?php

namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Main\Result;

class TemplatesTunnel
{
	/** @var TemplateScope */
	private $srcTemplate;
	/** @var TemplateScope */
	private $dstTemplate;

	public function __construct(Template $srcTemplate, Template $dstTemplate)
	{
		$this->srcTemplate = $srcTemplate;
		$this->dstTemplate = $dstTemplate;

		if (
			$srcTemplate->getDocumentType() === $dstTemplate->getDocumentType()
			&& $srcTemplate->getDocumentStatus() === $dstTemplate->getDocumentStatus()
		)
		{
			$this->dstTemplate = $this->srcTemplate;
		}
	}

	public function copyRobots(array $robotNames, int $userId): Result
	{
		$copyingRobots = $this->srcTemplate->getRobotsByNames($robotNames);
		$partitioned = $this->partitionByDescription($this->dstTemplate->getDocumentType(), $copyingRobots);

		$newRobots = [];
		/** @var Robot $robot */
		foreach ($partitioned['available'] as $robot)
		{
			$draftRobot = new Robot([
				'Name' => Robot::generateName(),
				'Type' => $robot->getType(),
				'Properties' => $robot->getProperties(),
			]);

			$delayInterval = $robot->getDelayInterval();
			$condition = $robot->getCondition();
			if ($delayInterval && !$delayInterval->isNow())
			{
				$draftRobot->setDelayInterval($delayInterval);
				$draftRobot->setDelayName(Robot::generateName());
			}
			if ($condition)
			{
				$draftRobot->setCondition($robot->getCondition());
			}
			if ($robot->isExecuteAfterPrevious())
			{
				$draftRobot->setExecuteAfterPrevious();
			}

			$newRobots[] = $draftRobot;
		}

		if ($newRobots)
		{
			$result = $this->dstTemplate->save(
				array_merge($this->dstTemplate->getRobots(), $newRobots),
				$userId
			);
		}
		else
		{
			$result = new Result();
		}

		if ($result->isSuccess())
		{
			$result->setData([
				'copied' => $partitioned['available'],
				'denied' => $partitioned['unavailable'],
			]);
		}

		return $result;
	}

	public function moveRobots(array $robotNames, int $userId): Result
	{
		$result = new Result();
		$copyingResult = $this->copyRobots($robotNames, $userId);

		if ($copyingResult->isSuccess())
		{
			$deletingResult = $this->srcTemplate->deleteRobots($copyingResult->getData()['copied'], $userId);

			if ($deletingResult->isSuccess())
			{
				$result->setData([
					'moved' => $copyingResult->getData()['copied'],
					'denied' => $copyingResult->getData()['denied'],
				]);
			}
			else
			{
				$result->addErrors($deletingResult->getErrors());
			}
		}
		else
		{
			$result->addErrors($copyingResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param array $complexDocumentType
	 * @param Robot[] $robots
	 * @return array
	 */
	private function partitionByDescription(array $complexDocumentType, array $robots): array
	{
		$runtime = \CBPRuntime::GetRuntime();
		$partitioned = [
			'available' => [],
			'unavailable' => [],
		];

		foreach ($robots as $robot)
		{
			$filter = $robot->getDescription()['FILTER'] ?? [];
			$isRobotAvailable = $runtime->checkActivityFilter($filter, $complexDocumentType);
			$direction = $isRobotAvailable  ? 'available' : 'unavailable';

			$partitioned[$direction][] = $robot;
		}

		return $partitioned;
	}
}