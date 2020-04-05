<?php
namespace Bitrix\Lists\Copy\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Copy\Integration\Feature;
use Bitrix\Socialnetwork\Copy\Integration\Helper;

class Group implements Feature, Helper
{
	private $stepper;

	private $executiveUserId;
	private $features = [];

	private $moduleId = "lists";
	private $queueOption = "ListsGroupQueue";
	private $checkerOption = "ListsGroupChecker_";
	private $stepperOption = "ListsGroupStepper_";
	private $errorOption = "ListsGroupError_";

	public function __construct($executiveUserId = 0, array $features = [])
	{
		$this->executiveUserId = $executiveUserId;
		$this->features = $features;

		$this->stepper = GroupStepper::class;
	}

	/**
	 * Starts the copy process.
	 * @param int $groupId Origin group id.
	 * @param int $copiedGroupId Copied group id.
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function copy($groupId, $copiedGroupId)
	{
		$iblockTypeId = "lists_socnet";

		$iblockIds = $this->getIblockIdsToCopy($iblockTypeId, $groupId);
		if (!$iblockIds)
		{
			return;
		}

		$this->addToQueue($copiedGroupId);

		Option::set($this->moduleId, $this->checkerOption.$copiedGroupId, "Y");

		$queueOption = [
			"iblockTypeId" => $iblockTypeId,
			"groupId" => $groupId,
			"copiedGroupId" => $copiedGroupId,
			"features" => $this->features
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

	private function getIblockIdsToCopy($iblockTypeId, $groupId)
	{
		$iblockIds = [];

		$filter = [
			"ACTIVE" => "Y",
			"TYPE" => $iblockTypeId,
			"CHECK_PERMISSIONS" => "N",
			"=SOCNET_GROUP_ID" => $groupId
		];

		$queryObject = \CIBlock::getList([], $filter);
		while ($iblock = $queryObject->fetch())
		{
			$iblockIds[] = $iblock["ID"];
		}

		return $iblockIds;
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