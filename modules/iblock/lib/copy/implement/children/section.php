<?php
namespace Bitrix\Iblock\Copy\Implement\Children;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Section implements Child
{
	/**
	 * @var Result
	 */
	private $result;

	private $sectionsRatio = [];

	public function __construct()
	{
		$this->result = new Result();
	}

	/**
	 * In order for the elements to be copied to the desired sections will return
	 * the identifier ratio of the copied partitions.
	 * @param int $iblockId Iblock id.
	 * @return array
	 */
	public function getSectionsRatio($iblockId = 0): array
	{
		return (isset($this->sectionsRatio[$iblockId]) ? $this->sectionsRatio[$iblockId] : $this->sectionsRatio);
	}

	public function copy($iblockId, $copiedIblockId): Result
	{
		$sectionObject = new \CIBlockSection;

		$sections = $this->getSections($iblockId, $copiedIblockId);
		$parentRatioIds = $this->getParentRatioIds($sections);
		$ratioIds = $this->addSections($sectionObject, $sections);
		$this->updateSections($sectionObject, $parentRatioIds, $ratioIds);
		$this->setRatios($iblockId, $ratioIds);

		return $this->result;
	}

	private function getSections($iblockId, $copiedIblockId)
	{
		$sections = [];

		$queryObject = \CIBlockSection::getList([], ["IBLOCK_ID" => $iblockId, "CHECK_PERMISSIONS" => "N"], false);
		while ($section = $queryObject->fetch())
		{
			$section["IBLOCK_ID"] = $copiedIblockId;
			$sections[] = $section;
		}

		return $sections;
	}

	private function getParentRatioIds(array $sections): array
	{
		$oldRatioIds = [];
		foreach ($sections as $section)
		{
			if (!empty($section["IBLOCK_SECTION_ID"]))
			{
				$oldRatioIds[$section["ID"]] = $section["IBLOCK_SECTION_ID"];
			}
		}
		return $oldRatioIds;
	}

	private function addSections(\CIBlockSection $sectionObject, array $sections): array
	{
		$rationIds = [];
		foreach ($sections as $section)
		{
			unset($section["IBLOCK_SECTION_ID"]);
			$rationIds[$section["ID"]] = $this->addSection($sectionObject, $section);
		}

		return $rationIds;
	}

	private function updateSections(\CIBlockSection $sectionObject, array $parentRatioIds, array $ratioIds)
	{
		foreach ($parentRatioIds as $parentId => $childId)
		{
			if (array_key_exists($parentId, $ratioIds) && array_key_exists($childId, $ratioIds))
			{
				$copiedSectionId = $ratioIds[$parentId];
				$sectionObject->update($copiedSectionId, ["IBLOCK_SECTION_ID" => $ratioIds[$childId]]);
			}
		}
	}

	private function addSection(\CIBlockSection $sectionObject, $section)
	{
		$result = $sectionObject->add($section);
		if (!$result)
		{
			if ($sectionObject->LAST_ERROR)
			{
				$this->result->addError(new Error($sectionObject->LAST_ERROR));
			}
			else
			{
				$this->result->addError(new Error("Unknown error while copying section"));
			}
		}
		return $result;
	}

	private function setRatios($iblockId, array $ratioIds)
	{
		$this->sectionsRatio[$iblockId] = $ratioIds;
	}
}