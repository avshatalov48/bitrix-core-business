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

class CBitrixSocialnetworkBlogPostMailComponent extends CBitrixComponent
{
	const E_BLOG_MODULE_NOT_INSTALLED = 10001;
	const E_POST_NOT_FOUND = 10002;
	const E_SOCIALNETWORK_MODULE_NOT_INSTALLED = 10003;

	/**
	 * Variable contains posts data
	 *
	 * @var array[] array
	 */

	protected $authorId = false;
	protected $postId = false;

	/**
	 * Function implements all the life cycle of the component
	 * @return void
	 */
	public function executeComponent()
	{
		$this->checkRequiredModules();

		$this->arResult = array(
			"AUTHOR" => array(),
			"POST" => array(),
			"DESTINATIONS" => array(),
			"POST_URL" => ""
		);

		try
		{
			$this->obtainDataPost();
			$this->obtainDataAuthor();
			$this->obtainDataDestinations();
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
			throw new Main\SystemException(Localization\Loc::getMessage("SBPM_BLOG_MODULE_NOT_INSTALLED"), self::E_BLOG_MODULE_NOT_INSTALLED);
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			throw new Main\SystemException(Localization\Loc::getMessage("SBPM_SOCIALNETWORK_MODULE_NOT_INSTALLED"), self::E_SOCIALNETWORK_MODULE_NOT_INSTALLED);
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams["RECIPIENT_ID"] = (isset($arParams["RECIPIENT_ID"]) ? intval($arParams["RECIPIENT_ID"]) : 0);
		$arParams["POST_ID"] = (isset($arParams["POST_ID"]) ? intval($arParams["POST_ID"]) : 0);
		$arParams["AVATAR_SIZE"] = (isset($arParams["AVATAR_SIZE"]) ? intval($arParams["AVATAR_SIZE"]) : 58);
		$arParams["URL"] = (
			isset($arParams["URL"])
			&& strlen($arParams["URL"]) > 0
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

	private function obtainDataPost()
	{
		$arResult =& $this->arResult;

		if ($this->arParams["POST_ID"] > 0)
		{
			$arResult["POST"] = ComponentHelper::getBlogPostData($this->arParams["POST_ID"], $this->getLanguageId());
		}

		if (empty($arResult["POST"]))
		{
			throw new Main\SystemException(str_replace("#ID#", $this->arParams["POST_ID"], Localization\Loc::getMessage("SBPM_NO_POST")), self::E_POST_NOT_FOUND);
		}
		else
		{
			$this->postId = $arResult["POST"]["ID"];
			$this->authorId = $arResult["POST"]["AUTHOR_ID"];
		}
	}

	private function obtainDataAuthor()
	{
		$arResult =& $this->arResult;

		if (intval($this->authorId) > 0)
		{
			$arResult["AUTHOR"] = ComponentHelper::getBlogAuthorData($this->authorId, $this->arParams);
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