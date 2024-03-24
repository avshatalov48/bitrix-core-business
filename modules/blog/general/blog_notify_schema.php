<?
IncludeModuleLangFile(__FILE__);

use	\Bitrix\Main\Localization\Loc;

class CBlogNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		$ar = array(
			"post" => Array(
				"NAME" => GetMessage('BLG_NS_POST'),
				"PUSH" => 'Y'
			),
			"post_mail" => Array(
				"NAME" => GetMessage('BLG_NS_POST_MAIL_MSGBER_1'),
				"PUSH" => 'Y'
			),
			"comment" => Array(
				"NAME" => GetMessage('BLG_NS_COMMENT'),
				"PUSH" => 'N'
			),
			"mention" => Array(
				"NAME" => GetMessage('BLG_NS_MENTION'),
				"PUSH" => 'N'
			),
			"mention_comment" => Array(
				"NAME" => GetMessage('BLG_NS_MENTION_COMMENT'),
				"PUSH" => 'Y'
			),
			"share" => Array(
				"NAME" => GetMessage('BLG_NS_SHARE'),
				"PUSH" => 'N'
			),
			"share2users" => Array(
				"NAME" => GetMessage('BLG_NS_SHARE2USERS'),
				"PUSH" => 'Y'
			)
		);

		if (IsModuleInstalled('intranet'))
		{
			$ar["broadcast_post"] = Array(
				"NAME" => GetMessage('BLG_NS_BROADCAST_POST_MSGVER_1'),
				"SITE" => "N",
				"MAIL" => "Y",
				"XMPP" => "N",
				"PUSH" => "Y",
				"DISABLED" => Array(IM_NOTIFY_FEATURE_SITE, IM_NOTIFY_FEATURE_XMPP)
			);
			$ar["grat"] = Array(
				"NAME" => GetMessage('BLG_NS_GRAT'),
				"PUSH" => "Y"
			);
		}

		if (IsModuleInstalled('socialnetwork'))
		{
			$ar["moderate_post"] = Array(
				"NAME" => GetMessage('BLG_NS_MODERATE_POST'),
				"SITE" => "Y",
				"MAIL" => "Y",
				"XMPP" => "N",
				"PUSH" => "N",
				"DISABLED" => Array(IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_PUSH)
			);
			$ar["moderate_comment"] = Array(
				"NAME" => GetMessage('BLG_NS_MODERATE_COMMENT'),
				"SITE" => "Y",
				"MAIL" => "Y",
				"XMPP" => "N",
				"PUSH" => "N",
				"DISABLED" => Array(IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_PUSH)
			);
			$ar["published_post"] = Array(
				"NAME" => GetMessage('BLG_NS_PUBLISHED_POST'),
				"SITE" => "Y",
				"MAIL" => "Y",
				"XMPP" => "N",
				"PUSH" => "N",
				"DISABLED" => Array(IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_PUSH)
			);
			$ar["published_comment"] = Array(
				"NAME" => GetMessage('BLG_NS_PUBLISHED_COMMENT'),
				"SITE" => "Y",
				"MAIL" => "Y",
				"XMPP" => "N",
				"PUSH" => "N",
				"DISABLED" => Array(IM_NOTIFY_FEATURE_XMPP, IM_NOTIFY_FEATURE_PUSH)
			);
		}

		return array(
			"blog" => array(
				"NAME" => GetMessage('BLG_NS_MSGVER_1'),
				"NOTIFY" => $ar,
			),
		);
	}

	public static function CBlogEventsIMCallback($module, $tag, $text, $arNotify)
	{
		if ($module == "blog")
		{
			$text = trim($text);
			if (empty($text))
			{
				return;
			}

			global $USER;

			$currentUserId = $USER->getId();
			$post = false;

			$tagParsed = explode("|", $tag);
			if (in_array($tagParsed[1], array("POST", "COMMENT", "SHARE", "SHARE2USERS", "POST_MENTION", "COMMENT_MENTION")))
			{
				$postId = intval($tagParsed[2]);
				if ($postId > 0)
				{
					$res = \CBlogPost::getList(
						array(),
						array(
							"ID" => $postId
						),
						false,
						false,
						array("ID", "BLOG_ID", "AUTHOR_ID", "BLOG_OWNER_ID", "TITLE")
					);

					$post = $res->fetch();
				}
			}

			if (!$post)
			{
				return Loc::getMessage('BLG_NS_IM_ANSWER_ERROR');
			}

			$blog = CBlog::getById($post["BLOG_ID"]);

			$userIP = CBlogUser::GetUserIP();
			$commentFields = Array(
				"POST_ID" => $post['ID'],
				"BLOG_ID" => $post['BLOG_ID'],
				"TITLE" => '',
				"POST_TEXT" => $text,
				"DATE_CREATE" => convertTimeStamp(time() + CTimeZone::getOffset(), "FULL"),
				"AUTHOR_IP" => $userIP[0],
				"AUTHOR_IP1" => $userIP[1],
				"URL" => $blog["URL"],
				"PARENT_ID" => false,
				"SEARCH_GROUP_ID" => $blog['GROUP_ID'],
				"AUTHOR_ID" => $currentUserId
			);

			$perm = \Bitrix\Blog\Item\Permissions::DENY;

			if($post["AUTHOR_ID"] == $currentUserId)
			{
				$perm = \Bitrix\Blog\Item\Permissions::FULL;
			}
			else
			{
				$postPerm = CBlogPost::getSocNetPostPerms($post["ID"]);
				if ($postPerm > \Bitrix\Blog\Item\Permissions::DENY)
				{
					$perm = CBlogComment::getSocNetUserPerms($post["ID"], $post["AUTHOR_ID"]);
				}
			}

			if ($perm == \Bitrix\Blog\Item\Permissions::DENY)
			{
				return Loc::getMessage('BLG_NS_IM_ANSWER_ERROR');
			}

			if (!\Bitrix\Blog\Item\Comment::checkDuplicate(array(
				'MESSAGE' => $text,
				'BLOG_ID' => $post['BLOG_ID'],
				'POST_ID' => $post['ID'],
				'AUTHOR_ID' => $currentUserId,
			)))
			{
				return Loc::getMessage('BLG_NS_IM_ANSWER_ERROR');
			}

			if ($perm == \Bitrix\Blog\Item\Permissions::PREMODERATE)
			{
				$commentFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;
			}

			if ($commentId = CBlogComment::add($commentFields))
			{
				\Bitrix\Blog\Item\Comment::actionsAfter(array(
					'MESSAGE' => $text,
					'BLOG_ID' => $post["BLOG_ID"],
					'BLOG_OWNER_ID' => $post["BLOG_OWNER_ID"],
					'POST_ID' => $post["ID"],
					'POST_TITLE' => $post["TITLE"],
					'POST_AUTHOR_ID' => $post["AUTHOR_ID"],
					'COMMENT_ID' => $commentId,
					'AUTHOR_ID' => $currentUserId,
				));

				return Loc::getMessage('BLG_NS_IM_ANSWER_SUCCESS');
			}
		}

	}
}