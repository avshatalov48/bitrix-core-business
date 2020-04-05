<?
namespace Bitrix\Vote\Attachment;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class BlogPostConnector extends Connector
{
	private $canRead = null;
	private $canEdit = null;
	private static $permissions = array();
	private static $posts = array();

	private static function getPostData($entityId)
	{
		if (array_key_exists($entityId, self::$posts))
			return self::$posts[$entityId];
		$cacheTtl = 2592000;
		$cacheId = 'blog_post_socnet_general_' . $entityId . '_' . LANGUAGE_ID;
		$timezoneOffset = \CTimeZone::getOffset();
		if($timezoneOffset != 0)
		{
			$cacheId .= "_" . $timezoneOffset;
		}
		$cacheDir = '/blog/socnet_post/gen/' . intval($entityId / 100) . '/' . $entityId;

		$cache = new \CPHPCache;
		if ($cache->initCache($cacheTtl, $cacheId, $cacheDir))
		{
			$post = $cache->getVars();
		}
		else
		{
			$cache->startDataCache();
			$post = \CBlogPost::getList(array(), array("ID" => $entityId), false, false, array(
				"ID",
				"BLOG_ID",
				"BLOG_OWNER_ID",
				"PUBLISH_STATUS",
				"TITLE",
				"AUTHOR_ID",
				"ENABLE_COMMENTS",
				"NUM_COMMENTS",
				"VIEWS",
				"CODE",
				"MICRO",
				"DETAIL_TEXT",
				"DATE_PUBLISH",
				"CATEGORY_ID",
				"HAS_SOCNET_ALL",
				"HAS_TAGS",
				"HAS_IMAGES",
				"HAS_PROPS",
				"HAS_COMMENT_IMAGES"
			))->fetch();
			$cache->endDataCache($post);
		}
		self::$posts[$entityId] = $post;
		return $post;
	}

	private function getPermission($userId)
	{
		global $APPLICATION;

		if (!Loader::includeModule('socialnetwork'))
			return false;
		elseif (
			$APPLICATION->getGroupRight("blog") >= "W"
			|| \CSocNetUser::isCurrentUserModuleAdmin()
		)
		{
			self::$permissions[$this->entityId] = BLOG_PERMS_FULL;
		}
		else if (!array_key_exists($this->entityId, self::$permissions))
		{
			self::$permissions[$this->entityId] = BLOG_PERMS_DENY;
			$post = self::getPostData($this->entityId);
			if ($post && $post["ID"] > 0)
			{
				$p = \CBlogPost::getSocNetPostPerms($this->entityId, true, $userId, $post["AUTHOR_ID"]);
				if ($p > BLOG_PERMS_MODERATE || ($p >= BLOG_PERMS_WRITE && $post["AUTHOR_ID"] == $userId))
					$p = BLOG_PERMS_FULL;
				self::$permissions[$this->entityId] = $p;
			}

		}
		return self::$permissions[$this->entityId];
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function checkFields(&$data)
	{
		$post = self::getPostData($this->entityId);
		if ($post)
		{
			$data["TITLE"] = $post["TITLE"];
			$data["URL"] = str_replace(
				array("#user_id#", "#post_id#"),
				array($post["BLOG_OWNER_ID"], $post["ID"]),
				\COption::GetOptionString("socialnetwork", "userblogpost_page")
			);
		}
		return $data;
	}
	/**
	 * @param integer $userId User ID.
	 * @return bool
	 */
	public function canRead($userId)
	{
		if(is_null($this->canRead))
			$this->canRead = $this->getPermission($userId) >= BLOG_PERMS_READ;

		return $this->canRead;
	}

	/**
	 * @param integer $userId User ID.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		if(is_null($this->canEdit))
			$this->canEdit = $this->getPermission($userId) > BLOG_PERMS_MODERATE;

		return $this->canEdit;
	}
}
