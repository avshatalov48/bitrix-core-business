<?php
namespace Bitrix\Photogallery\Copy\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Copy\Integration\Feature;

class Group implements Feature
{
	const MODULE_ID = "photogallery";
	const QUEUE_OPTION = "PhotogalleryGroupQueue";
	const CHECKER_OPTION = "PhotogalleryGroupChecker_";
	const STEPPER_OPTION = "PhotogalleryGroupStepper_";
	const STEPPER_CLASS = GroupStepper::class;
	const ERROR_OPTION = "PhotogalleryGroupError_";

	private $executiveUserId;
	private $features = [];

	public function __construct($executiveUserId = 0, array $features = [])
	{
		$this->executiveUserId = $executiveUserId;
		$this->features = $features;
	}

	public function copy($groupId, $copiedGroupId)
	{
		if (!Loader::includeModule("iblock") || !Loader::includeModule("socialnetwork"))
		{
			return;
		}

		$this->addToQueue($copiedGroupId);

		Option::set(self::MODULE_ID, self::CHECKER_OPTION.$copiedGroupId, "Y");

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
		Option::set(self::MODULE_ID, self::STEPPER_OPTION.$copiedGroupId, serialize($queueOption));

		$agent = \CAgent::getList([], [
			"MODULE_ID" => self::MODULE_ID,
			"NAME" => GroupStepper::class."::execAgent();"
		])->fetch();
		if (!$agent)
		{
			GroupStepper::bind(1);
		}
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
		$option = Option::get(self::MODULE_ID, self::QUEUE_OPTION, "");
		$option = ($option !== "" ? unserialize($option) : []);
		$option = (is_array($option) ? $option : []);

		$option[] = $copiedGroupId;
		Option::set(self::MODULE_ID, self::QUEUE_OPTION, serialize($option));
	}
}