<?php
namespace Bitrix\Blog\Copy\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Copy\Integration\Feature;
use Bitrix\Socialnetwork\Copy\Integration\Helper;

class Group implements Feature, Helper
{
	private $stepper;

	private $executiveUserId;
	private $features = [];

	private $moduleId = "blog";
	private $queueOption = "BlogGroupQueue";
	private $checkerOption = "BlogGroupChecker_";
	private $stepperOption = "BlogGroupStepper_";
	private $errorOption = "BlogGroupError_";

	public function __construct($executiveUserId = 0, array $features = [])
	{
		$this->executiveUserId = $executiveUserId;
		$this->features = $features;

		$this->stepper = GroupStepper::class;
	}

	public function copy($groupId, $copiedGroupId)
	{
		$blogPostIds = $this->getBlogPostIdsByGroupId($groupId);
		if (!$blogPostIds)
		{
			return;
		}

		$this->addToQueue($copiedGroupId);

		Option::set($this->moduleId, $this->checkerOption.$copiedGroupId, "Y");

		$queueOption = [
			"executiveUserId" => $this->executiveUserId,
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

	private function getBlogPostIdsByGroupId($groupId)
	{
		$blogPostIds = [];

		$queryObject = \CBlogPost::getList([], ["SOCNET_GROUP_ID" => $groupId]);
		while ($blogPost = $queryObject->fetch())
		{
			$blogPostIds[] = $blogPost["ID"];
		}

		return $blogPostIds;
	}

	// use it if need attach posts to another group
	private function attachGroupToPost($groupIdToCopy, $copiedGroupId)
	{
		$blogPosts = $this->getBlogPosts($groupIdToCopy);

		foreach ($blogPosts as $blogPost)
		{
			$sonetRights = $this->getsonetBlogPostRights($blogPost["ID"]);
			$sonetRights[] = "SG".$copiedGroupId;
			$newBlogPostRights = ["SG".$copiedGroupId];
			ComponentHelper::processBlogPostShare(
				[
					"POST_ID" => $blogPost["ID"],
					"BLOG_ID" => $blogPost["BLOG_ID"],
					"SITE_ID" => SITE_ID,
					"SONET_RIGHTS" => $sonetRights,
					"NEW_RIGHTS" => $newBlogPostRights,
					"USER_ID" => $this->executiveUserId
				],
				[]
			);
		}

		return new Result();
	}

	private function getBlogPosts($groupId)
	{
		$blogPosts = [];

		$queryObject = \CBlogPost::getList([], ["SOCNET_GROUP_ID" => $groupId]);
		while ($blogPost = $queryObject->fetch())
		{
			$blogPosts[] = $blogPost;
		}

		return $blogPosts;
	}

	private function getsonetBlogPostRights($blogPostId)
	{
		$currentRights = [];

		foreach (\CBlogPost::getSocNetPerms($blogPostId) as $type => $val)
		{
			foreach ($val as $id => $values)
			{
				if ($type != "U")
				{
					$currentRights[] = $type.$id;
				}
				else
				{
					$currentRights[] = (in_array("US".$id, $values) ? "UA" : $type.$id);
				}
			}
		}

		return $currentRights;
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