<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class BlogPost extends Provider
{
	public const PROVIDER_ID = 'BLOG_POST';
	public const CONTENT_TYPE_ID = 'BLOG_POST';

	protected static $blogPostClass = \CBlogPost::class;

	public static function getId(): string
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
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		return new BlogComment();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$postId = $this->entityId;

		if ($postId <= 0)
		{
			return;
		}

		if (isset($cache[$postId]))
		{
			$post = $cache[$postId];
		}
		elseif (Loader::includeModule('blog'))
		{
			$res = self::$blogPostClass::getList(
				[],
				[
					'ID' => $postId
				]
			);

			$post = $res->fetch();
			$cache[$postId] = $post;
		}

		if (
			empty($post)
			|| !self::canRead([
				'POST' => $post
			])
		)
		{
			return;
		}

		if (!empty($post['DETAIL_TEXT']))
		{
			$post['DETAIL_TEXT'] = \Bitrix\Main\Text\Emoji::decode($post['DETAIL_TEXT']);
		}

		$this->setSourceFields($post);
		$this->setSourceDescription($post['DETAIL_TEXT']);
		$this->setSourceTitle(truncateText(($post['MICRO'] === 'N' ? $post['TITLE'] : htmlspecialcharsback($post['TITLE'])), 100));
		$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects());
		$this->setSourceDiskObjects($this->getDiskObjects($postId, $this->cloneDiskObjects));
	}

	public function getPinnedTitle()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$post = $this->getSourceFields();
		if (empty($post))
		{
			return $result;
		}

		$result = ($post['MICRO'] === 'N' ? truncateText($post['TITLE'], 100) : '');

		return $result;
	}

	public function getPinnedDescription()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$post = $this->getSourceFields();
		if (empty($post))
		{
			return $result;
		}

		$result = truncateText(str_replace('&#39;', "'", htmlspecialcharsBack(\CTextParser::clearAllTags($post['DETAIL_TEXT']))), 100);
		$result = preg_replace('/^'.(\Bitrix\Main\Application::isUtfMode() ? "\xC2\xA0" : "\xA0").'$/', '', $result);

		if (
			$result === ''
			&& Loader::includeModule('disk')
		)
		{
			$fileNameList = [];
			$res = \Bitrix\Disk\AttachedObject::getList([
				'filter' => [
					'=ENTITY_TYPE' => \Bitrix\Disk\Uf\BlogPostConnector::className(),
					'ENTITY_ID' => $this->entityId
				],
				'select' => [ 'ID', 'FILENAME' => 'OBJECT.NAME' ]
			]);
			foreach ($res as $attachedObjectFields)
			{
				$fileNameList[] = $attachedObjectFields['FILENAME'];
			}
			$result = truncateText(implode(' ', $fileNameList), 100);
		}

		return $result;
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

	public static function canRead($params): bool
	{
		static $blogPostProvider = null;

		if (
			!is_array($params)
			&& (int)$params > 0
		)
		{
			$params = array(
				'POST' => \CBlogPost::getById($params)
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
				$blogPostProvider = new self;
			}

			$permissions = $blogPostProvider->getPermissions($params["POST"]);
			$result = ($permissions > self::PERMISSION_DENY);
		}

		return $result;
	}

	protected function getPermissions(array $post): string
	{
		global $USER;

		$result = self::PERMISSION_DENY;

		if (Loader::includeModule('blog'))
		{
			if((int)$post["AUTHOR_ID"] === (int)$USER->getId())
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

	public function getLiveFeedUrl(BlogPostService $service = null): string
	{
		if ($service === null)
		{
			$service = new BlogPostService();
		}

		$pathToPost = $service->getPathToPost();

		if (
			!empty($pathToPost)
			&& ($post = $this->getSourceFields())
			&& !empty($post)
		)
		{
			$pathToPost = \CComponentEngine::makePathFromTemplate($pathToPost, [
				'post_id' => $post['ID'],
				'user_id' => $post['AUTHOR_ID']
			]);
		}

		return $pathToPost;
	}

	public function getSuffix()
	{
		return '2';
	}
}

class BlogPostService
{
	public function getPathToPost()
	{
		return Option::get('socialnetwork', 'userblogpost_page', '', SITE_ID);
	}
}