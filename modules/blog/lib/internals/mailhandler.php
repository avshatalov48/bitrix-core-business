<?php

namespace Bitrix\Blog\Internals;

use Bitrix\Blog\Item\Blog;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config;
use Bitrix\Blog\Item\Permissions;

Loc::loadMessages(__FILE__);

/**
 * Class for incoming mail event handlers
 *
 * Class MailHandler
 * @package Bitrix\Blog\Internals
 */
final class MailHandler
{
	/**
	 * Adds new comment from mail
	 *
	 * @param \Bitrix\Main\Event $event Event.
	 * @return int|false
	 */
	public static function handleReplyReceivedBlogPost(\Bitrix\Main\Event $event)
	{
		$siteId = intval($event->getParameter('site_id'));
		$postId = intval($event->getParameter('entity_id'));
		$userId = intval($event->getParameter('from'));
		$message = trim($event->getParameter('content'));
		$attachments = $event->getParameter('attachments');

		if (
			strlen($message) <= 0
			&& count($attachments) > 0
		)
		{
			$message = Loc::getMessage('BLOG_MAILHANDLER_ATTACHMENTS');
		}

		if (
			$postId <= 0
			|| $userId <= 0
			|| strlen($message) <= 0
		)
		{
			return false;
		}

		$res = \CBlogPost::getList(
			array(),
			array(
				"ID" => $postId
			),
			false,
			false,
			array("BLOG_ID", "AUTHOR_ID", "BLOG_OWNER_ID")
		);

		if (!($blogPost = $res->fetch()))
		{
			return false;
		}

		$perm = BLOG_PERMS_DENY;

		if ($blogPost["AUTHOR_ID"] == $userId)
		{
			$perm = BLOG_PERMS_FULL;
		}
		else
		{
			$postPerm = \CBlogPost::getSocNetPostPerms($postId, false, $userId, $blogPost["AUTHOR_ID"]);
			if ($postPerm > Permissions::DENY)
			{
				$perm = \CBlogComment::getSocNetUserPerms($postId, $blogPost["AUTHOR_ID"], $userId);
			}
		}

		if ($perm == Permissions::DENY)
		{
			return false;
		}

		if (!\Bitrix\Blog\Item\Comment::checkDuplicate(array(
			'MESSAGE' => $message,
			'BLOG_ID' => $blogPost["BLOG_ID"],
			'POST_ID' => $postId,
			'AUTHOR_ID' => $userId,
		)))
		{
			return false;
		}

		$fields = Array(
			"POST_ID" => $postId,
			"BLOG_ID" => $blogPost["BLOG_ID"],
			"TITLE" => '',
			"POST_TEXT" => $message,
			"AUTHOR_ID" => $userId,
			"DATE_CREATE" => convertTimeStamp(time() + \CTimeZone::getOffset(), "FULL")
		);

		if (!empty($siteId))
		{
			$fields["SEARCH_GROUP_ID"] = \Bitrix\Main\Config\Option::get("socialnetwork", "userbloggroup_id", false, $siteId);
		}

		if ($perm == Permissions::PREMODERATE)
		{
			$fields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;
		}

		$ufCode = (
			isModuleInstalled("webdav")
			|| isModuleInstalled("disk")
				? "UF_BLOG_COMMENT_FILE"
				: "UF_BLOG_COMMENT_DOC"
		);
		$fields[$ufCode] = array();

		$type = false;
		$attachmentRelations = array();

		foreach ($attachments as $key => $attachedFile)
		{
			$resultId = \CSocNetLogComponent::saveFileToUF($attachedFile, $type, $userId);
			if ($resultId)
			{
				$fields[$ufCode][] = $resultId;
				$attachmentRelations[$key] = $resultId;
			}
		}

		$fields["POST_TEXT"] = preg_replace_callback(
			"/\[ATTACHMENT\s*=\s*([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER,
			function ($matches) use ($attachmentRelations)
			{
				if (isset($attachmentRelations[$matches[1]]))
				{
					return "[DISK FILE ID=".$attachmentRelations[$matches[1]]."]";
				}
			},
			$fields["POST_TEXT"]
		);

		if (Loader::includeModule('disk'))
		{
			\Bitrix\Disk\Uf\FileUserType::setValueForAllowEdit("BLOG_COMMENT", true);
		}

		$commentId = \CBlogComment::add($fields);

		if ($commentId)
		{
			\Bitrix\Blog\Item\Comment::actionsAfter(array(
				'MESSAGE' => $message,
				'BLOG_ID' => $blogPost["BLOG_ID"],
				'BLOG_OWNER_ID' => $blogPost["BLOG_OWNER_ID"],
				'POST_ID' => $postId,
				'POST_TITLE' => htmlspecialcharsBack($blogPost["TITLE"]),
				'POST_AUTHOR_ID' => $blogPost["AUTHOR_ID"],
				'COMMENT_ID' => $commentId,
				'AUTHOR_ID' => $userId,
			));
		}

		return $commentId;
	}
	/**
	 * Adds new post from mail
	 *
	 * @param \Bitrix\Main\Event $event Event.
	 * @return int|false
	 */
	public static function handleForwardReceivedBlogPost(\Bitrix\Main\Event $event)
	{
		$userId = intval($event->getParameter('from'));
		$message = trim($event->getParameter('content'));
		$subject = trim($event->getParameter('subject'));
		$attachments = $event->getParameter('attachments');
		$siteId = $event->getParameter('site_id');

		if (
			strlen($message) <= 0
			&& count($attachments) > 0
		)
		{
			$message = Loc::getMessage('BLOG_MAILHANDLER_ATTACHMENTS');
		}

		if (
			$userId <= 0
			|| strlen($message) <= 0
			|| strlen($siteId) <= 0
		)
		{
			return false;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			throw new Main\SystemException("Could not load 'socialnetwork' module.");
		}

		$pathToPost = Config\Option::get("socialnetwork", "userblogpost_page", '', $siteId);
		$postId = false;

		$blog = Blog::getByUser(array(
			"GROUP_ID" => Config\Option::get("socialnetwork", "userbloggroup_id", false, $siteId),
			"SITE_ID" => $siteId,
			"USER_ID" => $userId,
			"CREATE" => "Y",
		));

		if ($blog)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			$fields = Array(
				"BLOG_ID" => $blog["ID"],
				"AUTHOR_ID" => $userId,
				"=DATE_CREATE" => $helper->getCurrentDateTimeFunction(),
				"=DATE_PUBLISH" => $helper->getCurrentDateTimeFunction(),
				"MICRO" => "N",
				"TITLE" => $subject,
				"DETAIL_TEXT" => $message,
				"DETAIL_TEXT_TYPE" => "text",
				"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
				"HAS_IMAGES" => "N",
				"HAS_TAGS" => "N",
				"HAS_SOCNET_ALL" => "N",
				"SOCNET_RIGHTS" => array("U".$userId)
			);

			if (strlen($fields["TITLE"]) <= 0)
			{
				$fields["MICRO"] = "Y";
				$fields["TITLE"] = preg_replace("/\[ATTACHMENT\s*=\s*[^\]]*\]/is".BX_UTF_PCRE_MODIFIER, "", \blogTextParser::killAllTags($fields["DETAIL_TEXT"]));
				$fields["TITLE"] = TruncateText(trim(preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", $fields["TITLE"])), 100);
				if(strlen($fields["TITLE"]) <= 0)
				{
					$fields["TITLE"] = Loc::getMessage("BLOG_MAILHANDLER_EMPTY_TITLE_PLACEHOLDER");
				}
			}

			$ufCode = (
				isModuleInstalled("webdav")
				|| isModuleInstalled("disk")
					? "UF_BLOG_POST_FILE"
					: "UF_BLOG_POST_DOC"
			);
			$fields[$ufCode] = array();

			$type = false;
			$attachmentRelations = array();

			foreach ($attachments as $key => $attachedFile)
			{
				$resultId = \CSocNetLogComponent::saveFileToUF($attachedFile, $type, $userId);
				if ($resultId)
				{
					$fields[$ufCode][] = $resultId;
					$attachmentRelations[$key] = $resultId;
				}
			}

			$fields["HAS_PROPS"] = (!empty($attachmentRelations) ? "Y" :"N");

			$fields["DETAIL_TEXT"] = preg_replace_callback(
				"/\[ATTACHMENT\s*=\s*([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER,
				function ($matches) use ($attachmentRelations)
				{
					return (
						isset($attachmentRelations[$matches[1]])
							? "[DISK FILE ID=".$attachmentRelations[$matches[1]]."]"
							: ""
					);
				},
				$fields["DETAIL_TEXT"]
			);

			if (Loader::includeModule('disk'))
			{
				\Bitrix\Disk\Uf\FileUserType::setValueForAllowEdit("BLOG_POST", true);
			}

			$postId = \CBlogPost::add($fields);

			if ($postId)
			{
				BXClearCache(true, "/".$siteId."/blog/last_messages_list/");

				$fields["ID"] = $postId;
				$paramsNotify = array(
					"bSoNet" => true,
					"UserID" => $userId,
					"allowVideo" => "N",
					"PATH_TO_SMILE" => Config\Option::get("socialnetwork", "smile_page", '', $siteId),
					"PATH_TO_POST" => $pathToPost,
					"user_id" => $userId,
					"NAME_TEMPLATE" => \CSite::getNameFormat(null, $siteId),
					"SHOW_LOGIN" => "Y",
					"SEND_COUNTER_TO_AUTHOR" => "Y"
				);
				\CBlogPost::notify($fields, $blog, $paramsNotify);

				if (Loader::includeModule('im'))
				{
					$postUrl = \CComponentEngine::makePathFromTemplate($pathToPost, array(
						"post_id" => $postId,
						"user_id" => $userId
					));

					$processedPathData = \CSocNetLogTools::processPath(array("POST_URL" => $postUrl), $userId, $siteId);
					$serverName = $processedPathData["SERVER_NAME"];
					$postUrl = $processedPathData["URLS"]["POST_URL"];

					\CIMNotify::add(array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
						"NOTIFY_MODULE" => "blog",
						"NOTIFY_EVENT" => "post_mail",
						"NOTIFY_TAG" => "BLOG|POST|".$postId,
						"TO_USER_ID" => $userId,
						"NOTIFY_MESSAGE" => Loc::getMessage("BLOG_MAILHANDLER_NEW_POST", array(
							"#TITLE#" => "<a href=\"".$postUrl."\">".$fields["TITLE"]."</a>"
						)),
						"NOTIFY_MESSAGE_OUT" => Loc::getMessage("BLOG_MAILHANDLER_NEW_POST", array(
								"#TITLE#" => $fields["TITLE"]
							)).' '.$serverName.$postUrl
					));
				}
			}
		}

		return $postId;
	}
}
