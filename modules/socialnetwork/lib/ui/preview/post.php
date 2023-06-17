<?php
namespace Bitrix\Socialnetwork\Ui\Preview;

use Bitrix\Im\User;
use Bitrix\Main\Loader;

class Post
{
	/**
	 * Returns HTML code for blog post preview.
	 * @param array $params Expected keys: postId, userId.
	 * @return string
	 */
	public static function buildPreview(array $params)
	{
		global $APPLICATION;
		if(!Loader::includeModule('blog'))
			return null;

		ob_start();
		$APPLICATION->includeComponent(
			'bitrix:socialnetwork.blog.post.preview',
			'',
			$params
		);
		return ob_get_clean();
	}

	/**
	 * Returns attach to display in the messenger.
	 * @param array $params Expected keys: postId, userId
	 * @return \CIMMessageParamAttach | false
	 */
	public static function getImAttach(array $params)
	{
		if (!Loader::includeModule('im'))
			return false;

		if (!Loader::includeModule('blog'))
			return false;

		$cursor = \CBlogPost::getList(
			array(),
			array("ID" => $params["postId"]),
			false,
			false,
			array("ID", "BLOG_ID", "PUBLISH_STATUS", "TITLE", "AUTHOR", "ENABLE_COMMENTS", "NUM_COMMENTS", "VIEWS", "CODE", "MICRO", "DETAIL_TEXT", "DATE_PUBLISH", "CATEGORY_ID", "HAS_SOCNET_ALL", "HAS_TAGS", "HAS_IMAGES", "HAS_PROPS", "HAS_COMMENT_IMAGES")
		);
		$post = $cursor->fetch();
		if(!$post)
			return false;

		// For some reason, blog stores specialchared text.
		$post['DETAIL_TEXT'] = htmlspecialcharsback($post['DETAIL_TEXT']);
		if ($post['MICRO'] === 'Y')
			$post['TITLE'] = null;

		$parser = new \blogTextParser();
		$post['PREVIEW_TEXT'] = TruncateText($parser->killAllTags($post["DETAIL_TEXT"]), 200);
		$user = User::getInstance($post['AUTHOR']);

		$attach = new \CIMMessageParamAttach(1, '#E30000');
		$attach->addUser(array(
			'NAME' => $user->getFullName(),
			'AVATAR' => $user->getAvatar(),
		));

		if($post['TITLE'] != '')
		{
			$attach->addMessage('[b]' . $post['TITLE'] . '[/b]');
		}
		$attach->addMessage($post['PREVIEW_TEXT']);

		return $attach;
	}

	public static function getImRich(array $params)
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		if (!Loader::includeModule('blog'))
		{
			return false;
		}

		if (!class_exists('\Bitrix\Im\V2\Entity\Url\RichData'))
		{
			return false;
		}

		$cursor = \CBlogPost::getList(
			[],
			['ID' => $params['postId']],
			false,
			false,
			['TITLE', 'MICRO', 'DETAIL_TEXT']
		);
		$post = $cursor->fetch();
		if(!$post)
		{
			return false;
		}

		// For some reason, blog stores specialchared text.
		$post['DETAIL_TEXT'] = htmlspecialcharsback($post['DETAIL_TEXT']);
		if ($post['MICRO'] === 'Y')
		{
			$post['TITLE'] = null;
		}

		$parser = new \blogTextParser();
		$post['PREVIEW_TEXT'] = TruncateText($parser->killAllTags($post['DETAIL_TEXT']), 200);

		$rich = new \Bitrix\Im\V2\Entity\Url\RichData();

		return $rich
			->setName($post['TITLE'])
			->setDescription($post['PREVIEW_TEXT'])
			->setType(\Bitrix\Im\V2\Entity\Url\RichData::POST_TYPE)
		;
	}

	/**
	 * Returns true if current user has read access to the blog post.
	 * @param array $params Allowed keys: postId, userId.
	 * @param int $userId Current user's id.
	 * @return bool
	 */
	public static function checkUserReadAccess(array $params, $userId)
	{
		if(!Loader::includeModule('blog'))
			return false;

		$permissions = \CBlogPost::getSocNetPostPerms($params['postId'], true, $userId);
		return ($permissions >= BLOG_PERMS_READ);
	}

}