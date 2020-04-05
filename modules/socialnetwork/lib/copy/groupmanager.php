<?php
namespace Bitrix\Socialnetwork\Copy;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Socialnetwork\Copy\Implement\UserGroupHelper;
use Bitrix\Socialnetwork\Copy\Integration\Feature;
use Bitrix\Socialnetwork\Copy\UserToGroup as UserToGroupCopier;
use Bitrix\Socialnetwork\Copy\Implement\Group as GroupImplementer;
use Bitrix\Socialnetwork\Copy\Implement\UserToGroup as UserToGroup;

class GroupManager
{
	private $executiveUserId;
	private $groupIdsToCopy = [];

	private $changedFields = [];

	/**
	 * @var Feature[]
	 */
	private $features = [];

	private $ufIgnoreList = [];

	private $projectTerm = [];

	private $markerUsers = true;

	public function __construct($executiveUserId, array $groupIdsToCopy)
	{
		$this->executiveUserId = $executiveUserId;
		$this->groupIdsToCopy = $groupIdsToCopy;
	}

	/**
	 * Writes feature implementer to the copy queue.
	 *
	 * @param Feature $feature Feature implementer.
	 */
	public function setFeature(Feature $feature)
	{
		$this->features[] = $feature;
	}

	/**
	 * To avoid copying specific fields, specify a list of fields to ignore.
	 *
	 * @param array $ufIgnoreList Ignore list.
	 */
	public function setUfIgnoreList(array $ufIgnoreList): void
	{
		$this->ufIgnoreList = $ufIgnoreList;
	}

	/**
	 * Setting the start date of a project to update dates in entities.
	 *
	 * @param array $projectTerm ["project" => true, "start_point" => "", "end_point" => ""].
	 */
	public function setProjectTerm(array $projectTerm)
	{
		$this->projectTerm = $projectTerm;
	}

	public function setMarkerUsers(bool $markerUsers): void
	{
		$this->markerUsers = $markerUsers;
	}

	public function setChangedFields($changedFields)
	{
		$this->changedFields = array_merge($this->changedFields, $changedFields);
	}

	public function startCopy()
	{
		$containerCollection = $this->getContainerCollection();

		$groupImplementer = $this->getGroupImplementer();
		$groupCopier = $this->getGroupCopier($groupImplementer);

		if ($this->markerUsers)
		{
			$userToGroupImplementer = $this->getUserToGroupImplementer();
			$groupCopier->addEntityToCopy($this->getUserToGroupCopier($userToGroupImplementer));
		}

		return $groupCopier->copy($containerCollection);
	}

	private function getContainerCollection()
	{
		$containerCollection = new ContainerCollection();

		foreach ($this->groupIdsToCopy as $groupId)
		{
			$containerCollection[] = new Container($groupId);
		}

		return $containerCollection;
	}

	private function getGroupImplementer()
	{
		global $USER_FIELD_MANAGER;

		//todo application to implementer for get errors
		$groupImplementer = new GroupImplementer($this->executiveUserId);
		$groupImplementer->setChangedFields($this->changedFields);
		$groupImplementer->setUserFieldManager($USER_FIELD_MANAGER);
		$groupImplementer->setUfIgnoreList($this->ufIgnoreList);
		$groupImplementer->setExecutiveUserId($this->executiveUserId);
		$groupImplementer->setProjectTerm($this->projectTerm);

		if (!$this->markerUsers && $this->changedFields["MODERATORS"])
		{
			$userGroupHelper = new UserGroupHelper($this->executiveUserId, $this->changedFields["MODERATORS"]);
			$groupImplementer->setUserGroupHelper($userGroupHelper);
		}

		foreach ($this->features as $feature)
		{
			$groupImplementer->setFeature($feature);
		}

		return $groupImplementer;
	}

	private function getGroupCopier($groupImplementer)
	{
		return new EntityCopier($groupImplementer);
	}

	private function getUserToGroupImplementer()
	{
		$userGroupImplementer = new UserToGroup();
		$userGroupImplementer->setUfIgnoreList($this->ufIgnoreList);
		if ($this->changedFields["MODERATORS"])
		{
			$userGroupHelper = new UserGroupHelper($this->executiveUserId, $this->changedFields["MODERATORS"]);
			$userGroupImplementer->setUserGroupHelper($userGroupHelper);
		}
		return $userGroupImplementer;
	}

	private function getUserToGroupCopier($userToGroupImplementer)
	{
		return new UserToGroupCopier($userToGroupImplementer);
	}
}