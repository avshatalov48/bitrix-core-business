<?php
namespace Bitrix\Iblock\Copy\Implement\Children;

use Bitrix\Iblock\Copy\Stepper\Iblock as IblockStepper;
use Bitrix\Iblock\Copy\Stepper\Section as SectionStepper;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;

class Element implements Child
{
	const IBLOCK_COPY_MODE = "iblock";
	const SECTION_COPY_MODE = "section";

	protected $moduleId = "iblock";

	private $copyMode;
	protected $sectionsRatio = [];
	protected $enumRatio = [];

	/**
	 * @var Result
	 */
	protected $result;

	public function __construct($copyMode)
	{
		$this->copyMode = $copyMode;

		$this->result = new Result();
	}

	public function setSectionsRatio(array $sectionsRatio)
	{
		$this->sectionsRatio = $sectionsRatio;
	}

	public function setEnumRatio(array $enumRatio)
	{
		$this->enumRatio = $enumRatio;
	}

	/**
	 * @param int $entityId
	 * @param int $copiedEntityId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function copy($entityId, $copiedEntityId): Result
	{
		if ($this->copyMode == self::SECTION_COPY_MODE)
		{
			return $this->copySectionElements($entityId, $copiedEntityId);
		}
		else
		{
			return $this->copyIblockElements($entityId, $copiedEntityId);
		}
	}

	/**
	 * @param int $sectionId
	 * @param int $copiedSectionId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function copySectionElements(int $sectionId, int $copiedSectionId)
	{
		$this->addToQueue($copiedSectionId, "SectionGroupQueue");

		Option::set($this->moduleId, "SectionGroupChecker_".$copiedSectionId, "Y");

		$queueOption = [
			"sectionId" => $sectionId,
			"copiedSectionId" => $copiedSectionId,
			"enumRatio" => ($this->enumRatio[$sectionId] ?: []),
			"sectionsRatio" => ($this->sectionsRatio[$sectionId] ?: [])
		];
		Option::set($this->moduleId, "SectionGroupStepper_".$copiedSectionId, serialize($queueOption));

		$agent = \CAgent::getList([], [
			"MODULE_ID" => $this->moduleId,
			"NAME" => SectionStepper::class."::execAgent();"
		])->fetch();
		if (!$agent)
		{
			SectionStepper::bind(1);
		}

		return $this->result;
	}

	/**
	 * @param int $iblockId
	 * @param int $copiedIblockId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private function copyIblockElements(int $iblockId, int $copiedIblockId)
	{
		$this->addToQueue($copiedIblockId, "IblockGroupQueue");

		$moduleId = "iblock";

		Option::set($moduleId, "IblockGroupChecker_".$copiedIblockId, "Y");

		$queueOption = [
			"iblockId" => $iblockId,
			"copiedIblockId" => $copiedIblockId,
			"enumRatio" => ($this->enumRatio[$iblockId] ?: []),
			"sectionsRatio" => ($this->sectionsRatio[$iblockId] ?: [])
		];
		Option::set($moduleId, "IblockGroupStepper_".$copiedIblockId, serialize($queueOption));

		$agent = \CAgent::getList([], [
			"MODULE_ID" => $moduleId,
			"NAME" => IblockStepper::class."::execAgent();"
		])->fetch();
		if (!$agent)
		{
			IblockStepper::bind(1);
		}

		return $this->result;
	}

	protected function addToQueue(int $copiedSectionId, $queueName)
	{
		$option = Option::get($this->moduleId, $queueName, "");
		$option = ($option !== "" ? unserialize($option) : []);
		$option = (is_array($option) ? $option : []);

		$option[] = $copiedSectionId;
		Option::set($this->moduleId, $queueName, serialize($option));
	}
}