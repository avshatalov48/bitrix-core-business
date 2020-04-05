<?php
namespace Bitrix\Iblock\Copy\Implement;

use Bitrix\Iblock\Copy\Implement\Children\Child;
use Bitrix\Iblock\Copy\Implement\Children\Element as ElementChild;
use Bitrix\Iblock\Copy\Section as SectionCopier;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Section extends CopyImplementer
{
	const SECTION_COPY_ERROR = "SECTION_COPY_ERROR";

	/**
	 * @var Child[]
	 */
	private $child;

	private $changedFields = [];
	private $changedFieldsForChildSections = [];

	/**
	 * @var SectionCopier|null
	 */
	private $sectionCopier = null;

	/**
	 * Writes child implementer to the copy queue.
	 *
	 * @param Child $child Child implementer.
	 */
	public function setChild(Child $child)
	{
		$this->child[] = $child;
	}

	/**
	 * To copy child sections needs section copier.
	 *
	 * @param SectionCopier $sectionCopier Section copier.
	 */
	public function setSectionCopier(SectionCopier $sectionCopier): void
	{
		$this->sectionCopier = $sectionCopier;
	}

	public function setChangedFields($changedFields)
	{
		$this->changedFields = array_merge($this->changedFields, $changedFields);
	}

	public function setChangedFieldsForChildSections($changedFieldsForChildSections)
	{
		$this->changedFieldsForChildSections = array_merge(
			$this->changedFieldsForChildSections, $changedFieldsForChildSections);
	}

	/**
	 * Adds entity.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool return entity id or false.
	 */
	public function add(Container $container, array $fields)
	{
		$sectionObject = new \CIBlockSection;

		$result = $sectionObject->add($fields);

		if (!$result)
		{
			if ($sectionObject->LAST_ERROR)
			{
				$this->result->addError(new Error($sectionObject->LAST_ERROR, self::SECTION_COPY_ERROR));
			}
			else
			{
				$this->result->addError(new Error("Unknown error", self::SECTION_COPY_ERROR));
			}
		}
		return $result;
	}

	/**
	 * Returns section fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$queryObject = \CIBlockSection::getList([], ["ID" => $entityId, "CHECK_PERMISSIONS" => "N"], false);
		return (($fields = $queryObject->fetch()) ? $fields : []);
	}

	/**
	 * Preparing data before creating a new entity.
	 *
	 * @param Container $container
	 * @param array $fields List entity fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		if (!empty($this->changedFields))
		{
			$fields = $this->changeFields($fields);
		}

		if (!empty($fields["PICTURE"]))
		{
			$fields["PICTURE"] = \CFile::makeFileArray($fields["PICTURE"]);
		}
		if (!empty($fields["DETAIL_PICTURE"]))
		{
			$fields["DETAIL_PICTURE"] = \CFile::makeFileArray($fields["DETAIL_PICTURE"]);
		}

		if (!empty($container->getParentId()))
		{
			$fields["IBLOCK_SECTION_ID"] = $container->getParentId();
		}

		$fields["RIGHTS"] = $this->getRights($fields["IBLOCK_ID"], $fields["ID"]);

		unset($fields["XML_ID"]);

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $sectionId Section id.
	 * @param int $copiedSectionId Copied section id.
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function copyChildren(Container $container, $sectionId, $copiedSectionId)
	{
		$results = [];

		$results[] = $copyChildrenResult = $this->copyChildSections($sectionId, $copiedSectionId);

		$copyResult = $copyChildrenResult->getData();
		$sectionsRatio[$sectionId] = $this->getSectionsMapIds($copyResult);
		$sectionsRatio[$sectionId] = $sectionsRatio[$sectionId] + [$sectionId => $copiedSectionId];

		$enumRatio = [];

		foreach ($this->child as $child)
		{
			if ($child instanceof ElementChild)
			{
				$child->setEnumRatio($enumRatio);
				$child->setSectionsRatio($sectionsRatio);
			}

			$results[] = $child->copy($sectionId, $copiedSectionId);

			if (method_exists($child, "getEnumRatio"))
			{
				$enumRatio = $child->getEnumRatio();
			}
		}

		return $this->getResult();
	}

	private function copyChildSections(int $sectionId, int $copiedSectionId)
	{
		if (!$this->sectionCopier)
		{
			return new Result();
		}

		$this->cleanChangedFields();

		$containerCollection = new ContainerCollection();

		$queryObject = \CIBlockSection::getList([], [
			"SECTION_ID" => $sectionId, "CHECK_PERMISSIONS" => "N"], false, ["ID"]);
		while ($section = $queryObject->fetch())
		{
			$container = new Container($section["ID"]);
			$container->setParentId($copiedSectionId);
			$containerCollection[] = $container;
		}

		if (!$containerCollection->isEmpty())
		{
			return $this->sectionCopier->copy($containerCollection);
		}

		return new Result();
	}

	private function getSectionsMapIds(array $data): array
	{
		$sectionMapIds = [];
		foreach ($data as $key => $values)
		{
			if ($key == get_class() && is_array($values))
			{
				$sectionMapIds = $sectionMapIds + $this->getSectionsMapIds($values);
			}
			elseif (is_int($key))
			{
				$sectionMapIds[$key] = $values;
			}
		}
		return $sectionMapIds;
	}

	private function changeFields(array $fields)
	{
		foreach ($this->changedFields as $fieldId => $fieldValue)
		{
			if (array_key_exists($fieldId, $fields))
			{
				$fields[$fieldId] = $fieldValue;
			}
		}

		foreach ($this->changedFieldsForChildSections as $fieldId => $fieldValue)
		{
			if (array_key_exists($fieldId, $fields))
			{
				$fields[$fieldId] = $fieldValue;
			}
		}

		return $fields;
	}

	private function cleanChangedFields()
	{
		$this->changedFields = [];
	}

	private function getRights(int $iblockId, int $elementId)
	{
		$rights = [];

		$objectRights = new \CIBlockSectionRights($iblockId, $elementId);

		$groupCodeIgnoreList = $this->getGroupCodeIgnoreList($iblockId);

		foreach ($objectRights->getRights() as $right)
		{
			if (!in_array($right["GROUP_CODE"], $groupCodeIgnoreList))
			{
				$rights["n".(count($rights))] = [
					"GROUP_CODE" => $right["GROUP_CODE"],
					"DO_CLEAN" => "N",
					"TASK_ID" => $right["TASK_ID"],
				];
			}
		}

		return $rights;
	}

	private function getGroupCodeIgnoreList(int $iblockId): array
	{
		$groupCodeIgnoreList = [];

		$rightObject = new \CIBlockRights($iblockId);
		foreach ($rightObject->getRights() as $right)
		{
			$groupCodeIgnoreList[] = $right["GROUP_CODE"];
		}

		return $groupCodeIgnoreList;
	}
}