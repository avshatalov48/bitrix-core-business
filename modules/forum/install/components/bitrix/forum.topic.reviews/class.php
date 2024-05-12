<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Forum\Permission;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Forum;

Loc::loadMessages(__FILE__);

final class ForumTopicReviewsComponent extends CBitrixComponent implements Main\Engine\Contract\Controllerable
{
	/**
	 * @var Main\ErrorCollection
	 */
	protected $errorCollection;
	/**
	 * @var Forum\Forum
	 */
	protected $forum;
	/**
	 * @var Forum\User
	 */
	protected $user;
	/** @var Forum\Topic|null */
	protected $topic;
	/**
	 * @var Main\Data\Cache
	 */
	protected $cache;
	protected $cachePath;
	protected $isAjaxMode = false;

	public function __construct($component = null)
	{
		parent::__construct($component);
		Main\Loader::includeModule('forum');
		$this->errorCollection = new Main\ErrorCollection();
		$this->cache = Main\Data\Cache::createInstance();
		$this->cachePath = str_replace([":", "//"], "/", "/".SITE_ID."/".$this->getName()."/");
	}

	private function initCache($id, $path = "")
	{
		$tzOffset = CTimeZone::GetOffset();
		$id .= ($tzOffset <> 0 ? "_".$tzOffset : "");
		if ($this->cache->initCache($this->arParams["CACHE_TIME"], $id, $this->cachePath.$path))
		{
			return $this->cache->getVars();
		}
		return false;
	}

	private function setCache($id, $path, $data)
	{
		if ($this->arParams["CACHE_TIME"] > 0 && $this->arResult["FORUM_TOPIC_ID"] > 0)
		{
			$tzOffset = CTimeZone::GetOffset();
			$id .= ($tzOffset <> 0 ? "_".$tzOffset : "");
			$this->cache->startDataCache($this->arParams["CACHE_TIME"], $id, $this->cachePath.$path);
			CForumCacheManager::SetTag($this->cachePath.$path, "forum_topic_".$this->arResult["FORUM_TOPIC_ID"]);
			$this->cache->endDataCache($data);
		}
	}

	public function executeComponent()
	{
		if (!Main\Loader::includeModule("forum") ||
			!Main\Loader::includeModule("iblock"))
		{
			return false;
		}

		try
		{
			$this->arParams = $this->checkParams($this->arParams);

			if ($this->arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"] > 0)
			{
				$this->topic = Forum\Topic::getById($this->arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
			}

			//region Collect data block
			$sites = $this->forum->getSites();
			$this->arResult["FORUM"] = $this->forum->getData() + ["PATH2FORUM_MESSAGE" => $sites[SITE_ID]];
			$this->arResult["FORUM_TOPIC_ID"] = $this->topic ? $this->topic->getId() : 0;
			$this->arResult["MESSAGES"] = [];
			$this->arResult["MESSAGE_VIEW"] = [];
			$this->arResult["MESSAGE"] = [];
			$this->arResult["FILES"] = [];
			//data for form
			$this->arResult["REVIEW_AUTHOR"] = $this->user->getName();
			$this->arResult["REVIEW_USE_SMILES"] = "Y";
			$this->arResult["REVIEW_EMAIL"] = "";
			$this->arResult["REVIEW_TEXT"] = "";
			$this->arResult["REVIEW_FILES"] = [];
			//endregion

			//region Action block
			if ($result = $this->checkPostActions())
			{
				if (!$result->isSuccess())
				{
					$this->errorCollection->add($result->getErrors());
				}
				else if ($result instanceof Main\ORM\Data\AddResult)
				{
					if (!$this->isAjaxMode)
					{
						$url = (new Main\Web\Uri($this->request->get("back_page") ?: $this->request->getRequestUri()))
							->deleteParams(["ACTION", "sessid", "PAGE_NAME", "FID", "TID", "MID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID, "result", "AJAX_CALL", "bxajaxid"])
							->getLocator();
						$message = Forum\Message::getById($result->getId());
						$url = ForumAddPageParams($url, ["MID" => $result->getId(), "result" => ($message["APPROVED"] === "Y" ? "reply" : "not_approved")], true, false);
						LocalRedirect($url);
					}
					else
					{
						$this->arResult["RESULT"] = $result->getId();
					}
				}
			}
			else if ($result = $this->checkModerateActions())
			{
				if ($this->isAjaxMode)
				{
					$this->sendJsonResponse([
						"status" => $result->isSuccess(),
						"message" => implode(" ", $result->getErrorMessages())
					]);
				}
				else if (!$result->isSuccess())
				{
					$this->errorCollection->add($result->getErrors());
				}
				else
				{
					$url = (new Main\Web\Uri($this->request->getRequestUri()))
						->deleteParams(["REVIEW_ACTION", "sessid", "PAGE_NAME", "FID", "TID", "MID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID, "result", "AJAX_CALL", "bxajaxid"])
						->getLocator();
					LocalRedirect($url);
				}
			}
			//endregion

			$this->adaptForCustomTemplates();
			$this->markUserInTopic();
			if ($this->arParams["SHOW_SUBSCRIBE"] == "Y")
			{
				$this->fillUserSubscribeData();
			}

			if ($this->topic)
			{
				$this->fillTopicData();
			}

			if ($this->arParams["SHOW_FORM"] !== "N")
			{
				$this->fillFormData($result);
			}

			return $this->__includeComponent();
		}
		catch (Main\AccessDeniedException $exception)
		{
			//Do nothing
		}
		catch(\Throwable $e)
		{
			ShowError($e->getMessage());
		}

		return false;
	}

	protected function checkParams($arParams)
	{
		//region check Critical Params
		$arParams["FORUM_ID"] = intval($arParams["FORUM_ID"]);
		$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
		if ($arParams["FORUM_ID"] <= 0)
		{
			throw new Main\ArgumentNullException("Forum ID");
		}
		$this->forum = new Bitrix\Forum\Forum($arParams["FORUM_ID"]);

		if ($arParams["ELEMENT_ID"] <= 0)
		{
			global $ID; // very obsolete alias for Element ID
			$arParams["ELEMENT_ID"] = intval($ID > 0 ? $ID : $this->request->get("ELEMENT_ID"));
		}
		if ($arParams["ELEMENT_ID"] <= 0)
		{
			throw new Main\ArgumentNullException("Iblock Element ID");
		}
		if (!($this->arResult["ELEMENT"] = $this->getIblockElement($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"])))
		{
			throw new Main\ObjectNotFoundException("Iblock element");
		}

		global $USER;
		$this->user = Forum\User::getById($USER->GetID());
		if ($this->user->getPermissionOnForum($this->forum->getId()) < Forum\Permission::CAN_READ)
		{
			throw new Main\AccessDeniedException();
		}
		// endregion check Critical Params

		//region check Main Params
		$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
		foreach ([
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"detail" => "PAGE_NAME=detail&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"
		] as $pageName => $url)
		{
			$key = "URL_TEMPLATES_".mb_strtoupper($pageName);
			if (array_key_exists($key, $arParams))
			{
				$arParams["~".$key] = $arParams[$key];
			}
			elseif ($key === "read")
			{
				$siteUrls = $this->forum->getSites();
				if (array_key_exists(SITE_ID, $siteUrls))
				{
					$arParams["~".$key] = str_replace(
						["#FORUM_ID#", "#TOPIC_ID#", "#MESSAGE_ID#"],
						["#FID#", "#TID#", "#MID#"],
						$siteUrls[SITE_ID]
					);
				}
				else
				{
					$arParams["~".$key] = (new Main\Web\Uri($this->request->getRequestUri()))
						->deleteParams(["ACTION", "sessid", "PAGE_NAME", "FID", "TID", "MID"])
						->addParams(["PAGE_NAME" => "read", "FID" => "#FID#", "TID" => "#TID#", "MID" => "#MID#"])
						->getLocator();
				}
			}
			else
			{
				$arParams["~".$key] = "";
			}
			$arParams[$key] = htmlspecialcharsbx($arParams[$key]);
		}

		$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = trim($arParams["POST_FIRST_MESSAGE_TEMPLATE"]);
		if (empty($arParams["POST_FIRST_MESSAGE_TEMPLATE"]))
		{
			$arParams["POST_FIRST_MESSAGE_TEMPLATE"] = "#IMAGE# \n [url=#LINK#]#TITLE#[/url]\n\n#BODY#";
		}

		$arParams["USE_CAPTCHA"] = $this->forum["USE_CAPTCHA"] === "Y" || $arParams["USE_CAPTCHA"] === "Y" ? "Y" : "N";
		// endregion check Main Params

		//region check Additional Params
		$arParams["USER_FIELDS"] = (is_array($arParams["USER_FIELDS"]) ? $arParams["USER_FIELDS"] : (empty($arParams["USER_FIELDS"]) ? [] : [$arParams["USER_FIELDS"]]));
		if (!in_array("UF_FORUM_MESSAGE_DOC", $arParams["USER_FIELDS"]))
			$arParams["USER_FIELDS"][] = "UF_FORUM_MESSAGE_DOC";
		$arParams["IMAGE_SIZE"] = intval($arParams["IMAGE_SIZE"]);
		$arParams["IMAGE_SIZE"] = $arParams["IMAGE_SIZE"] > 0 ? $arParams["IMAGE_SIZE"] : 300;

		$arParams["PATH_TO_SMILE"] = "";
		$arParams["ENABLE_HIDDEN"] = ($arParams["ENABLE_HIDDEN"] == "Y" ? "Y" : "N");
		$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] == "Y" ? "Y" : "N");

		$arParams["SUBSCRIBE_AUTHOR_ELEMENT"] = ($arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" ? "Y" : "N");

		$arParams["MESSAGES_PER_PAGE"] = intval($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : Main\Config\Option::get("forum", "MESSAGES_PER_PAGE", "10"));
		$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
		$arParams["PAGE_NAVIGATION_TEMPLATE"] = (!empty($arParams["PAGE_NAVIGATION_TEMPLATE"]) ? $arParams["PAGE_NAVIGATION_TEMPLATE"] : "modern");

		$arParams["DATE_TIME_FORMAT"] = $arParams["DATE_TIME_FORMAT"]?: implode(' ', [
			Main\Context::getCurrent()->getCulture()->getLongDateFormat(),
			Main\Context::getCurrent()->getCulture()->getShortTimeFormat()
		]);
		$arParams["SHOW_AVATAR"] = ($arParams["SHOW_AVATAR"] == "N" || $arParams["SHOW_AVATAR"] == "PHOTO" ? $arParams["SHOW_AVATAR"] : "Y");

		$arParams["PREORDER"] = ($arParams["PREORDER"] == "N" ? "N" : "Y");

		$arParams["AUTOSAVE"] = (!isset($arParams["AUTOSAVE"]) ?
			CForumAutosave::GetInstance() : $arParams["AUTOSAVE"]
		);

		// activation rating
		CRatingsComponentsMain::GetShowRating($arParams);

		$arParams["AJAX_POST"] = "Y";
		if (isset($this->__parent) &&
			isset($this->__parent->arParams) &&
			isset($this->__parent->arParams["AJAX_MODE"]) &&
			$this->__parent->arParams["AJAX_MODE"] == "Y")
			$arParams["AJAX_POST"] = "N";

		$arParams["SHOW_SUBSCRIBE"] = ($arParams["SHOW_SUBSCRIBE"] == "N" ? "N" : "Y");

		if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && Main\Config\Option::get("main", "component_cache_on", "Y") == "Y"))
			$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
		else
			$arParams["CACHE_TIME"] = 0;
		//endregion check Additional Params
		return $arParams;
	}

	protected function getIblockElement($elementId, $iblockId = 0)
	{
		$cacheId = "forum_iblock_".$elementId;
		$element = null;
		if (
			($res = $this->initCache($cacheId)) &&
			is_array($res) &&
			$res["ID"] == $elementId
		)
		{
			$element = $res;
		}
		else
		{
			$filter = ["ID" => $elementId, "SHOW_HISTORY" => "Y"];
			if ($iblockId > 0)
			{
				$filter["IBLOCK_ID"] = $iblockId;
			}

			$element = CIBlockElement::GetList(
				[],
				$filter,
				false,
				false,
				[
					"IBLOCK_ID",
					"ID",
					"NAME",
					"TAGS",
					"CODE",
					"IBLOCK_SECTION_ID",
					"DETAIL_PAGE_URL",
					"CREATED_BY",
					"PREVIEW_PICTURE",
					"PREVIEW_TEXT",
					"PROPERTY_FORUM_TOPIC_ID",
					"PROPERTY_FORUM_MESSAGE_CNT"
				]
			)->GetNext();
			$this->setCache($cacheId, "", $element);
		}
		return $element;
	}

	protected function adaptForCustomTemplates()
	{
		$this->arParams["POST_FIRST_MESSAGE"] = "Y";
		foreach([
			"MINIMIZED_EXPAND_TEXT" => Loc::getMessage("F_EXPAND_TEXT"),
			"MINIMIZED_MINIMIZE_TEXT" => Loc::getMessage("F_MINIMIZE_TEXT"),
			"MESSAGE_TITLE" => Loc::getMessage("F_MESSAGE_TEXT")
		] as $locName => $locValue)
		{
			if (!array_key_exists($locName, $this->arParams))
			{
				$this->arParams[$locName] = $locValue;
			}
		}

		$this->arResult["FORUM_TOPIC_ID"] = intval($this->arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);

		global $USER;
		$this->arResult["USER"] = array(
			"ID" => $USER->GetID(),
			"GROUPS" => $USER->GetUserGroupArray(),
			"PERMISSION" => $this->user->getPermissionOnForum($this->forum->getId()),
			"SHOWED_NAME" => $this->user->getName(),
			"SUBSCRIBE" => [],
			"FORUM_SUBSCRIBE" => "N",
			"TOPIC_SUBSCRIBE" => "N",
			"RIGHTS" => array(
				"ADD_TOPIC" => $this->user->canAddTopic($this->forum) ? "Y" : "N",
				"MODERATE" => $this->user->canModerate($this->forum) ? "Y" : "N",
				"EDIT" => $this->user->canEditForum($this->forum) ? "Y" : "N",
				"ADD_MESSAGE" => $this->topic ==! null && $this->user->canAddMessage($this->topic) ? "Y" : "N"));

		$this->arResult["PANELS"] = array(
			"MODERATE" => $this->arResult["USER"]["RIGHTS"]["MODERATE"],
			"DELETE" => $this->arResult["USER"]["RIGHTS"]["EDIT"],
		);
		$this->arResult["SHOW_PANEL"] = in_array("Y", $this->arResult["PANELS"]) ? "Y" : "N";

		$this->arResult["ERROR_MESSAGE"] = "";
		$this->arResult["~ERROR_MESSAGE"] = [];
		if (!$this->errorCollection->isEmpty())
		{
			$this->arResult["ERROR_MESSAGE"] = implode("<br>", $this->errorCollection->getValues());
			$this->arResult["~ERROR_MESSAGE"] = $this->errorCollection->getValues();
		}
		$this->arResult["OK_MESSAGE"] = "";
		$this->arResult["~OK_MESSAGE"] = [];
	}

	protected function checkPostActions()
	{
		if (!$this->request->isPost() || $this->request->getPost("save_product_review") !== "Y")
		{
			return false;
		}

		$result = new Main\Result();

		$this->isAjaxMode = $this->arParams["AJAX_POST"] === "Y" && $this->request->get("dataType");

		$data = $this->request->toArray();
		if ($this->arParams["ELEMENT_ID"] != $this->request->get("ELEMENT_ID"))
		{
			$result->addError(new Main\Error(Loc::getMessage("F_ERR_EID_IS_NOT_EXIST",
				["#ELEMENT_ID#" => intval($this->request->get("ELEMENT_ID"))])));
		}
		elseif (!check_bitrix_sessid())
		{
			$result->addError(new Main\Error(Loc::getMessage("F_ERR_SESSION_TIME_IS_UP")));
		}
		elseif ($this->topic && !$this->user->canAddMessage($this->topic) ||
			!$this->topic && ($this->user->getPermissionOnForum($this->forum) < Permission::CAN_ADD_MESSAGE))
		{
			$result->addError(new Main\Error(Loc::getMessage("F_ERR_NOT_RIGHT_FOR_ADD")));
		}
		elseif (mb_strlen($data["REVIEW_TEXT"]) < 1)
		{
			$result->addError(new Main\Error(Loc::getMessage("F_ERR_NO_REVIEW_TEXT"), "post is empty"));
		}
		else if (!$this->user->isAuthorized() && $this->arParams["USE_CAPTCHA"] === "Y" &&
			!$this->checkCaptchta($data["captcha_code"], $data["captcha_word"]))
		{
			$result->addError(new Main\Error(Loc::getMessage("POSTM_CAPTCHA"), "bad captcha"));
		}

		if ($result->isSuccess())
		{
			return ($this->request->get("preview_comment") && $this->request->get("preview_comment") !== "N"
				? $this->previewMessage($data) : $this->addMessage($data));
		}
		return $result;
	}

	private function checkCaptchta($captchaCode, $captchaWord)
	{
		if (mb_strlen($captchaCode) > 0)
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$captchaPass = Main\Config\Option::get("main", "captcha_password", "");

			$cpt = new CCaptcha();
			return $cpt->CheckCodeCrypt($captchaWord, $captchaCode, $captchaPass);
		}
		return false;
	}

	private function getCaptchaCode()
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");

		$cpt = new CCaptcha();
		$captchaPass = Main\Config\Option::get("main", "captcha_password", "");
		if ($captchaPass == "")
		{
			$captchaPass = Main\Security\Random::getString(10);
			Main\Config\Option::set("main", "captcha_password", $captchaPass);
		}
		$cpt->SetCodeCrypt($captchaPass);
		return $cpt->GetCodeCrypt();
	}

	protected function addMessage($data): Main\Result
	{
		$PRODUCT_IBLOCK_ID = intval($this->arResult["ELEMENT"]["IBLOCK_ID"]);

		//region Create new Properties
		$needProperty = [];
		if (!array_key_exists("PROPERTY_FORUM_TOPIC_ID_VALUE", $this->arResult["ELEMENT"]))
		{
			$needProperty[] = array(
				"IBLOCK_ID" => $this->arResult["ELEMENT"]["IBLOCK_ID"],
				"ACTIVE" => "Y",
				"PROPERTY_TYPE" => "N",
				"MULTIPLE" => "N",
				"NAME" => GetMessage("F_FORUM_TOPIC_ID"),
				"CODE" => "FORUM_TOPIC_ID");
		}
		if (!array_key_exists("PROPERTY_FORUM_MESSAGE_CNT_VALUE", $this->arResult["ELEMENT"]))
		{
			$needProperty[] = array(
				"IBLOCK_ID" => $this->arResult["ELEMENT"]["IBLOCK_ID"],
				"ACTIVE" => "Y",
				"PROPERTY_TYPE" => "N",
				"MULTIPLE" => "N",
				"NAME" => GetMessage("F_FORUM_MESSAGE_CNT"),
				"CODE" => "FORUM_MESSAGE_CNT");
		}
		if (!empty($needProperty))
		{
			$obProperty = new CIBlockProperty;
			foreach ($needProperty as $prop)
			{
				$obProperty->Add($prop);
			}
		}
		//endregion

		$result = new Main\Result();
		//region Create Topic
		if (!$this->topic)
		{
			$result = $this->createTopicFromElement($this->arResult["ELEMENT"]);
			if ($result->isSuccess())
			{
				$this->topic = Forum\Topic::getById($result->getData()["TOPIC_ID"]);
			}
		}
		if ($result->isSuccess())
		{
			if ($this->arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"] != $this->topic["ID"])
			{
				CIBlockElement::SetPropertyValues($this->arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, $this->topic["ID"], "FORUM_TOPIC_ID");
				CIBlockElement::SetPropertyValues($this->arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, $this->topic["POSTS"], "FORUM_MESSAGE_CNT");
			}
		}
		//endregion Create Topic

		//region Create Message
		if ($result->isSuccess())
		{
			$fields = [
				"POST_MESSAGE" => $data["REVIEW_TEXT"],
				"AUTHOR_ID" => $this->user->getId(),
				"AUTHOR_NAME" => ($data["REVIEW_AUTHOR"] ?: $this->user->getName()),
				"AUTHOR_EMAIL" => $data["REVIEW_EMAIL"],
				"USE_SMILES" => $data["REVIEW_USE_SMILES"],
				"APPROVED" => ($this->forum["MODERATION"] !== "Y" || $this->user->canModerate($this->forum) ? "Y" : "N"),
				"PARAM2" => intval($this->arParams["ELEMENT_ID"]),
				"FILES" => [],
				"FILES_TO_UPLOAD" => []
			];

			if (is_array($data["FILES"]))
			{
				foreach ($data["FILES"] as $key)
				{
					$file = ["FILE_ID" => $key];
					if (!in_array($key, $data["FILES_TO_UPLOAD"]))
					{
						$file["del"] = "Y";
					}
					$fields["FILES"][] = $file;
				}
			}
			if ($this->request->getFile("REVIEW_ATTACH_IMG"))
			{
				$fields["FILES"][] = $this->request->getFile("REVIEW_ATTACH_IMG");
			}
			else
			{
				foreach ($this->request->getFileList() as $key => $val)
				{
					if (mb_strpos($key, "FILE_NEW") ===  0)
					{
						$fields["FILES"][] = $val;
					}
				}
			}
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("FORUM_MESSAGE", $fields);

			$result = Forum\Message::create($this->topic, $fields);
			if ($result->isSuccess())
			{
				// SUBSCRIBE
				if ($data["TOPIC_SUBSCRIBE"] == "Y")
				{
					ForumSubscribeNewMessagesEx(
						$this->forum->getId(),
						$this->topic->getId(), "N",
						$strErrorMessage, $strOKMessage);
					BXClearCache(true, "/bitrix/forum/user/".$this->user->getId()."/subscribe/");
				}

				if ($this->arParams["AUTOSAVE"])
				{
					$this->arParams["AUTOSAVE"]->Reset();
				}
				ForumClearComponentCache($this->getName());
			}
			$result->setData(["fields" => $fields]);
		}
		return $result;
	}

	private function createTopicFromElement(array $element): Main\Result
	{
		$starter = $element["~CREATED_BY"] !== $this->user->getId() ? Forum\User::getById($element["~CREATED_BY"]) : $this->user;
		$postMessage = $this->arParams["POST_FIRST_MESSAGE_TEMPLATE"];
		$postReplacement = [
			"#IMAGE#" => "",
			"#TITLE#" => $element["~NAME"],
			"#BODY#" => ($this->forum["ALLOW_HTML"] != "Y" ? strip_tags($element["~PREVIEW_TEXT"]) : $element["~PREVIEW_TEXT"]),
			"#LINK#" => (empty($this->arParams["URL_TEMPLATES_DETAIL"]) ? $element["DETAIL_PAGE_URL"] : $this->arParams["URL_TEMPLATES_DETAIL"])
		];

		if (mb_strpos($postMessage, "#LINK#") !== false)
		{
			$urlReplacements = [
				"#ELEMENT_ID#" => $element["ID"],
				"#ID#" => $element["ID"],
				"#ELEMENT_CODE#" => $element["CODE"],
				"#SECTION_ID#" => $element["IBLOCK_SECTION_ID"]
			];

			if (mb_strpos($postReplacement["#LINK#"], "#SECTION_CODE#") !== false)
			{
				$urlReplacements["#SECTION_CODE#"] = "";
				if ($element["IBLOCK_SECTION_ID"] > 0)
				{
					$section = CIBlockSection::GetList([], ["ID" => $element["IBLOCK_SECTION_ID"]], false, ["ID", "NAME", "CODE"])->fetch();
					$urlReplacements["#SECTION_CODE#"] = $section["CODE"];
				}
			}
			if (mb_strpos($postReplacement["#LINK#"], "#SECTION_CODE_PATH#") !== false)
			{
				$codePath = [];
				$dbRes = CIBlockSection::GetNavChain(0, $element["IBLOCK_SECTION_ID"], ["ID", "IBLOCK_SECTION_ID", "CODE"]);
				while ($a = $dbRes->Fetch())
				{
					$codePath[] = urlencode($a["CODE"]);
				}
				$urlReplacements["#SECTION_CODE_PATH#"] = implode("/", $codePath);
			}
			$postReplacement["#LINK#"] = str_replace(array_keys($urlReplacements), array_values($urlReplacements), $postReplacement["#LINK#"]);
		}

		if (mb_strpos($postMessage, "#IMAGE#") !== false
			&& ($arImage = CFile::GetFileArray($element["PREVIEW_PICTURE"]))
		)
		{
			$postReplacement["#IMAGE#"] = ($this->arResult["FORUM"]["ALLOW_IMG"] == "Y" ? "[IMG]".$arImage["SRC"]."[/IMG]" : "");
		}

		$result = Forum\Topic::create($this->forum, [
			"TITLE" => $element["~NAME"],
			"TAGS" => $element["~TAGS"],
			"APPROVED" => "Y",
			"AUTHOR_ID" => $starter->getId(),
			"AUTHOR_NAME" => $starter->getName(),
			"TOPIC_XML_ID" => "IBLOCK_".$element["ID"],
			"POST_MESSAGE" => str_replace(array_keys($postReplacement), array_values($postReplacement), $postMessage),
			"PARAM1" => "IB",
			"PARAM2" => $element["ID"],
		]);

		if ($result->isSuccess())
		{
			$topic = Forum\Topic::getById($result->getData()["TOPIC_ID"]);

			if ($this->arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" && $starter->isAuthorized())
			{
				CForumSubscribe::Add(array(
					"USER_ID" => $starter->getId(),
					"FORUM_ID" => $this->forum->getId(),
					"SITE_ID" => SITE_ID,
					"TOPIC_ID" => $topic->getId(),
					"NEW_TOPIC_ONLY" => "N"));
				BXClearCache(true, "/bitrix/forum/user/".$starter->getId()."/subscribe/");
			}
		}
		return $result;
	}

	protected function previewMessage($data)
	{
		global $USER, $APPLICATION;
		$userId = intval($USER->GetID());

		$data["FILES_TO_UPLOAD"] = is_array($data["FILES_TO_UPLOAD"]) ? $data["FILES_TO_UPLOAD"] : [];
		$data["FILES"] = is_array($data["FILES"]) ? $data["FILES"] : [];
		$files = [];
		foreach ($data["FILES"] as $key => $fileId)
		{
			$files[$fileId] = ["FILE_ID" => $fileId];

			if (!in_array($fileId, $data["FILES_TO_UPLOAD"]))
			{
				$files[$fileId] += ["del" => "Y"];
				unset($data["FILES"][$key]);
				unset($data["FILES_TO_UPLOAD"][$key]);
			}
		}

		foreach ($this->request->getFileList() as $key => $val)
		{
			if (mb_strpos($key, "FILE_NEW") === 0 && $val["error"] <= 0)
			{
				$files[] = $val;
			}
		}

		$result = new Main\Result();
		$savedFiles = [];
		if (!empty($files) && !($savedFiles = CForumFiles::Save($files, [
				"FORUM_ID" => $this->forum->getId(),
				"TOPIC_ID" => ($this->topic ? $this->topic->getId() : 0),
				"MESSAGE_ID" => 0,
				"USER_ID" => $userId
			])))
		{
			$result->addError(
				new Main\Error(
					(($exception = $APPLICATION->GetException())?
						$exception->GetString() : "File uploading error"
					)
				)
			);
			$savedFiles = [];
		}
		$files = array_keys($savedFiles);
		sort($files);
		$allows = ["SMILES" => ($data["REVIEW_USE_SMILES"] !== "Y" ? "N" : $this->forum["ALLOW_SMILES"])]
			+ forumTextParser::GetFeatures($this->forum);
		$parser = new forumTextParser(LANGUAGE_ID);
		$this->arResult["MESSAGE_VIEW"] = [
			"POST_MESSAGE_TEXT" => $parser->convert($data["REVIEW_TEXT"], $allows, "html", $files),
			"AUTHOR_NAME" => htmlspecialcharsbx($data["REVIEW_AUTHOR"] ?: $this->user->getName()),
			"REVIEW_EMAIL" => htmlspecialcharsbx($data["REVIEW_EMAIL"] ?: ""),
			"AUTHOR_ID" => $userId,
			"AUTHOR_URL" => CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_PROFILE_VIEW"], ["UID" => $userId]),
			"POST_DATE" => CForumFormat::DateFormat($this->arParams["DATE_TIME_FORMAT"], time() + CTimeZone::GetOffset()),
			"FILES" => $files
		];
		$result->setData(["fields" => [
			"POST_MESSAGE" => $data["REVIEW_TEXT"],
			"AUTHOR_ID" => $this->user->getId(),
			"AUTHOR_NAME" => ($data["REVIEW_AUTHOR"] ?: $this->user->getName()),
			"AUTHOR_EMAIL" => $data["REVIEW_EMAIL"],
			"USE_SMILES" => $data["REVIEW_USE_SMILES"],
			"FILES" => $files
		]]);
		return $result;
	}

	protected function markUserInTopic()
	{
		$this->user->setLastVisit();
		if ($this->topic)
		{
			CForumStat::RegisterUSER(array(
				"SITE_ID" => SITE_ID,
				"FORUM_ID" => $this->forum->getId(),
				"TOPIC_ID" => $this->topic->getId()
			));
			ForumSetReadTopic($this->forum->getId(), $this->topic->getId());
		}
	}

	protected function fillUserSubscribeData()
	{
		if (!$this->user->isAuthorized())
		{
			return;
		}
		$dbRes = Forum\SubscribeTable::getList([
			"select" => ["*"],
			"filter" => [
				"USER_ID" => $this->user->getId(),
				"FORUM_ID" => $this->forum->getId(),
			] + ($this->topic !== null ?
			[
				"LOGIC" => "OR",
				["TOPIC_ID" => 0],
				["TOPIC_ID" => $this->topic->getId()]
			] : [
				"TOPIC_ID" => 0
			]
		)]);
		while ($res = $dbRes->Fetch())
		{
			$this->arResult["USER"]["SUBSCRIBE"][] = $res;
			if ($this->topic !== null && $res["TOPIC_ID"] == $this->topic->getId())
			{
				$this->arResult["USER"]["TOPIC_SUBSCRIBE"] = "Y";
			}
			else
			{
				$this->arResult["USER"]["FORUM_SUBSCRIBE"] = "Y";
			}
		}
	}

	protected function fillTopicData()
	{
		CPageOption::SetOptionString("main", "nav_page_in_session", "N");
		global $NavNum;
		$pageNo = min(intval($this->request->getQuery("PAGEN_".($NavNum + 1))), 200);

		$MID = intval(array_key_exists("RESULT", $this->arResult) ?
			$this->arResult["RESULT"] : $this->request->get("MID"));

		global $USER;
		if ($MID)
		{
			$pageNo = CForumMessage::GetMessagePage(
				$MID,
				$this->arParams["MESSAGES_PER_PAGE"],
				$USER->GetUserGroupArray(),
				$this->arResult["FORUM_TOPIC_ID"],
				array(
					"ORDER_DIRECTION" => ($this->arParams["PREORDER"] == "N" ? "DESC" : "ASC"),
					"PERMISSION_EXTERNAL" => $this->arResult["USER"]["PERMISSION"],
					"FILTER" => ["!PARAM1" => "IB"]
				)
			);
		}

		$ar_cache_id = [
			$this->arParams["FORUM_ID"],
			$this->arParams["ELEMENT_ID"],
			$this->arParams["SHOW_AVATAR"],
			$this->arParams["SHOW_RATING"],
			$this->arParams["MESSAGES_PER_PAGE"],
			$this->arParams["DATE_TIME_FORMAT"],
			$this->arParams["PREORDER"],
			$this->arResult["FORUM_TOPIC_ID"],
			$this->arResult["USER"]["PERMISSION"]
		];

		$cache_id = "forum_message_".serialize($ar_cache_id);

		if ($pageNo <= 0 && ($res = $this->initCache($cache_id, "list")))
		{
			$arMessages = $res["messages"];
			$this->arResult["NAV_RESULT"] = $res["Nav"]["NAV_RESULT"];
			$this->arResult["NAV_STRING"] = $res["Nav"]["NAV_STRING"];
			global $APPLICATION;
			$APPLICATION->SetAdditionalCSS($res["Nav"]["NAV_STYLE"]);
			$NavNum++;
		}
		else
		{
			$fields = array(
				"bDescPageNumbering" => false,
				"nPageSize" => $this->arParams["MESSAGES_PER_PAGE"],
				"bShowAll" => false,
				"sNameTemplate" => $this->arParams["NAME_TEMPLATE"],
				"iNumPage" => $pageNo > 0 ? $pageNo : false
			);

			$filter = [
				"FORUM_ID" => $this->forum->getId(),
				"TOPIC_ID" => $this->topic->getId(),
				"!PARAM1" => "IB"
			];
			if ($this->arResult["USER"]["RIGHTS"]["MODERATE"] != "Y")
				$filter["APPROVED"] = "Y";

			$dbRes = CForumMessage::GetListEx(
				["ID" => ($this->arParams["PREORDER"] == "N" ? "DESC" : "ASC")],
				$filter,
				false,
				0,
				$fields
			);
			$dbRes->NavStart($fields["nPageSize"], $fields["bShowAll"], $fields["iNumPage"]);
			$this->arResult["NAV_RESULT"] = $dbRes;

			$arMessages = [];
			if ($dbRes)
			{
				global $APPLICATION;
				$this->arResult["NAV_STRING"] = $dbRes->GetPageNavStringEx($navComponentObject, GetMessage("NAV_OPINIONS"), $this->arParams["PAGE_NAVIGATION_TEMPLATE"]);
				$this->arResult["NAV_STYLE"] = $APPLICATION->GetAdditionalCSS();
				$this->arResult["PAGE_COUNT"] = $dbRes->NavPageCount;
				$this->arResult["PAGE_NUMBER"] = $dbRes->NavPageNomer;
				$number = intval($dbRes->NavPageNomer-1) * $this->arParams["MESSAGES_PER_PAGE"] + 1;

				$arAllow = forumTextParser::GetFeatures($this->forum);

				while ($res = $dbRes->GetNext())
				{
					/************** Message info ***************************************/
					// number in topic
					$res["NUMBER"] = $number++;
					// date
					$res["POST_DATE"] = CForumFormat::DateFormat($this->arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["POST_DATE"], CSite::GetDateFormat()));
					$res["EDIT_DATE"] = CForumFormat::DateFormat($this->arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
					// text
					$res["ALLOW"] = ($res["USE_SMILES"] === "Y" ? [] : ["SMILES" => "N"]) + $arAllow;
					$res["~POST_MESSAGE_TEXT"] = (Main\Config\Option::get("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
					$res["PROPS"] = [];
					// attach
					$res["ATTACH_IMG"] = "";
					$res["FILES"] = $res["~ATTACH_FILE"] = $res["ATTACH_FILE"] = [];
					// links
					if ($this->arResult["SHOW_PANEL"] == "Y")
					{
						$res["URL"]["REVIEWS"] = $APPLICATION->GetCurPageParam();
						$res["URL"]["~MODERATE"] = ForumAddPageParams($res["URL"]["REVIEWS"],
							["MID" => $res["ID"], "REVIEW_ACTION" => $res["APPROVED"]=="Y" ? "HIDE" : "SHOW"], true, false);
						$res["URL"]["MODERATE"] = htmlspecialcharsbx($res["URL"]["~MODERATE"])."&amp;".bitrix_sessid_get();
						$res["URL"]["~DELETE"] = ForumAddPageParams($res["URL"]["REVIEWS"],
							["MID" => $res["ID"], "REVIEW_ACTION" => "DEL"], true, false);
						$res["URL"]["DELETE"] = htmlspecialcharsbx($res["URL"]["~DELETE"])."&amp;".bitrix_sessid_get();
					}
					/************** Message info/***************************************/
					/************** Author info ****************************************/
					$res["AUTHOR_URL"] = "";
					if ($res["AUTHOR_ID"] > 0)
					{
						if (!empty($this->arParams["URL_TEMPLATES_PROFILE_VIEW"]))
						{
							$res["AUTHOR_URL"] = CComponentEngine::MakePathFromTemplate(
								$this->arParams["URL_TEMPLATES_PROFILE_VIEW"],
								[
									"UID" => $res["AUTHOR_ID"],
									"USER_ID" => $res["AUTHOR_ID"],
									"ID" => $res["AUTHOR_ID"]
								]
							);
						}
						// avatar
						$id = ($this->arParams["SHOW_AVATAR"] === "Y" ?
							($res["AVATAR"] > 0 ? $res["AVATAR"] : $res["PERSONAL_PHOTO"]) :
							($this->arParams["SHOW_AVATAR"] == "PHOTO" ? $res["PERSONAL_PHOTO"] : 0)
						);
						if ($id > 0)
						{
							$res["AVATAR"] = array(
								"ID" => $id,
								"FILE" => CFile::ResizeImageGet(
									$id,
									["width" => 30, "height" => 30],
									BX_RESIZE_IMAGE_EXACT,
									false
								)
							);
							if (!empty($res["AVATAR"]["FILE"]))
								$res["AVATAR"]["HTML"] = CFile::ShowImage($res["AVATAR"]["FILE"]["src"], 30, 30, "border=0 align='right'");
						}
					}

					/************** Author info/****************************************/
					// For quote JS
					$res["FOR_JS"] = array(
						"AUTHOR_NAME" => Cutil::JSEscape($res["AUTHOR_NAME"]),
						"POST_MESSAGE_TEXT" => Cutil::JSEscape(htmlspecialcharsbx($res["POST_MESSAGE_TEXT"]))
					);
					$arMessages[$res["ID"]] = $res;
				}
			}
			/************** Attach files ***************************************/
			if (!empty($arMessages))
			{
				$res = array_keys($arMessages);

				$filterFile = array(
					"FORUM_ID" => $this->forum->getId(),
					"TOPIC_ID" => $this->topic->getId()
				);
				$arFilterProps = $filterFile;

				if (min($res) > 1)
				{
					$arFilterProps[">ID"] = $filterFile[">MESSAGE_ID"] = intval(min($res) - 1);
				}
				$arFilterProps["<ID"] = $filterFile["<MESSAGE_ID"] = intval(max($res) + 1);

				$dbRes = Forum\FileTable::getList([
					"select" => ["*", "FILE.*"],
					"filter" => $filterFile
				]);

				while ($raw = $dbRes->fetch())
				{
					$res = [];
					foreach ($raw as $key => $value)
					{
						if (strpos($key, "FORUM_FILE_FILE_") === 0)
						{
							$key = substr($key, 16);
						}
						$res[$key] = $value;
					}
					$res["SRC"] = CFile::GetFileSRC($res);
					if ($arMessages[$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
					{
						// attach for custom
						$arMessages[$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
						$arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0,
							$this->arParams["IMAGE_SIZE"], $this->arParams["IMAGE_SIZE"], true, "border=0", false);
						$arMessages[$res["MESSAGE_ID"]]["ATTACH_FILE"] = $arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"];
					}
					$arMessages[$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
					$this->arResult["FILES"][$res["FILE_ID"]] = $res;
				}
				if (!empty($this->arParams["USER_FIELDS"]))
				{
					$dbProps = CForumMessage::GetList(["ID" => "ASC"], $arFilterProps, false, 0, ["SELECT" => $this->arParams["USER_FIELDS"]]);
					while ($res = $dbProps->Fetch())
					{
						$arMessages[$res["ID"]]["PROPS"] = array_intersect_key($res, array_flip($this->arParams["USER_FIELDS"]));
					}
				}
				/************** Message info ***************************************/
			}

			/************** Message List/***************************************/
			if ($pageNo <= 0 )
			{
				$this->setCache($cache_id, "list", [
					"messages" => $arMessages,
					"Nav" => array(
						"NAV_RESULT" => $this->arResult["NAV_RESULT"],
						"NAV_STYLE"  => $this->arResult["NAV_STYLE"],
						"NAV_STRING" => $this->arResult["NAV_STRING"])
				]);
			}
		}

		//region parse data
		$ratings = ($this->arParams["SHOW_RATING"] == "Y"
			? CRatings::GetRatingVoteResult("FORUM_POST", array_keys($arMessages)) : []);
		$ratings = (is_array($ratings) ? $ratings : []);
		$files = [];
		array_map(function($res) use (&$files) {
			if (array_key_exists("FILES", $res) && is_array($res["FILES"]))
			{
				foreach ($res["FILES"] as $file)
				{
					$files[$file["ID"]] = $file;
				}
			}
		}, $arMessages);
		$parser = new forumTextParser(LANGUAGE_ID);
		$parser->imageWidth = $parser->imageHeight = $this->arParams["IMAGE_SIZE"];
		$parser->arFiles = $files;
		array_walk($arMessages, function(&$res) use ($parser, $ratings) {
			$res["RATING"] = array_key_exists($res["ID"], $ratings) ? $ratings[$res["ID"]] : [];
			$res["POST_MESSAGE_TEXT"] = $parser->convert(
				$res["~POST_MESSAGE_TEXT"],
				array_merge($res["ALLOW"], ["USERFIELDS" => $res["PROPS"]])
			);
		});
		$this->arResult["MESSAGES"] = $arMessages;
		// Link to forum
		$this->arResult["read"] = CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_READ"],
			array("FID" => $this->arParams["FORUM_ID"], "TID" => $this->arResult["FORUM_TOPIC_ID"], "TITLE_SEO" => $this->arResult["FORUM_TOPIC_ID"], "MID" => "s",
				"PARAM1" => "IB", "PARAM2" => $this->arParams["ELEMENT_ID"]));
	}

	protected function fillFormData($result)
	{
		$arResult = &$this->arResult;
		$arResult["REVIEW_AUTHOR"] = $this->user->getName();
		$arResult["REVIEW_USE_SMILES"] = $this->forum["ALLOW_SMILES"];
		$arResult["REVIEW_EMAIL"] = "";
		$arResult["REVIEW_TEXT"] = "";
		$arResult["REVIEW_FILES"] = [];

		if ($result instanceof Main\Result
			&& !($result instanceof Main\ORM\Data\AddResult && $result->isSuccess())
			&& ($postData = $result->getData())
			&& ($postData = $postData["fields"]))
		{
			$arResult["REVIEW_AUTHOR"] = $postData["AUTHOR_NAME"];
			$arResult["REVIEW_USE_SMILES"] = $postData["USE_SMILES"];
			$arResult["REVIEW_EMAIL"] = $postData["AUTHOR_EMAIL"];
			$arResult["REVIEW_TEXT"] = $postData["POST_MESSAGE"];
			foreach ($postData["FILES"] as $fileId)
			{
				if (is_integer($fileId))
				{
					$arResult["REVIEW_FILES"][$fileId] = CFile::GetFileArray($fileId);
				}
			}
		}
		foreach (["REVIEW_AUTHOR", "REVIEW_USE_SMILES", "REVIEW_EMAIL", "REVIEW_TEXT"] as $key)
		{
			$arResult["~".$key] = $arResult[$key];
			$arResult[$key] = htmlspecialcharsbx($arResult[$key]);
		}

		$arResult["SHOW_PANEL_ATTACH_IMG"] = (in_array($this->forum["ALLOW_UPLOAD"], array("A", "F", "Y")) ? "Y" : "N");
		$arResult["TRANSLIT"] = "N";
		if ($this->forum["ALLOW_SMILES"] == "Y")
		{
			$arResult["ForumPrintSmilesList"] = ($this->forum["ALLOW_SMILES"] == "Y" ? ForumPrintSmilesList(3, LANGUAGE_ID) : "");
			$arResult["SMILES"] = CForumSmile::getSmiles("S", LANGUAGE_ID);
		}

		$arResult["CAPTCHA_CODE"] = "";
		if ($this->arParams["USE_CAPTCHA"] == "Y" && !$this->user->isAuthorized())
		{
			$arResult["CAPTCHA_CODE"] = $this->getCaptchaCode();
		}
		unset($arResult);
	}

	protected function checkModerateActions()
	{
		$mid = (int) $this->request->get("MID");
		if ($mid > 0 && in_array($this->request->get("REVIEW_ACTION"), ["DEL", "HIDE", "SHOW"]))
		{
			$this->isAjaxMode = $this->request->get("AJAX_CALL") === "Y";
			if (!check_bitrix_sessid())
			{
				$result = new Main\Result();
				$result->addError(new Main\Error(Loc::getMessage("F_ERR_SESSION_TIME_IS_UP")));
			}
			else if ($this->request->get("REVIEW_ACTION") === "DEL")
			{
				$result = $this->deleteMessageAction($mid);
			}
			else if ($this->request->get("REVIEW_ACTION") === "SHOW")
			{
				$result = $this->showMessageAction($mid);
			}
			else
			{
				$result = $this->hideMessageAction($mid);
			}
			return $result;
		}
		return false;
	}

	protected function sendJsonResponse($response)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		while (ob_end_clean());

		header('Content-Type:application/json; charset=UTF-8');
		echo Main\Web\Json::encode($response);
		CMain::finalActions();
	}

	public function configureActions()
	{
		return [];
	}

	protected function moderateMessageAction($id, $show = true)
	{
		$result = new Main\Orm\Data\UpdateResult();
		$result->setPrimary(['ID' => $id]);
		if (ForumModerateMessage(['MID' => $id], ($show === true ? 'SHOW' : 'HIDE'), $strErrorMessage, $strOKMessage))
		{
			$result->setData(['APPROVED' => $show  === true ? 'Y' : 'N']);
			ForumClearComponentCache($this->getName());
		}
		else
		{
			$result->addError(new Bitrix\Main\Error($strErrorMessage));
		}
		return $result;
	}

	public function showMessageAction(int $id)
	{
		$result = $this->moderateMessageAction($id, true);
		if ($result->isSuccess())
		{
			return $result->getData();
		}
		$this->errorCollection->add($result->getErrors());
	}

	public function hideMessageAction(int $id)
	{
		$result = $this->moderateMessageAction($id, false);
		if ($result->isSuccess())
		{
			return $result->getData();
		}
		$this->errorCollection->add($result->getErrors());
	}

	public function deleteMessageAction(int $id)
	{
		$result = new Main\Orm\Data\DeleteResult();
		if (ForumDeleteMessage(
			["MID" => $id],
			$strErrorMessage,
			$strOKMessage))
		{
			ForumClearComponentCache($this->getName());
		}
		else
		{
			$result->addError(new Bitrix\Main\Error($strErrorMessage));
		}
		return $result;
	}
}