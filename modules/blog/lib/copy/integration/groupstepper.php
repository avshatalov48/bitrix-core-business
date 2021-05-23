<?php
namespace Bitrix\Blog\Copy\Integration;

use Bitrix\Blog\Copy\BlogPostManager;
use Bitrix\Main\Config\Option;
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
			$errorOffset = ($queueOption["errorOffset"] ?: 0);

			$limit = 10;
			$offset = $this->getOffset($copiedGroupId) + $errorOffset;

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

				$result = $copyManager->startCopy();
				if (!$result->isSuccess())
				{
					$queueOption["errorOffset"] += $this->getErrorOffset($copyManager);
					$this->saveQueueOption($queueOption);
				}

				$option["steps"] = $offset;

				return true;
			}
			else
			{
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

	private function getErrorOffset(BlogPostManager $copyManager): int
	{
		$numberIds = count($copyManager->getMapIdsCopiedPosts());
		$numberSuccessIds = count(array_filter($copyManager->getMapIdsCopiedPosts()));
		return $numberIds - $numberSuccessIds;
	}

	protected function getQueue(): array
	{
		return $this->getOptionData($this->queueName);
	}

	protected function setQueue(array $queue): void
	{
		$queueId = (string) current($queue);
		$this->checkerName = (mb_strpos($this->checkerName, $queueId) === false ?
			$this->checkerName.$queueId : $this->checkerName);
		$this->baseName = (mb_strpos($this->baseName, $queueId) === false ?
			$this->baseName.$queueId : $this->baseName);
		$this->errorName = (mb_strpos($this->errorName, $queueId) === false ?
			$this->errorName.$queueId : $this->errorName);
	}

	protected function getQueueOption()
	{
		return $this->getOptionData($this->baseName);
	}

	protected function saveQueueOption(array $data)
	{
		Option::set(static::$moduleId, $this->baseName, serialize($data));
	}

	protected function deleteQueueOption()
	{
		$queue = $this->getQueue();
		$this->setQueue($queue);
		$this->deleteCurrentQueue($queue);
		Option::delete(static::$moduleId, ["name" => $this->checkerName]);
		Option::delete(static::$moduleId, ["name" => $this->baseName]);
	}

	protected function deleteCurrentQueue(array $queue): void
	{
		$queueId = current($queue);
		$currentPos = array_search($queueId, $queue);
		if ($currentPos !== false)
		{
			unset($queue[$currentPos]);
			Option::set(static::$moduleId, $this->queueName, serialize($queue));
		}
	}

	protected function isQueueEmpty()
	{
		$queue = $this->getOptionData($this->queueName);
		return empty($queue);
	}

	protected function getOptionData($optionName)
	{
		$option = Option::get(static::$moduleId, $optionName);
		$option = ($option !== "" ? unserialize($option, ['allowed_classes' => false]) : []);
		return (is_array($option) ? $option : []);
	}

	protected function deleteOption($optionName)
	{
		Option::delete(static::$moduleId, ["name" => $optionName]);
	}
}