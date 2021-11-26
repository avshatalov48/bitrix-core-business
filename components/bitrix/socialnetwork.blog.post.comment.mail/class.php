<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2015 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\ComponentHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CBitrixSocialnetworkBlogPostCommentMailComponent extends CBitrixComponent
{
	const E_BLOG_MODULE_NOT_INSTALLED 		= 10001;
	const E_COMMENT_NOT_FOUND 				= 10002;

	/**
	 * Variable contains comments data
	 *
	 * @var array[] array
	 */

	protected $postId = false;
	protected $authorIdList = [];

	/**
	 * Function implements all the life cycle of the component
	 * @return void
	 */
	public function executeComponent()
	{
		$this->checkRequiredModules();

		$this->arResult = array(
			"AUTHORS" => array(),
			"COMMENTS" => array(),
			"POST" => array(),
			"DESTINATIONS" => array(),
			"POST_URL" => ""
		);

		try
		{
			$this->obtainDataComment();
			$this->obtainDataComments();
			$this->obtainDataPost();
			$this->obtainDataDestinations();
			$this->obtainDataAuthors();
			$this->obtainPostUrl();
		}
		catch(Exception $e)
		{
		}

		Loader::includeModule('mail');

		$this->includeComponentTemplate();
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		Localization\Loc::loadMessages(__FILE__);
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		if (!Loader::includeModule('blog'))
		{
			throw new Main\SystemException(Localization\Loc::getMessage("SBPCM_BLOG_MODULE_NOT_INSTALLED"), self::E_BLOG_MODULE_NOT_INSTALLED);
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			throw new Main\SystemException(Localization\Loc::getMessage("SBPCM_SOCIALNETWORK_MODULE_NOT_INSTALLED"), self::E_SOCIALNETWORK_MODULE_NOT_INSTALLED);
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams["RECIPIENT_ID"] = (isset($arParams["RECIPIENT_ID"]) ? intval($arParams["RECIPIENT_ID"]) : 0);
		$arParams["COMMENT_ID"] = (isset($arParams["COMMENT_ID"]) ? intval($arParams["COMMENT_ID"]) : 0);
		$arParams["POST_ID"] = (isset($arParams["POST_ID"]) ? intval($arParams["POST_ID"]) : 0);
		$arParams["AVATAR_SIZE_COMMENT"] = (
			isset($arParams["AVATAR_SIZE_COMMENT"])
			&& intval($arParams["AVATAR_SIZE_COMMENT"]) > 0
				? intval($arParams["AVATAR_SIZE_COMMENT"])
				: 100
		);
		$arParams["COMMENTS_COUNT"] = (
			isset($arParams["COMMENTS_COUNT"])
			&& intval($arParams["COMMENTS_COUNT"]) > 0
				? intval($arParams["COMMENTS_COUNT"])
				: 3
		);
		$arParams["URL"] = (
			isset($arParams["URL"])
			&& $arParams["URL"] <> ''
				? $arParams["URL"]
				: CComponentEngine::MakePathFromTemplate(
					'/pub/post.php?post_id=#post_id#',
					array(
						"post_id"=> intval($arParams["POST_ID"])
					)
				)
		);

		return $arParams;
	}

	private function obtainDataComment()
	{
		$arResult =& $this->arResult;

		if ($this->arParams["COMMENT_ID"] > 0)
		{
			$arResult["COMMENT"] =  ComponentHelper::getBlogCommentData($this->arParams["COMMENT_ID"], $this->getLanguageId());
		}

		if (empty($arResult["COMMENT"]))
		{
			throw new Main\SystemException(str_replace("#ID#", $this->arParams["COMMENT_ID"], Localization\Loc::getMessage("SBPCM_NO_COMMENT")), self::E_COMMENT_NOT_FOUND);
		}
		else
		{
			if (!in_array((int)$arResult["COMMENT"]["AUTHOR_ID"], $this->authorIdList, true))
			{
				$this->authorIdList[] = (int)$arResult["COMMENT"]["AUTHOR_ID"];
			}
			$this->postId = $arResult["COMMENT"]["POST_ID"];
		}
	}

	private function obtainDataComments()
	{
		$arResult =& $this->arResult;

		if ($this->postId > 0)
		{
			$arResult["COMMENTS"] = ComponentHelper::getBlogCommentListData($this->arParams["POST_ID"], array_merge($this->arParams, array("MAIL" => "Y")), $this->getLanguageId(), $this->authorIdList);
			$arResult["COMMENTS_ALL_COUNT"] = ComponentHelper::getBlogCommentListCount($this->arParams["POST_ID"]);
		}
	}

	private function obtainDataPost()
	{
		$arResult =& $this->arResult;

		if ($this->postId > 0)
		{
			$arResult["POST"] = ComponentHelper::getBlogPostData($this->postId, $this->getLanguageId());
		}

		if (empty($arResult["POST"]))
		{
			throw new Main\SystemException(str_replace("#ID#", $this->postId, Localization\Loc::getMessage("SBPM_NO_POST")), self::E_POST_NOT_FOUND);
		}
		else
		{
			if (!in_array((int)$arResult["POST"]["AUTHOR_ID"], $this->authorIdList, true))
			{
				$this->authorIdList[] = (int)$arResult["POST"]["AUTHOR_ID"];
			}
		}
	}

	private function obtainDataAuthors()
	{
		$arResult =& $this->arResult;

		if (!empty($this->authorIdList))
		{
			foreach ($this->authorIdList as $authorId)
			{
				if ((int)$authorId > 0)
				{
					if (isset($arResult["AUTHORS"][$authorId]))
					{
						continue;
					}

					$arResult["AUTHORS"][$authorId] = ComponentHelper::getBlogAuthorData($authorId, $this->arParams);
				}
			}
		}
	}

	private function obtainDataDestinations()
	{
		$arResult =& $this->arResult;

		if (intval($this->postId) > 0)
		{
			$arResult["DESTINATIONS"] = ComponentHelper::getBlogPostDestinations($this->postId);
		}
	}

	private function obtainPostUrl()
	{
		$arResult =& $this->arResult;
		$arResult["POST_URL"] = $this->arParams["URL"];

		if (
			isset($this->arParams["RECIPIENT_ID"])
			&& intval($this->arParams["RECIPIENT_ID"]) > 0
		)
		{
			$backUrl = ComponentHelper::getReplyToUrl($arResult["POST_URL"], intval($this->arParams["RECIPIENT_ID"]), 'BLOG_POST', $arResult["POST"]["ID"], $arResult["POST"]["BLOG_GROUP_SITE_ID"]);
			if ($backUrl)
			{
				$arResult["POST_URL"] = $backUrl;
			}
		}
	}
}