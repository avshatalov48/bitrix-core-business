<?php
namespace Bitrix\Lists\Copy\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Copy\Integration\Feature;

class Group implements Feature
{
	private $executiveUserId;
	private $features = [];

	const MODULE_ID = "lists";
	const QUEUE_OPTION = "ListsGroupQueue";
	const CHECKER_OPTION = "ListsGroupChecker_";
	const STEPPER_OPTION = "ListsGroupStepper_";
	const STEPPER_CLASS = GroupStepper::class;
	const ERROR_OPTION = "ListsGroupError_";

	public function __construct($executiveUserId = 0, array $features = [])
	{
		$this->executiveUserId = $executiveUserId;
		$this->features = $features;
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

		Option::set(self::MODULE_ID, self::CHECKER_OPTION.$copiedGroupId, "Y");

		$queueOption = [
			"iblockTypeId" => $iblockTypeId,
			"groupId" => $groupId,
			"copiedGroupId" => $copiedGroupId,
			"features" => $this->features
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
		$option = Option::get(self::MODULE_ID, self::QUEUE_OPTION, "");
		$option = ($option !== "" ? unserialize($option) : []);
		$option = (is_array($option) ? $option : []);

		$option[] = $copiedGroupId;
		Option::set(self::MODULE_ID, self::QUEUE_OPTION, serialize($option));
	}
}