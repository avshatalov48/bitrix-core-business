<?php
namespace Bitrix\Blog\Copy;

use Bitrix\Blog\Copy\Implement\BlogComment as BlogCommentImplementer;
use Bitrix\Blog\Copy\Implement\BlogPost as BlogPostImplementer;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Result;

class BlogPostManager
{
	private $executiveUserId;
	private $blogPostIdsToCopy = [];

	private $features = [];
	private $changedRights = [];

	private $result;
	private $mapIdsCopiedPosts = [];

	public function __construct($executiveUserId, array $blogPostIdsToCopy)
	{
		$this->executiveUserId = $executiveUserId;
		$this->blogPostIdsToCopy = $blogPostIdsToCopy;

		$this->result = new Result();
	}

	/**
	 * Writes features to the copy queue.
	 *
	 * @param array $features
	 */
	public function setFeatures(array $features)
	{
		$this->features = array_filter($features);
	}

	public function setChangedRights($changedRights)
	{
		$this->changedRights = $changedRights;
	}

	public function startCopy()
	{
		$containerCollection = $this->getContainerCollection();

		$blogPostImplementer = $this->getBlogPostImplementer();
		$blogPostCopier = $this->getBlogPostCopier($blogPostImplementer);

		$this->result = $blogPostCopier->copy($containerCollection);
		$this->mapIdsCopiedPosts = $blogPostCopier->getMapIdsCopiedEntity();

		return $this->result;
	}

	public function getMapIdsCopiedPosts()
	{
		return $this->mapIdsCopiedPosts;
	}

	private function getContainerCollection()
	{
		$containerCollection = new ContainerCollection();

		foreach ($this->blogPostIdsToCopy as $blogPostId)
		{
			$containerCollection[] = new Container($blogPostId);
		}

		return $containerCollection;
	}

	private function getBlogPostCopier(BlogPostImplementer $blogPostImplementer)
	{
		return new EntityCopier($blogPostImplementer);
	}

	private function getBlogPostImplementer()
	{
		global $USER_FIELD_MANAGER;

		$blogPostImplementer = new BlogPostImplementer();

		$blogPostImplementer->setBlogCommentCopier($this->getBlogCommentCopier());
		$blogPostImplementer->setUserFieldManager($USER_FIELD_MANAGER);
		$blogPostImplementer->setFeatures($this->features);
		$blogPostImplementer->setChangedRights($this->changedRights);
		$blogPostImplementer->setExecutiveUserId($this->executiveUserId);

		return $blogPostImplementer;
	}

	private function getBlogCommentCopier()
	{
		global $USER_FIELD_MANAGER;

		$blogCommentImplementer = new BlogCommentImplementer();

		$blogCommentImplementer->setUserFieldManager($USER_FIELD_MANAGER);
		$blogCommentImplementer->setExecutiveUserId($this->executiveUserId);

		return new EntityCopier($blogCommentImplementer);
	}
}