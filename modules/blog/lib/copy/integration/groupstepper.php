<?php
namespace Bitrix\Blog\Copy\Integration;

use Bitrix\Blog\Copy\BlogPostManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

class GroupStepper extends Stepper
{
	protected static $moduleId = "blog";

	protected $queueName = "BlogGroupQueue";
	protected $checkerName = "BlogGroupChecker_";
	protected $baseName = "BlogGroupStepper_";
	protected $errorName = "BlogGroupError_";

	/**
	 * @param array $option
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		try
		{
			$queue = $this->getQueue();
			$this->setQueue($queue);

			$queueOption = $this->getQueueOption();
			if (empty($queueOption))
			{
				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
			}

			$executiveUserId = ($queueOption["executiveUserId"] ?: 0);
			$groupId = ($queueOption["groupId"] ?: 0);
			$copiedGroupId = ($queueOption["copiedGroupId"] ?: 0);

			$limit = 5;
			$offset = $this->getOffset($copiedGroupId);

			$blogPostIds = $this->getBlogPostIdsByGroupId($groupId);
			$count = count($blogPostIds);
			$blogPostIds = array_slice($blogPostIds, $offset, $limit);
			$features = ($queueOption["features"] ?: []);

			if ($blogPostIds)
			{
				$option["count"] = $count;

				$copyManager = new BlogPostManager($executiveUserId, $blogPostIds);
				$copyManager->setChangedRights([
					"SG" => [$groupId => $copiedGroupId]
				]);

				$featuresToBlogPost = [];
				if (in_array("comments", $features))
				{
					$featuresToBlogPost[] = "comments";
				}
				if (in_array("voteResult", $features))
				{
					$featuresToBlogPost[] = "voteResult";
				}

				$copyManager->setFeatures($featuresToBlogPost);

				$copyManager->startCopy();

				$option["steps"] = $offset;

				return true;
			}
			else
			{
				$this->deleteCurrentQueue($queue);
				$this->deleteQueueOption();
				return !$this->isQueueEmpty();
			}
		}
		catch (\Exception $exception)
		{
			$this->writeToLog($exception);
			$this->deleteQueueOption();
			return false;
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

	private function getOffset(int $copiedGroupId): int
	{
		$blogPostIds = $this->getBlogPostIdsByGroupId($copiedGroupId);
		return count($blogPostIds);
	}
}