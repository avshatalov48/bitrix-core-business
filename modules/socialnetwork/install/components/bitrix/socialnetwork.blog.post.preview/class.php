<?php
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class CSocialnetworkBlogPostPreview extends \CBitrixComponent
{
	const E_BLOG_MODULE_NOT_INSTALLED 		= 10001;
	const E_SOCIALNETWORK_MODULE_NOT_INSTALLED 		= 10003;

	protected function checkRequiredModules()
	{
		if (!Main\Loader::includeModule('blog'))
		{
			throw new Main\SystemException(Loc::getMessage("BLOG_POST_PREVIEW_BLOG_MODULE_NOT_INSTALLED"), self::E_BLOG_MODULE_NOT_INSTALLED);
		}

		if (!Main\Loader::includeModule('socialnetwork'))
		{
			throw new Main\SystemException(Loc::getMessage("BLOG_POST_PREVIEW_SOCIALNETWORK_MODULE_NOT_INSTALLED"), self::E_SOCIALNETWORK_MODULE_NOT_INSTALLED);
		}
	}

	protected function prepareParams()
	{
		$this->arParams["AVATAR_SIZE"] = $this->arParams["AVATAR_SIZE"] ?: 40;
		CSocNetLogComponent::processDateTimeFormatParams($this->arParams);
	}

	protected function prepareData()
	{
		global $CACHE_MANAGER;

		if (strlen(trim($this->arParams["NAME_TEMPLATE"])) <= 0)
			$this->arParams["NAME_TEMPLATE"] = \CSite::GetNameFormat();

		$dbPost = \CBlogPost::GetList(
			array(),
			array("ID" => $this->arParams["postId"]),
			false,
			false,
			array("ID", "BLOG_ID", "PUBLISH_STATUS", "TITLE", "AUTHOR", "ENABLE_COMMENTS", "NUM_COMMENTS", "VIEWS", "CODE", "MICRO", "DETAIL_TEXT", "DATE_PUBLISH", "CATEGORY_ID", "HAS_SOCNET_ALL", "HAS_TAGS", "HAS_IMAGES", "HAS_PROPS", "HAS_COMMENT_IMAGES")
		);
		if($arPost = $dbPost->Fetch())
		{
			// For some reason, blog stores specialchared text.
			$arPost['DETAIL_TEXT'] = htmlspecialcharsback($arPost['DETAIL_TEXT']);
			if($arPost['MICRO'] === 'Y')
				$arPost['TITLE'] = null;

			$parser = new blogTextParser();
			$arPost['PREVIEW_TEXT'] = TruncateText($parser->killAllTags($arPost["DETAIL_TEXT"]), 200);
			$this->arResult['POST'] = $arPost;

			$user = new CUser();
			$this->arResult["arUser"] = $user->GetByID($this->arResult['POST']['AUTHOR'])->Fetch();
			$this->arResult["arUser"]["PERSONAL_PHOTO_file"] = CFile::GetFileArray($this->arResult["arUser"]["PERSONAL_PHOTO"]);
			$this->arResult["arUser"]["PERSONAL_PHOTO_resized"] = CFile::ResizeImageGet(
					$this->arResult["arUser"]["PERSONAL_PHOTO_file"],
					array("width" => $this->arParams["AVATAR_SIZE"], "height" => $this->arParams["AVATAR_SIZE"]),
					BX_RESIZE_IMAGE_EXACT,
					false
			);

			$this->arResult['POST']['AUTHOR_FORMATTED_NAME'] = \CUser::FormatName(
				$this->arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => $this->arResult['POST']['LOGIN'],
					'NAME' => $this->arResult['POST']['NAME'],
					'LAST_NAME' => $this->arResult['POST']['LAST_NAME'],
				),
				true, false
			);
			$this->arResult["POST"]['AUTHOR_PROFILE'] = \CComponentEngine::MakePathFromTemplate(
				$this->arParams["PATH_TO_USER_PROFILE"],
				array("user_id" => $this->arResult['POST']['AUTHOR'])
			);
			$this->arResult["POST"]['AUTHOR_UNIQID'] = 'u_'.$this->randString();

			$this->arResult["POST"]["DATE_FORMATTED"] = CComponentUtil::GetDateTimeFormatted(
				MakeTimeStamp($this->arResult["POST"]["DATE_PUBLISH"]),
				$this->arParams["DATE_TIME_FORMAT"],
				CTimeZone::GetOffset()
			);

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->RegisterTag('blog_post_'.$this->arParams['postId']);
			}
		}
	}

	public function executeComponent()
	{
		$this->checkRequiredModules();

		$this->prepareParams();
		$this->prepareData();
		if($this->arResult['POST']['ID'] > 0)
		{
			$this->includeComponentTemplate();
		}
	}

}