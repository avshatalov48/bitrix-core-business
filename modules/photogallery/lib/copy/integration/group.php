<?php
namespace Bitrix\Photogallery\Copy\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Copy\Integration\Feature;
use Bitrix\Socialnetwork\Copy\Integration\Helper;

class Group implements Feature, Helper
{
	private $stepper;

	private $executiveUserId;
	private $features = [];

	private $moduleId = "photogallery";
	private $queueOption = "PhotogalleryGroupQueue";
	private $checkerOption = "PhotogalleryGroupChecker_";
	private $stepperOption = "PhotogalleryGroupStepper_";
	private $errorOption = "PhotogalleryGroupError_";

	public function __construct($executiveUserId = 0, array $features = [])
	{
		$this->executiveUserId = $executiveUserId;
		$this->features = $features;

		$this->stepper = GroupStepper::class;
	}

	public function copy($groupId, $copiedGroupId)
	{
		if (!Loader::includeModule("iblock") || !Loader::includeModule("socialnetwork"))
		{
			return;
		}

		$this->addToQueue($copiedGroupId);

		Option::set($this->moduleId, $this->checkerOption.$copiedGroupId, "Y");

		$sectionName = $this->getSectionName($groupId);
		$parentSectionId = $this->getParentSectionId($groupId, $sectionName);
		$newSectionName = $this->getNewSectionName($copiedGroupId);
		if (!$parentSectionId)
		{
			return;
		}

		$queueOption = [
			"copiedGroupId" => $copiedGroupId,
			"parentSectionId" => $parentSectionId,
			"newSectionName" => $newSectionName,
		];
		Option::set($this->moduleId, $this->stepperOption.$copiedGroupId, serialize($queueOption));

		$agent = \CAgent::getList([], [
			"MODULE_ID" => $this->moduleId,
			"NAME" => $this->stepper."::execAgent();"
		])->fetch();
		if (!$agent)
		{
			GroupStepper::bind(1);
		}
	}

	/**
	 * Returns a module id for work with options.
	 * @return string
	 */
	public function getModuleId()
	{
		return $this->moduleId;
	}

	/**
	 * Returns a map of option names.
	 *
	 * @return array
	 */
	public function getOptionNames()
	{
		return [
			"queue" => $this->queueOption,
			"checker" => $this->checkerOption,
			"stepper" => $this->stepperOption,
			"error" => $this->errorOption
		];
	}

	/**
	 * Returns a link to stepper class.
	 * @return string
	 */
	public function getLinkToStepperClass()
	{
		return $this->stepper;
	}

	/**
	 * Returns a text map.
	 * @return array
	 */
	public function getTextMap()
	{
		return [
			"title" => Loc::getMessage("GROUP_STEPPER_PROGRESS_TITLE"),
			"error" => Loc::getMessage("GROUP_STEPPER_PROGRESS_ERROR")
		];
	}

	private function getParentSectionId($groupId, $sectionName)
	{
		$parentSectionId = 0;

		$res = \CIBlockSection::getList([], ["NAME" => $sectionName, "SOCNET_GROUP_ID" => $groupId], false, ["ID"]);
		if ($section = $res->fetch())
		{
			$parentSectionId = $section["ID"];
		}

		return $parentSectionId;
	}

	private function getSectionName($groupId)
	{
		$fields = \CSocNetGroup::getByID($groupId);
		return Loc::getMessage("GROUP_FEATURE_SECTION_NAME_PREFIX").$fields["NAME"];
	}

	private function getNewSectionName($copiedGroupId)
	{
		$fields = \CSocNetGroup::getByID($copiedGroupId);
		return Loc::getMessage("GROUP_FEATURE_SECTION_NAME_PREFIX").$fields["NAME"];
	}

	private function addToQueue(int $copiedGroupId)
	{
		$option = Option::get($this->moduleId, $this->queueOption, "");
		$option = ($option !== "" ? unserialize($option) : []);
		$option = (is_array($option) ? $option : []);

		$option[] = $copiedGroupId;
		Option::set($this->moduleId, $this->queueOption, serialize($option));
	}
}