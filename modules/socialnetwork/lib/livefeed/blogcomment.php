<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class BlogComment extends Provider
{
	const PROVIDER_ID = 'BLOG_COMMENT';
	const TYPE = 'comment';
	const CONTENT_TYPE_ID = 'BLOG_COMMENT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('blog_comment', 'blog_comment_micro');
	}

	public function getType()
	{
		return static::TYPE;
	}

	public function initSourceFields()
	{
		$commentId = $this->entityId;

		if (
			$commentId > 0
			&& Loader::includeModule('blog')
		)
		{
			$res = \CBlogComment::getList(
				array(),
				array(
					"ID" => $commentId
				)
			);

			if ($comment = $res->fetch($commentId))
			{
				$res = \CBlogPost::getList(
					array(),
					array(
						"ID" => $comment["POST_ID"]
					)
				);

				if (
					($post = $res->fetch())
					&& (BlogPost::canRead(array(
						'POST' => $post
					)))
				)
				{
					$this->setSourceFields(array_merge($comment, array("POST" => $post)));
					$this->setSourceDescription(htmlspecialcharsback($comment['POST_TEXT']));

					$title = htmlspecialcharsback($comment['POST_TEXT']);
					$title = preg_replace(
						"/\[USER\s*=\s*([^\]]*)\](.+?)\[\/USER\]/is".BX_UTF_PCRE_MODIFIER,
						"\\2",
						$title
					);
					$p = new \blogTextParser();
					$title = $p->convert($title, false);
					$title = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", \blogTextParser::killAllTags($title));

					$this->setSourceTitle(truncateText($title, 100));
					$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects());
					$this->setSourceDiskObjects(self::getDiskObjects($commentId, $this->cloneDiskObjects));
				}
			}
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		global $USER_FIELD_MANAGER;
		static $cache = array();

		$commentId = $this->entityId;

		$result = array();
		$cacheKey = $commentId.$clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$commentUF = $USER_FIELD_MANAGER->getUserFields("BLOG_COMMENT", $commentId, LANGUAGE_ID);
			if (
				!empty($commentUF['UF_BLOG_COMMENT_FILE'])
				&& !empty($commentUF['UF_BLOG_COMMENT_FILE']['VALUE'])
				&& is_array($commentUF['UF_BLOG_COMMENT_FILE']['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($commentUF['UF_BLOG_COMMENT_FILE']['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $commentUF['UF_BLOG_COMMENT_FILE']['VALUE'];
				}
			}
		}

		return $result;
	}

	public function getLiveFeedUrl()
	{
		$pathToPost = Option::get('socialnetwork', 'userblogpost_page', '', $this->getSiteId());

		if (
			!empty($pathToPost)
			&& ($comment = $this->getSourceFields())
			&& isset($comment["POST"])
		)
		{
			$pathToPost = \CComponentEngine::makePathFromTemplate($pathToPost, array("post_id" => $comment["POST"]["ID"], "user_id" => $comment["POST"]["AUTHOR_ID"]));
			$pathToPost .= (strpos($pathToPost, '?') === false ? '?' : '&').'commentId='.$comment["ID"].'#com'.$comment["ID"];
		}

		return $pathToPost;
	}

}