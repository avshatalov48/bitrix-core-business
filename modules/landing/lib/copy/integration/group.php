<?php
namespace Bitrix\Landing\Copy\Integration;

use Bitrix\Landing;
use Bitrix\Landing\Folder;
use Bitrix\Landing\Hook;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Socialnetwork\Copy\Integration\Feature;

class Group implements Feature
{
	const MODULE_ID = "landing";
	const QUEUE_OPTION = "LandingGroupQueue";
	const CHECKER_OPTION = "LandingGroupChecker_";
	const STEPPER_OPTION = "LandingGroupStepper_";
	const STEPPER_CLASS = GroupStepper::class;
	const ERROR_OPTION = "LandingGroupError_";

	private $executiveUserId;
	private $features = [];

	public function __construct($executiveUserId = 0, array $features = [])
	{
		$this->executiveUserId = $executiveUserId;
		$this->features = $features;
	}

	/**
	 * Initializes the start of copying the group’s knowledge base.
	 * @param int $groupId Group id.
	 * @param int $copiedGroupId Copied group id.
	 * @throws ArgumentOutOfRangeException
	 */
	public function copy($groupId, $copiedGroupId)
	{
		Hook::setEditMode();
		Landing\Site\Type::setScope(Landing\Site\Type::SCOPE_CODE_GROUP);
		$binder = Landing\Connector\SocialNetwork::getBindingRow($groupId);
		if (!$binder)
		{
			return;
		}

		$siteId = (int) $binder['ENTITY_ID'];

		$addSiteResult = Landing\Site::copy($siteId);
		if (!$addSiteResult->isSuccess())
		{
			return;
		}
		/** @var AddResult $addSiteResult */
		$copiedSiteId = (int) $addSiteResult->getId();

		// copy folders
		$folderMapIds = [];
		Landing\Site::copyFolders($siteId, $copiedSiteId, $folderMapIds);

		$folderIndexIds = [];
		$res = Folder::getList([
			'select' => [
				'ID', 'INDEX_ID'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'!INDEX_ID' => null
			]
		]);
		while ($row = $res->fetch())
		{
			$folderIndexIds[$row['ID']] = $row['INDEX_ID'];
		}

		$this->addToQueue($copiedGroupId);

		Option::set(self::MODULE_ID, self::CHECKER_OPTION.$copiedGroupId, "Y");

		$dataToCopy = [
			"executiveUserId" => $this->executiveUserId,
			"groupId" => $groupId,
			"copiedGroupId" => $copiedGroupId,
			"siteId" => $siteId,
			"copiedSiteId" => $copiedSiteId,
			"folderMapIds" => $folderMapIds,
			"folderIndexIds" => $folderIndexIds
		];
		Option::set(self::MODULE_ID, self::STEPPER_OPTION.$copiedGroupId, serialize($dataToCopy));

		GroupStepper::bind(1);

		$binder = new Landing\Binding\Group($copiedGroupId);
		if (!$binder->isForbiddenBindingAction())
		{
			$binder->bindSite($copiedSiteId);
		}
	}

	private function addToQueue(int $copiedGroupId)
	{
		$option = Option::get(self::MODULE_ID, self::QUEUE_OPTION, "");
		$option = ($option !== "" ? unserialize($option, ['allowed_classes' => false]) : []);
		$option = (is_array($option) ? $option : []);

		$option[] = $copiedGroupId;
		Option::set(self::MODULE_ID, self::QUEUE_OPTION, serialize($option));
	}
}
