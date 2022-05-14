<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class BlogComment extends Provider
{
	public const PROVIDER_ID = 'BLOG_COMMENT';
	public const CONTENT_TYPE_ID = 'BLOG_COMMENT';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'blog_comment', 'blog_comment_micro' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_COMMENT;
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
				),
				false,
				false,
				array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "SHARE_DEST")
			);

			$comment = $res->fetch();
			if (!$comment)
			{
				return;
			}

			$res = \CBlogPost::getList(
				array(),
				array(
					"ID" => $comment["POST_ID"]
				)
			);

			$post = $res->fetch();
			if (!$post)
			{
				return;
			}

			$checkAccess = ($this->getOption('checkAccess') !== false);
			if (
				$checkAccess
				&& !BlogPost::canRead([
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

			$this->setSourceFields(array_merge($comment, array("POST" => $post)));
			$this->setSourceDescription(htmlspecialcharsback($comment['POST_TEXT']));

			$title = htmlspecialcharsback($comment['POST_TEXT']);
			$title = \Bitrix\Socialnetwork\Helper\Mention::clear($title);

			$p = new \blogTextParser();
			$title = $p->convert($title, false);
			$title = preg_replace([
				"/\n+/is".BX_UTF_PCRE_MODIFIER,
				"/\s+/is".BX_UTF_PCRE_MODIFIER,
				"/&nbsp;+/is".BX_UTF_PCRE_MODIFIER
			], " ", \blogTextParser::killAllTags($title));

			$this->setSourceTitle(truncateText($title, 100));
			$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects());
			$this->setSourceDiskObjects($this->getDiskObjects($commentId, $this->cloneDiskObjects));
			$this->setSourceOriginalText($comment['POST_TEXT']);
			$this->setSourceAuxData($comment);
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		return $this->getEntityAttachedDiskObjects([
			'userFieldEntity' => 'BLOG_COMMENT',
			'userFieldCode' => 'UF_BLOG_COMMENT_FILE',
			'clone' => $clone,
		]);
	}

	public function getLiveFeedUrl(): string
	{
		$pathToPost = \Bitrix\Socialnetwork\Helper\Path::get('userblogpost_page', $this->getSiteId());

		if (
			!empty($pathToPost)
			&& ($comment = $this->getSourceFields())
			&& isset($comment["POST"])
		)
		{
			$pathToPost = \CComponentEngine::makePathFromTemplate($pathToPost, array("post_id" => $comment["POST"]["ID"], "user_id" => $comment["POST"]["AUTHOR_ID"]));
			$pathToPost .= (mb_strpos($pathToPost, '?') === false ? '?' : '&').'commentId='.$comment["ID"].'#com'.$comment["ID"];
		}

		return $pathToPost;
	}

	public function getSuffix(): string
	{
		return '2';
	}
}
