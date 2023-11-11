<?php

namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger;
use Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable;
use Bitrix\Main\Result;

class TemplatesTunnel
{
	protected Template $srcTemplate;
	protected Template $dstTemplate;
	protected array $availableTriggers = [];

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

		$documentService = \CBPRuntime::getRuntime()->getDocumentService();
		$dstTarget = $documentService->createAutomationTarget($this->dstTemplate->getDocumentType());

		foreach ($dstTarget->getAvailableTriggers() as $triggerDescription)
		{
			if (is_string($triggerDescription['CODE'] ?? null) && $triggerDescription['CODE'] !== '')
			{
				$this->availableTriggers[$triggerDescription['CODE']] = $triggerDescription;
			}
		}
	}

	public function copyRobots(array $robotNames, int $userId): Result
	{
		if ($this->srcTemplate->isExternalModified() || $this->dstTemplate->isExternalModified())
		{
			$result = new Result();
			$result->setData([
				'copied' => [],
				'denied' => [],
			]);

			return $result;
		}

		$copyingRobots = $this->srcTemplate->getRobotsByNames($robotNames);
		$partitioned = $this->partitionByDescription($this->dstTemplate->getDocumentType(), $copyingRobots);

		$newRobots = [];
		/** @var Robot $robot */
		foreach ($partitioned['available'] as $robot)
		{
			$draftRobot = new Robot([
				'Name' => Robot::generateName(),
				'Type' => $robot->getType(),
				'Activated' => $robot->isActivated() ? 'Y' : 'N',
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
		if ($this->srcTemplate->isExternalModified() || $this->dstTemplate->isExternalModified())
		{
			$result = new Result();
			$result->setData([
				'moved' => [],
				'denied' => [],
			]);

			return $result;
		}

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

	public function copyTriggers(array $triggerNames): Result
	{
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();
		$target = $documentService->createAutomationTarget($this->srcTemplate->getDocumentType());

		/** @var EO_Trigger[] $triggersToCopy */
		$triggersToCopy = array_filter(
			$target->getTriggerObjects([$this->srcTemplate->getDocumentStatus()]),
			fn ($trigger) => in_array($trigger->getId(), $triggerNames, true),
		);

		$copiedTriggers = [];
		$deniedTriggers = [];
		foreach ($triggersToCopy as $trigger)
		{
			if (!array_key_exists($trigger->getCode(), $this->availableTriggers))
			{
				$deniedTriggers[] = $trigger;
				continue;
			}

			$newTrigger = TriggerTable::createObject();

			$complexDocumentType = $this->dstTemplate->getDocumentType();

			$newTrigger->setName($trigger->getName());
			$newTrigger->setCode($trigger->getCode());
			$newTrigger->setModuleId($complexDocumentType[0]);
			$newTrigger->setEntity($complexDocumentType[1]);
			$newTrigger->setDocumentType($complexDocumentType[2]);
			$newTrigger->setDocumentStatus($this->dstTemplate->getDocumentStatus());
			$newTrigger->setApplyRules($trigger->getApplyRules());

			$newTrigger->save();
			$copiedTriggers[] = $newTrigger;
		}

		$result = new Result();
		$result->setData([
			'copied' => $copiedTriggers,
			'denied' => $deniedTriggers,
			'original' => $triggersToCopy,
		]);

		return $result;
	}

	public function moveTriggers(array $triggerNames): Result
	{
		$copyingResult = $this->copyTriggers($triggerNames);

		$result = new Result();
		if ($copyingResult->isSuccess())
		{
			$deniedTriggers = [];
			foreach ($copyingResult->getData()['denied'] as $trigger)
			{
				$deniedTriggers[$trigger->getId()] = $trigger;
			}

			/** @var EO_Trigger $trigger */
			foreach ($copyingResult->getData()['original'] as $trigger)
			{
				if (!array_key_exists($trigger->getId(), $deniedTriggers))
				{
					$trigger->delete();
				}
			}

			$result->setData([
				'moved' => $copyingResult->getData()['copied'],
				'denied' => $copyingResult->getData()['denied'],
				'original' => $copyingResult->getData()['original'],
			]);
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
			$type = mb_strtolower($robot->getType());
			$availableRobots = Template::getAvailableRobots($complexDocumentType);
			$filter = $robot->getDescription()['FILTER'] ?? [];

			$isRobotAvailable = (
				isset($availableRobots[$type])
				&& $runtime->checkActivityFilter($filter, $complexDocumentType)
			);
			$direction = $isRobotAvailable  ? 'available' : 'unavailable';

			$partitioned[$direction][] = $robot;
		}

		return $partitioned;
	}
}