<?php
namespace Bitrix\Blog\Copy\Implement;

use Bitrix\Blog\Integration\Socialnetwork\Log as SocnetLogIntegration;
use Bitrix\Blog\Item\Post;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Vote\Copy\Manager;

class BlogPost extends Base
{
	const BLOG_POST_COPY_ERROR = "BLOG_POST_COPY_ERROR";

	/**
	 * @var EntityCopier|null
	 */
	private $blogCommentCopier = null;

	private $features = [];
	private $changedRights = [];

	protected $ufEntityObject = "BLOG_POST";
	protected $ufDiskFileField = "UF_BLOG_POST_FILE";

	/**
	 * To copy comments needs comment copier.
	 *
	 * @param EntityCopier $blogCommentCopier Comment copier.
	 */
	public function setBlogCommentCopier(EntityCopier $blogCommentCopier)
	{
		$this->blogCommentCopier = $blogCommentCopier;
	}

	/**
	 * Writes features to the copy queue.
	 *
	 * @param array $features List features.
	 */
	public function setFeatures(array $features)
	{
		$this->features = $features;
	}

	/**
	 * To overwrite access rights to a post in a new entity, you need to specify the identifier of the new entity.
	 *
	 * @param array $changedRights Ration changed id.
	 */
	public function setChangedRights($changedRights)
	{
		$this->changedRights = $changedRights;
	}

	/**
	 * Adds entity.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool Added entity id or false.
	 */
	public function add(Container $container, array $fields)
	{
		$blogPostId = \CBlogPost::add($fields);

		if ($blogPostId)
		{
			$this->addCategory($blogPostId, $fields);
			$this->addNotify($blogPostId, $fields);
		}
		else
		{
			$this->result->addError(new Error("Blog post hasn't been added", self::BLOG_POST_COPY_ERROR));
		}

		return $blogPostId;
	}

	public function update($blogPostId, array $fields)
	{
		return \CBlogPost::update($blogPostId, $fields);
	}

	/**
	 * Returns entity fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$queryObject = \CBlogPost::getlist([], ["ID" => $entityId], false, false, ["*"]);

		return (($fields = $queryObject->fetch()) ? $fields : []);
	}

	/**
	 * Preparing data before creating a new BlogPost.
	 *
	 * @param Container $container
	 * @param array $fields List entity fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		$blogPostId = $fields["ID"];

		$fields["SOCNET_RIGHTS"] = $this->getSocnetRights($blogPostId);

		unset($fields["ID"]);

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $entityId BlogPost id.
	 * @param int $copiedEntityId Copied BlogPost id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $entityId, $copiedEntityId)
	{
		$this->copyUfFields($entityId, $copiedEntityId, $this->ufEntityObject);

		$results = [];

		if (in_array("comments", $this->features))
		{
			$results[] = $this->copyComments($entityId, $copiedEntityId);
		}

		return $this->getResult($results);
	}

	/**
	 * Copies vote.
	 *
	 * @param int $voteId Vote id.
	 * @return int copied vote id.
	 */
	public function copyVote(int $voteId): int
	{
		$copyManager = new Manager([$voteId]);

		if (in_array("voteResult", $this->features))
		{
			$copyManager->setResetVotingResult(false);
		}

		$result = $copyManager->startCopy();

		return $this->getCopiedVoteId($voteId, $result->getData());
	}

	protected function getText($blogPostId)
	{
		$queryObject = \CBlogPost::getlist([], ["ID" => $blogPostId], false, false, ["DETAIL_TEXT"]);

		if ($fields = $queryObject->fetch())
		{
			return ["DETAIL_TEXT", $fields["DETAIL_TEXT"]];
		}
		else
		{
			return ["DETAIL_TEXT", ""];
		}
	}

	private function addCategory($blogPostId, array $fields)
	{
		if (!empty($fields["CATEGORY_ID"]))
		{
			\CBlogPostCategory::deleteByPostID($blogPostId);

			$categoryIds = explode(",", $fields["CATEGORY_ID"]);
			foreach ($categoryIds as $categoryId)
			{
				\CBlogPostCategory::add([
					"BLOG_ID" => $fields["BLOG_ID"],
					"POST_ID" => $blogPostId,
					"CATEGORY_ID" => $categoryId
				]);
			}
		}
	}

	private function getSocnetRights($blogPostId)
	{
		$socnetRights = [];

		$prevSocnetRights = \CBlogPost::getSocNetPerms($blogPostId, false);

		foreach ($prevSocnetRights as $entityPrefix => $entities)
		{
			foreach ($entities as $entityId => $rights)
			{
				if ($entityPrefix != "SG")
				{
					$socnetRights[$entityId] = $entityPrefix.$entityId;
				}
			}
		}

		if ($this->changedRights)
		{
			foreach ($this->changedRights as $entityPrefix => $entitiesRatio)
			{
				foreach ($entitiesRatio as $oldEntityId => $newEntityId)
				{
					$socnetRights[$newEntityId] = $entityPrefix.$newEntityId;
				}
			}
		}

		return ($socnetRights ? $socnetRights : []);
	}

	private function copyComments($blogPostId, $copiedBlogPostId)
	{
		$containerCollection = new ContainerCollection();

		$queryObject = \CBlogComment::getList([], ["POST_ID" => $blogPostId], false, false, ["ID"]);
		while ($blogPostComment = $queryObject->fetch())
		{
			$container = new Container($blogPostComment["ID"]);
			$container->setParentId($copiedBlogPostId);
			$containerCollection[] = $container;
		}

		if (!$containerCollection->isEmpty())
		{
			return $this->blogCommentCopier->copy($containerCollection);
		}

		return new Result();
	}

	private function addNotify($blogPostId, $fields)
	{
		$fields["ID"] = $blogPostId;

		$pathToPost = \COption::getOptionString("socialnetwork", "userblogpost_page",
			"/company/personal/user/#user_id#/blog/#post_id#/", SITE_ID);
		$pathToSmile = \COption::getOptionString("socialnetwork", "smile_page", false, SITE_ID);

		$paramsNotify = [
			"bSoNet" => true,
			"UserID" => $fields["AUTHOR_ID"],
			"allowVideo" => \COption::getOptionString("blog","allow_video", "Y"),
			"PATH_TO_SMILE" => $pathToSmile,
			"PATH_TO_POST" => $pathToPost,
			"user_id" => $fields["AUTHOR_ID"],
			"NAME_TEMPLATE" => \CSite::getNameFormat(false),
			"SITE_ID" => SITE_ID
		];

		$logId = \CBlogPost::notify($fields, [], $paramsNotify);

		if ($logId)
		{
			$eventId = SocnetLogIntegration::EVENT_ID_POST;
			$logFields = ["EVENT_ID" => $eventId];
			if ($post = Post::getById($blogPostId))
			{
				$logFields["TAG"] = $post->getTags();
			}
			\CSocNetLog::update($logId, $logFields);
		}
	}

	private function getCopiedVoteId($voteId, array $result)
	{
		$copiedVoteId = "";
		foreach ($result as $value)
		{
			if (array_key_exists($voteId, $value))
			{
				$copiedVoteId = $value[$voteId];
				break;
			}
		}
		return $copiedVoteId;
	}
}