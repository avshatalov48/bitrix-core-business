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
	protected array $fieldRatio = [];

	/**
	 * @var Result
	 */
	protected $result;

	public function __construct($copyMode)
	{
		$this->copyMode = $copyMode;

		$this->result = new Result();
	}

	/**
	 * Add sections map from old iblock to new iblock.
	 *
	 * @param array $sectionsRatio Sections map.
	 * @return void
	 */
	public function setSectionsRatio(array $sectionsRatio)
	{
		$this->sectionsRatio = $sectionsRatio;
	}

	/**
	 * Add lists values map from old iblock to new iblock.
	 *
	 * @param array $enumRatio Lists values map.
	 * @return void
	 */
	public function setEnumRatio(array $enumRatio)
	{
		$this->enumRatio = $enumRatio;
	}

	/**
	 * Add properties map from old iblock to new iblock.
	 *
	 * @param array $fieldRatio Properties map.
	 * @return void
	 */
	public function setFieldRatio(array $fieldRatio): void
	{
		$this->fieldRatio = $fieldRatio;
	}

	/**
	 * Copy iblock.
	 *
	 * @param int $entityId Source iblock id.
	 * @param int $copiedEntityId Destination iblock id.
	 * @return Result
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
	 */
	private function copyIblockElements(int $iblockId, int $copiedIblockId)
	{
		$this->addToQueue($copiedIblockId, "IblockGroupQueue");

		$moduleId = "iblock";

		Option::set($moduleId, "IblockGroupChecker_".$copiedIblockId, "Y");

		$queueOption = [
			"iblockId" => $iblockId,
			"copiedIblockId" => $copiedIblockId,
			"enumRatio" => ($this->enumRatio[$iblockId] ?? []),
			"sectionsRatio" => ($this->sectionsRatio[$iblockId] ?? []),
			'fieldRatio' => ($this->fieldRatio[$iblockId] ?? []),
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
		$option = ($option !== "" ? unserialize($option, ['allowed_classes' => false]) : []);
		$option = (is_array($option) ? $option : []);

		$option[] = $copiedSectionId;
		Option::set($this->moduleId, $queueName, serialize($option));
	}
}