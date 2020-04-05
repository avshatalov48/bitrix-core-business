<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class BlogPost extends Provider
{
	const PROVIDER_ID = 'BLOG_POST';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'BLOG_POST';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		$result = array('blog_post', 'blog_post_important', 'blog_post_micro');
		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$result[] = 'blog_post_grat';
		}
		if (ModuleManager::isModuleInstalled('vote'))
		{
			$result[] = 'blog_post_vote';
		}

		return $result;
	}

	public function getType()
	{
		return static::TYPE;
	}

	public function initSourceFields()
	{
		$postId = $this->entityId;

		if (
			$postId > 0
			&& Loader::includeModule('blog')
		)
		{
			$res = \CBlogPost::getList(
				array(),
				array(
					"ID" => $postId
				)
			);
			if (
				($post = $res->fetch())
				&& (self::canRead(array(
					'POST' => $post
				)))
			)
			{
				$this->setSourceFields($post);
				$this->setSourceDescription($post['DETAIL_TEXT']);
				$this->setSourceTitle(truncateText(($post['MICRO'] == 'N' ? $post['TITLE'] : htmlspecialcharsback($post['TITLE'])), 100));
				$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects());
				$this->setSourceDiskObjects($this->getDiskObjects($postId, $this->cloneDiskObjects));
			}
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		global $USER_FIELD_MANAGER;
		static $cache = array();

		$postId = $this->entityId;

		$result = array();
		$cacheKey = $postId.$clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$postUF = $USER_FIELD_MANAGER->getUserFields("BLOG_POST", $postId, LANGUAGE_ID);
			if (
				!empty($postUF['UF_BLOG_POST_FILE'])
				&& !empty($postUF['UF_BLOG_POST_FILE']['VALUE'])
				&& is_array($postUF['UF_BLOG_POST_FILE']['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($postUF['UF_BLOG_POST_FILE']['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $postUF['UF_BLOG_POST_FILE']['VALUE'];
				}
			}
		}

		return $result;
	}

	public static function canRead($params)
	{
		static $blogPostProvider = null;

		if (
			!is_array($params)
			&& intval($params) > 0
		)
		{
			$params = array(
				'POST' => \CBlogPost::getByID($params)
			);
		}

		$result = false;
		if (
			isset($params["POST"])
			&& is_array($params["POST"])
		)
		{
			if ($blogPostProvider === null)
			{
				$blogPostProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
			}

			$permissions = $blogPostProvider->getPermissions($params["POST"]);
			$result = ($permissions > self::PERMISSION_DENY);
		}

		return $result;
	}

	protected function getPermissions(array $post)
	{
		global $USER;

		$result = self::PERMISSION_DENY;

		if (Loader::includeModule('blog'))
		{
			if($post["AUTHOR_ID"] == $USER->getId())
			{
				$result = self::PERMISSION_FULL;
			}
			else
			{
				$perms = \CBlogPost::getSocNetPostPerms(array(
					"POST_ID" => $post["ID"],
					"NEED_FULL" => true,
					"USER_ID" => false,
					"POST_AUTHOR_ID" => $post["AUTHOR_ID"],
					"PUBLIC" => false,
					"LOG_ID" => false
				));

				if ($perms >= BLOG_PERMS_FULL)
				{
					$result = self::PERMISSION_FULL;
				}
				elseif ($perms >= BLOG_PERMS_READ)
				{
					$result = self::PERMISSION_READ;
				}
			}
		}

		return $result;
	}

	public function getLiveFeedUrl()
	{
		$pathToPost = Option::get('socialnetwork', 'userblogpost_page', '', SITE_ID);
		if (
			!empty($pathToPost)
			&& ($post = $this->getSourceFields())
			&& !empty($post)
		)
		{
			$pathToPost = \CComponentEngine::makePathFromTemplate($pathToPost, array("post_id" => $post["ID"], "user_id" => $post["AUTHOR_ID"]));
		}

		return $pathToPost;
	}
}