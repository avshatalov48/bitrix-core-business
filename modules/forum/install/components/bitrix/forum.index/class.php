<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Result;
use \Bitrix\Main\Entity\AddResult;
use \Bitrix\Main\Error;

class ForumIndexComponent extends \CBitrixComponent
{
	public function executeComponent()
	{
		try
		{
			if (!\Bitrix\Main\Loader::includeModule("forum"))
			{
				throw new \Bitrix\Main\NotSupportedException(Loc::getMessage("F_NO_MODULE"));
			}

			$this->prepareParams();

			$filter = $this->getFilter();

			$this->processAction($filter);
			$this->prepareData($filter);

			$this->includeComponentTemplate();

			if ($this->arParams["SET_TITLE"] !== "N")
			{
				$this->setTitle();
			}
			if ($this->arParams["SET_NAVIGATION"] !== "N")
			{
				$this->setNavigation();
			}

			return $this->arResult["FORUMS_LIST"];
		}
		catch(Exception $e)
		{
			$exceptionHandling = \Bitrix\Main\Config\Configuration::getValue("exception_handling");
			if ($exceptionHandling["debug"])
			{
				throw $e;
			}
			else
			{
				ShowError($e->getMessage());
			}
		}
	}

	private function prepareParams()
	{
		global $DB;
		/***************** BASE ********************************************/
		$this->arParams["GID"] = intval($this->arParams["GID"]);
		/***************** URL *********************************************/
		$url = (new \Bitrix\Main\Web\Uri($this->request->getRequestedPage()))->getLocator();
		foreach ([
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#",
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#"
		] as $pageName => $pageUrl)
		{
			$key = "URL_TEMPLATES_".strtoupper($pageName);
			$this->arParams[$key] = array_key_exists($key, $this->arParams) ? trim($this->arParams[$key]) : "";
			$this->arParams["~".$key] = strlen($this->arParams[$key]) > 0 ? $this->arParams[$key] : $url."?".$pageUrl;
			$this->arParams[$key] = htmlspecialcharsbx($this->arParams["~".$key]);
		}
		/***************** ADDITIONAL **************************************/
		$this->arParams["FORUMS_PER_PAGE"] = intval($this->arParams["FORUMS_PER_PAGE"]);
		$this->arParams["FORUMS_PER_PAGE"] = $this->arParams["FORUMS_PER_PAGE"] > 0 ? $this->arParams["FORUMS_PER_PAGE"] : \Bitrix\Main\Config\Option::get("forum", "FORUMS_PER_PAGE", 10);
		$this->arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($this->arParams["PAGE_NAVIGATION_TEMPLATE"]);
		$this->arParams["PAGE_NAVIGATION_WINDOW"] = intval(intval($this->arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $this->arParams["PAGE_NAVIGATION_WINDOW"] : 11);
		$this->arParams["FID_RANGE"] = array_map( "intval", is_array($this->arParams["FID"]) && !empty($this->arParams["FID"]) ? $this->arParams["FID"] : []);
		$this->arParams["DATE_FORMAT"] = trim(empty($this->arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(\CSite::GetDateFormat("SHORT")) : $this->arParams["DATE_FORMAT"]);
		$this->arParams["DATE_TIME_FORMAT"] = trim(empty($this->arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(\CSite::GetDateFormat("FULL")) : $this->arParams["DATE_TIME_FORMAT"]);
		$this->arParams["NAME_TEMPLATE"] = (!empty($this->arParams["NAME_TEMPLATE"]) ? $this->arParams["NAME_TEMPLATE"] : false);
		$this->arParams["WORD_LENGTH"] = array_key_exists("WORD_LENGTH", $this->arParams) ? intval($this->arParams["WORD_LENGTH"]) : null;
		$this->arParams["USE_DESC_PAGE"] = ($this->arParams["USE_DESC_PAGE"] != "Y" ? "N" : "Y");

		$this->arParams["SHOW_FORUM_ANOTHER_SITE"] = ($this->arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
		$this->arParams["SHOW_FORUMS_LIST"] = ($this->arParams["SHOW_FORUMS_LIST"] == "Y" ? "Y" : "N");
	/***************** STANDART ****************************************/
		if ($this->arParams["CACHE_TYPE"] == "Y" || ($this->arParams["CACHE_TYPE"] == "A" && \COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
			$this->arParams["CACHE_TIME"] = intval($this->arParams["CACHE_TIME"]);
		else
			$this->arParams["CACHE_TIME"] = 0;
		$this->arParams["SET_TITLE"] = ($this->arParams["SET_TITLE"] == "N" ? "N" : "Y");
		$this->arParams["SET_NAVIGATION"] = ($this->arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");


		$this->arResult["GROUPS"] = \CForumGroup::GetByLang(LANGUAGE_ID);
		$this->arResult["GROUP"] = array_key_exists($this->arParams["GID"], $this->arResult["GROUPS"]) ? $this->arResult["GROUPS"][$this->arParams["GID"]] : null;

		$this->arParams["GID"] = $this->arResult["GROUP"] === null ? 0 : $this->arParams["GID"];

		$this->arResult["USER"] = array(
			"CAN_MODERATE" => "N",
			"HIDDEN_GROUPS" => array(),
			"HIDDEN_FORUMS" => array());

		if ($this->getUser()->IsAuthorized())
		{
			$res = \CUserOptions::GetOption("forum", "user_info", "");
			$res = (CheckSerializedData($res) ? @unserialize($res) : []);
			$this->arResult["USER"]["HIDDEN_GROUPS"] = (is_array($res["groups"]) ? $res["groups"] : array());
			$this->arResult["USER"]["HIDDEN_FORUMS"] = (is_array($res["forums"]) ? $res["forums"] : array());
		}

		$this->arResult["URL"] = array(
			"INDEX" => \CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_INDEX"]),
			"~INDEX" => \CComponentEngine::MakePathFromTemplate($this->arParams["~URL_TEMPLATES_INDEX"]),
			"RSS" => \CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_RSS"],
				array("TYPE" => "default", "MODE" => "forum", "IID" => "all")),
			"~RSS" => \CComponentEngine::MakePathFromTemplate($this->arParams["~URL_TEMPLATES_RSS"],
				array("TYPE" => "default", "MODE" => "forum", "IID" => "all")),
			"~RSS_DEFAULT" => \CComponentEngine::MakePathFromTemplate($this->arParams["~URL_TEMPLATES_RSS"],
				array("TYPE" => "rss2", "MODE" => "forum", "IID" => "all")),
			"RSS_DEFAULT" => \CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_RSS"],
				array("TYPE" => "rss2", "MODE" => "forum", "IID" => "all")),
		);
		foreach ($this->arResult["GROUPS"] as $id => $group)
		{
			$this->arResult["URL"]["GROUP_".$id] = \CComponentEngine::MakePathFromTemplate(
				$this->arParams["URL_TEMPLATES_FORUMS"], array("GID" => $id));
			$this->arResult["URL"]["~GROUP_".$id] = \CComponentEngine::MakePathFromTemplate(
				$this->arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $id));
		}

		$this->arResult["FORUMS_FOR_GUEST"] = array();
		$this->arResult["FORUMS_LIST"] = array();
	}

	private function getFilter()
	{
		$filter = [];
		if (!\CForumUser::IsAdmin())
		{
			$filter = [
				"LID" => SITE_ID,
				"PERMS" => [$this->getUser()->GetGroups(), "A"],
				"ACTIVE" => "Y"
			];
			if (!empty($this->arParams["FID_RANGE"]))
			{
				$filter["@ID"] = $this->arParams["FID_RANGE"];
			}
		}
		else
		{
			if ($this->arParams["SHOW_FORUM_ANOTHER_SITE"] == "N")
			{
				$filter["LID"] = SITE_ID;
			}
			if ($this->arParams["SHOW_FORUMS_LIST"] == "Y" && !empty($this->arParams["FID_RANGE"]))
			{
				$filter["@ID"] = $this->arParams["FID_RANGE"];
			}
		}

		if (is_array($this->arResult["GROUP"]))
		{
			if (($this->arResult["GROUP"]["RIGHT_MARGIN"] - $this->arResult["GROUP"]["LEFT_MARGIN"]) > 1)
			{
				$filter["@FORUM_GROUP_ID"] = [$this->arParams["GID"]];
				reset($this->arResult["GROUPS"]);
				$found = false;
				$res = reset($this->arResult["GROUPS"]);
				do
				{
					if ($res["ID"] == $this->arParams["GID"])
					{
						$found = true;
						continue;
					}
					if (!$found)
					{
						continue;
					}
					if ($res["LEFT_MARGIN"] > $this->arResult["GROUP"]["RIGHT_MARGIN"])
					{
						break;
					}
					$filter["@FORUM_GROUP_ID"][] = $res["ID"];
				} while ($res = next($this->arResult["GROUPS"]));
			}
			else
			{
				$filter["FORUM_GROUP_ID"] = $this->arParams["GID"];
			}
		}
		return $filter;
	}

	private function processAction(array $filter)
	{

		if ($this->request->getQuery("ACTION") == "SET_BE_READ" && check_bitrix_sessid())
		{
			$dbRes = \CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $filter);
			while ($res = $dbRes->fetch())
			{
				ForumSetReadForum($res["ID"]);
			}

			$url = (new \Bitrix\Main\Web\Uri($this->request->getRequestUri()))
				->deleteParams(array("ACTION", "sessid"))
				->getLocator();

			LocalRedirect($url);
		}
	}

	private function prepareData(array $filter)
	{
		$parser = new forumTextParser(false, false, false, "light");
		if ($this->arParams["WORD_LENGTH"] !== null)
			$parser->MaxStringLen = $this->arParams["WORD_LENGTH"];
		//region getting common data from DB or cache
		$cache = new \CPHPCache();
		global $NavNum;
		$PAGEN_NAME = "PAGEN_".($NavNum+1);
		global ${$PAGEN_NAME};
		$PAGEN = ${$PAGEN_NAME};
		global $CACHE_MANAGER;
		$cache_path = $CACHE_MANAGER->GetCompCachePath(\CComponentEngine::MakeComponentPath($this->__name));

		\CPageOption::SetOptionString("main", "nav_page_in_session", "N"); // reduce cache size

		$arForumOrder = array(
			"FORUM_GROUP_SORT"=>"ASC",
			"FORUM_GROUP_ID"=>"ASC",
			"SORT"=>"ASC",
			"NAME"=>"ASC"
		);
		$arForumAddParams = array(
			"bDescPageNumbering" => ($this->arParams["USE_DESC_PAGE"] == "Y"),
			"nPageSize" => $this->arParams["FORUMS_PER_PAGE"],
			"bShowAll" => false,
			"sNameTemplate" => $this->arParams["NAME_TEMPLATE"]
		);
		$arNavParams = array(
			"nPageSize" => $this->arParams["FORUMS_PER_PAGE"],
			"bShowAll" => false
		);
		$arNavigation = \CDBResult::GetNavParams($arNavParams);

		if ($this->StartResultCache($this->arParams["CACHE_TIME"], array($filter, $arForumAddParams, $arNavigation)))
		{
			$arForumAddParams["nav_result"] = false;
			$dbForumNav = \CForumNew::GetListEx(
				$arForumOrder,
				$filter,
				false,
				false,
				$arForumAddParams
			);
			$arForumAddParams["nav_result"] = $dbForumNav;
			$dbForum = \CForumNew::GetListEx(
				$arForumOrder,
				$filter,
				false,
				false,
				$arForumAddParams
			);

			$this->arResult["NAV_RESULT"] = $dbForumNav;
			$this->arResult["NAV_STRING"] = $dbForumNav->GetPageNavStringEx($navComponentObject, GetMessage("F_FORUM"), $this->arParams["PAGE_NAVIGATION_TEMPLATE"]);
			$this->arResult["NAV_PAGE"] = $dbForumNav->NavNum.":".$dbForumNav->NavPageNomer;

			while ($res = $dbForum->GetNext())
			{
				$res["MODERATE"] = array("TOPICS" => 0, "POSTS" => intval($res["POSTS_UNAPPROVED"]));
				$res["mCnt"] = $res["MODERATE"]["POSTS"]; // for custom templates

				$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
				$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
				$res["LAST_POST_DATE"] = ($res["LAST_MESSAGE_ID"] > 0 ?
					\CForumFormat::DateFormat($this->arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], \CSite::GetDateFormat())) : "");
				$res["ABS_LAST_POST_DATE"] = ($res["ABS_LAST_MESSAGE_ID"] > 0 ?
					\CForumFormat::DateFormat($this->arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["ABS_LAST_POST_DATE"], \CSite::GetDateFormat())) : "");
				$res["URL"] = array(
					"MODERATE_MESSAGE" => \CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_MESSAGE_APPR"],
						array("FID" => $res["ID"], "TID" => "s")),
					"TOPICS" => \CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"])),
					"MESSAGE" => \CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_MESSAGE"],
							array("FID" => $res["ID"], "TID" => $res["TID"], "TITLE_SEO" => $res["TITLE_SEO"],
							      "MID" => $res["LAST_MESSAGE_ID"]))."#message".$res["LAST_MESSAGE_ID"],
					"AUTHOR" => \CComponentEngine::MakePathFromTemplate($this->arParams["URL_TEMPLATES_PROFILE_VIEW"],
						array("UID" => $res["LAST_POSTER_ID"]))	);
				/************** For custom template ********************************/
				$res["topic_list"] = $res["URL"]["TOPICS"];
				$res["message_appr"] = $res["URL"]["MODERATE_MESSAGE"];
				$res["message_list"] = $res["URL"]["MESSAGE"];
				$res["profile_view"] = $res["URL"]["AUTHOR"];
				/*******************************************************************/
				$res["FORUM_GROUP_ID"] = intval($res["FORUM_GROUP_ID"]);
				$this->arResult["FORUMS_LIST"][$res["ID"]] = $res;
				\CForumCacheManager::SetTag($this->GetCachePath(), "forum_msg_count".$res["ID"]);
			}
			$this->EndResultCache();
		}
		//endregion

		//region actualizing forum data for certain user
		$canModerateAtLeastOneForumOnPage = $this->arResult["USER"]["CAN_MODERATE"] == "Y";
		$renewedForums = [];
		if ($dbRes = CForumNew::GetForumRenew(array("FORUM_ID" => array_keys($this->arResult["FORUMS_LIST"]))))
		{
			while($forum = $dbRes->fetch())
			{
				$renewedForums[$forum["FORUM_ID"]] = intval($res["TCRENEW"]);
			}
		}
		foreach ($this->arResult["FORUMS_LIST"] as &$forum)
		{
			$forum["PERMISSION"] = ForumCurrUserPermissions($forum["ID"]);
			if ($forum["PERMISSION"] >= "Q")
			{
				foreach(["POSTER_ID", "POST_DATE", "POSTER_NAME", "MESSAGE_ID"] as $key)
				{
					$forum["~LAST_".$key] = $forum["~ABS_LAST_".$key];
					$forum["LAST_".$key] = $forum["ABS_LAST_".$key];
				}
				$forum["TID"] = $forum["ABS_TID"];
				$forum["TITLE"] = $forum["ABS_TITLE"];
				$canModerateAtLeastOneForumOnPage = true;
			}
			$forum["~NewMessage"] = (array_key_exists($forum["ID"], $renewedForums) ? $renewedForums[$forum["ID"]] : 0);
			$forum["NewMessage"] = ($forum["~NewMessage"] > 0 ? "Y" : "N");
		}
		unset($forum);
		$this->arResult["USER"]["CAN_MODERATE"] = ($canModerateAtLeastOneForumOnPage ? "Y" : "N");
		//endregion

		//region making structured forum array
		$plainGroups = [];

		foreach ($this->arResult["FORUMS_LIST"] as $forumId => $forum)
		{
			if (!array_key_exists($forum["FORUM_GROUP_ID"], $plainGroups))
			{
				$plainGroups[$forum["FORUM_GROUP_ID"]] = [];
				if (array_key_exists($forum["FORUM_GROUP_ID"], $this->arResult["GROUPS"]))
				{
					$plainGroups[$forum["FORUM_GROUP_ID"]] = $this->arResult["GROUPS"][$forum["FORUM_GROUP_ID"]];
				}
				$plainGroups[$forum["FORUM_GROUP_ID"]] += ["GROUPS" => [], "FORUMS" => []];
			}
			$plainGroups[$forum["FORUM_GROUP_ID"]]["FORUMS"][] = $forum;
		}
		$this->arResult["FORUM"] = $plainGroups;

		$treeGroup = $plainGroups;
		foreach ($treeGroup as $id => $group)
		{
			if ($group["LEFT_MARGIN"] >= 1)
			{
				$parentId = intval($group["PARENT_ID"]);
				$treeGroup[$parentId]["GROUPS"][$id] = &$treeGroup[$id];
			}
		}
		$this->arResult["FORUMS"] = reset($treeGroup);

		if (key($treeGroup) > 0) // for template
		{
			$this->arResult["FORUMS"] = ["GROUPS" => $treeGroup];
		}
		//endregion

		//region getting forums allowed for the guest
		unset($filter["APPROVED"]);
		$filter["PERMS"] = array(2, "A");
		$filter["ACTIVE"] = "Y";
		$filter["LID"] = SITE_ID;

		$cache_id = "forums_for_guest_".serialize($filter);
		if(($tzOffset = CTimeZone::GetOffset()) <> 0)
			$cache_id .= "_".$tzOffset;
		$forums = array();
		if ($this->arParams["CACHE_TIME"] > 0 && $cache->InitCache($this->arParams["CACHE_TIME"], $cache_id, $cache_path))
		{
			$forums = $cache->GetVars();
		}
		if (!is_array($forums) || count($forums) <= 0)
		{
			$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $filter);
			if (!defined("forum_index_11_5_0"))
			{
				if ($res = $db_res->GetNext())
					$forums[$res["ID"]] = $res;
			}
			else
			{
				while ($res = $db_res->GetNext())
					$forums[$res["ID"]] = $res;
			}

			if ($this->arParams["CACHE_TIME"] > 0):
				$cache->StartDataCache($this->arParams["CACHE_TIME"], $cache_id, $cache_path);
				$cache->EndDataCache($forums);
			endif;
		}
		$this->arResult["FORUMS_FOR_GUEST"] = $forums;
		//endregion

		//region for custom templates
		$this->arResult["FORUMS_LIST"] = array_combine(array_keys($this->arResult["FORUMS_LIST"]), array_keys($this->arResult["FORUMS_LIST"]));
		$this->arResult["index"] = $this->arResult["URL"]["INDEX"];
		$this->arResult["DrawAddColumn"] = ($this->arResult["USER"]["CAN_MODERATE"] == "Y" ? "Y" : "N");
		$this->arResult["PARSER"] = $parser;
		//endregion
	}

	private function setNavigation()
	{
		if (!is_array($this->arResult["GROUP"]))
		{
			return;
		}

		$parentId = $this->arResult["GROUP"]["PARENT_ID"];
		$chainList = [];
		while ($parentId > 0 && array_key_exists($parentId, $this->arResult["GROUPS"]))
		{
			array_unshift($chainList, [
				"name" => $this->arResult["GROUPS"][$parentId]["NAME"],
				"url" => \CComponentEngine::MakePathFromTemplate(
					$this->arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $parentId))
			]);
			$parentId = $this->arResult["GROUPS"][$parentId]["PARENT_ID"];
		}
		foreach ($chainList as $res)
		{
			$this->getApplication()->AddChainItem($res["name"], $res["url"]);
		}
		$this->getApplication()->AddChainItem($this->arResult["GROUP"]["NAME"]);
	}
	private function setTitle()
	{
		$sTitle = ($this->arParams["GID"] <= 0 ? GetMessage("F_TITLE") : $this->arResult["GROUP"]["NAME"]);
		$this->getApplication()->SetTitle($sTitle);
	}

	private function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	private function getUser()
	{
		global $USER;
		return $USER;
	}

}