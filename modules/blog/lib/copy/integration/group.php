<?php
namespace Bitrix\Blog\Copy\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Copy\Integration\Feature;

class Group implements Feature
{
	const MODULE_ID = "blog";
	const QUEUE_OPTION = "BlogGroupQueue";
	const CHECKER_OPTION = "BlogGroupChecker_";
	const STEPPER_OPTION = "BlogGroupStepper_";
	const STEPPER_CLASS = GroupStepper::class;
	const ERROR_OPTION = "BlogGroupError_";

	private $executiveUserId;
	private $features = [];

	public function __construct($executiveUserId = 0, array $features = [])
	{
		$this->executiveUserId = $executiveUserId;
		$this->features = $features;
	}

	public function copy($groupId, $copiedGroupId)
	{
		$blogPostIds = $this->getBlogPostIdsByGroupId($groupId);
		if (!$blogPostIds)
		{
			return;
		}

		$this->addToQueue($copiedGroupId);

		Option::set(self::MODULE_ID, self::CHECKER_OPTION.$copiedGroupId, "Y");

		$queueOption = [
			"executiveUserId" => $this->executiveUserId,
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
			$sonetRights = $this->getSonetBlogPostRights($blogPost["ID"]);
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

	private function getSonetBlogPostRights($blogPostId)
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
		$option = Option::get(self::MODULE_ID, self::QUEUE_OPTION, "");
		$option = ($option !== "" ? unserialize($option, ['allowed_classes' => false]) : []);
		$option = (is_array($option) ? $option : []);

		$option[] = $copiedGroupId;
		Option::set(self::MODULE_ID, self::QUEUE_OPTION, serialize($option));
	}
}