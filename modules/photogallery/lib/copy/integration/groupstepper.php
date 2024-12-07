<?php
namespace Bitrix\Photogallery\Copy\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Photogallery\Copy\Implement\Children\Element as ElementImplementer;
use Bitrix\Iblock\Copy\Implement\Section as SectionImplementer;
use Bitrix\Iblock\Copy\Section as SectionCopier;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

class GroupStepper extends Stepper
{
	protected static $moduleId = "photogallery";

	protected $queueName = "PhotogalleryGroupQueue";
	protected $checkerName = "PhotogalleryGroupChecker_";
	protected $baseName = "PhotogalleryGroupStepper_";
	protected $errorName = "PhotogalleryGroupError_";

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
		if (!Loader::includeModule("iblock") || !Loader::includeModule("photogallery"))
		{
			return false;
		}

		try
		{
			$queue = $this->getQueue();
			$this->setQueue($queue);
			$queueOption = $this->getOptionData($this->baseName);

			$copiedGroupId = ($queueOption["copiedGroupId"] ?? 0);
			$parentSectionId = ($queueOption["parentSectionId"] ?? 0);
			$newSectionName = ($queueOption["newSectionName"] ?? "");

			if ($parentSectionId && $newSectionName)
			{
				$containerCollection = new ContainerCollection();
				$containerCollection[] = new Container($parentSectionId);

				$elementImplementer = new ElementImplementer(ElementImplementer::SECTION_COPY_MODE);
				$sectionImplementer = new SectionImplementer();
				$sectionImplementer->setChangedFields([
					"NAME" => $newSectionName,
					"CODE" => "group_".$copiedGroupId,
					"SOCNET_GROUP_ID" => $copiedGroupId,
				]);
				$sectionImplementer->setChangedFieldsForChildSections(["CODE" => "group_".$copiedGroupId]);
				$sectionImplementer->setChild($elementImplementer);

				$sectionCopier = new SectionCopier($sectionImplementer);
				$sectionCopier->copy($containerCollection);

				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
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