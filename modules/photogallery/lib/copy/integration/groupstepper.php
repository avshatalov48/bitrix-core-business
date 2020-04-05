<?php
namespace Bitrix\Photogallery\Copy\Integration;

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

			$copiedGroupId = ($queueOption["copiedGroupId"] ?: 0);
			$parentSectionId = ($queueOption["parentSectionId"] ?: 0);
			$newSectionName = ($queueOption["newSectionName"] ?: "");

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
				$result = $sectionCopier->copy($containerCollection);

				$resultData = current($result->getData());
				if (!empty($resultData[$parentSectionId]))
				{
					$this->deleteCurrentQueue($queue);
					$this->deleteQueueOption();
					return !$this->isQueueEmpty();
				}
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
}