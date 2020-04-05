<?php
namespace Bitrix\Forum\Copy;

use Bitrix\Forum\Copy\Implement\Comment as CommentImplementer;
use Bitrix\Forum\Copy\Implement\Topic as TopicImplementer;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;

class TopicManager
{
	private $executiveUserId;
	private $topicIdsToCopy = [];

	public function __construct($executiveUserId, array $topicIdsToCopy)
	{
		$this->executiveUserId = $executiveUserId;
		$this->topicIdsToCopy = $topicIdsToCopy;
	}

	public function startCopy()
	{
		$containerCollection = $this->getContainerCollection();

		$topicCopier = $this->getTopicCopier();

		return $topicCopier->copy($containerCollection);
	}

	private function getContainerCollection()
	{
		$containerCollection = new ContainerCollection();

		foreach ($this->topicIdsToCopy as $topicId)
		{
			$containerCollection[] = new Container($topicId);
		}

		return $containerCollection;
	}

	private function getTopicCopier()
	{
		return new EntityCopier($this->getTopicImplementer());
	}

	private function getTopicImplementer()
	{
		global $USER_FIELD_MANAGER;

		$commentImplementer = new CommentImplementer();
		$commentImplementer->setUserFieldManager($USER_FIELD_MANAGER);
		$commentImplementer->setExecutiveUserId($this->executiveUserId);
		$commentCopier = new EntityCopier($commentImplementer);

		$topicImplementer = new TopicImplementer();
		$topicImplementer->setCommentCopier($commentCopier);

		return $topicImplementer;
	}
}