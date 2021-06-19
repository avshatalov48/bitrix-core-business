<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Application;
Loc::loadMessages(__FILE__);

//need for class CDemoSqr
CBitrixComponent::includeComponentClass("bitrix:blog.post.comment");

class CBlogPostCommentList extends CBlogPostCommentEdit
{
	protected function createCacheId($uniqueString = "")
	{
		$cacheId = parent::createCacheId($uniqueString);
		return $cacheId.'_list';
	}
	
	/**
	 * Create list of ALL comments for this post, but with just base parameters.
	 * Need to small cache of comments list, to convert them in tree or flat list.
	 * And next we can add additional params only for visible elements.
	 */
	protected function createCommentsList()
	{
		$cache = new CPHPCache;
		$uniqueCacheString = $this->createCacheIdByPagination();
		$cacheId = $this->createCacheId("comments_".$uniqueCacheString);
		$cachePath = $this->createCachePath();
		if ($this->arParams["CACHE_TIME"] > 0 && $cache->InitCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath))
		{
			$vars = $cache->GetVars();
			$this->arResult = array_merge($this->arResult, $vars["arResult"]);

			$template = new CBitrixComponentTemplate();
			$template->ApplyCachedData($vars["templateCachedData"]);

			$cache->Output();
		}
		else
		{
			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath);

//			PROCESS
//			URLs
			$request = Application::getInstance()->getContext()->getRequest();
			$uri = new Uri($request->getRequestUri());
			$this->arResult["urlToHide"] = $uri->deleteParams(array("sessid", "delete_comment_id", "hide_comment_id", "success", "show_comment_id", "commentId"))
				->addParams(array("hide_comment_id" => "#comment_id#"))
				->getUri();
			$this->arResult["urlToDelete"] = $uri->deleteParams(array("sessid", "delete_comment_id", "hide_comment_id", "success", "show_comment_id", "commentId"))
				->addParams(array("delete_comment_id" => "#comment_id#"))
				->getUri();
			$this->arResult["urlToShow"] = $uri->deleteParams(array("sessid", "delete_comment_id", "hide_comment_id", "success", "show_comment_id", "commentId"))
				->getUri();
			
//			for main.post.list params
			$this->arResult["PUSH&PULL"] = false;
			$this->arResult["LAZYLOAD"] = "Y";
			$this->arParams["mfi"] = md5("blog.comments");
			
//			create GET LIST params
			$arOrder = Array("DATE_CREATE" => "DESC", "ID" => "DESC");
			$arFilter = Array("POST_ID" => $this->arParams["ID"], "BLOG_ID" => $this->arResult["Blog"]["ID"]);
//			hide draft comments. Hidden of current user must be showed, other hidden we hide later
			if (!($this->arResult["Perm"] >= BLOG_PERMS_MODERATE || $this->arParams["BLOG_MODULE_PERMS"] >= "W"))
				$arFilter["PUBLISH_STATUS"] = array(BLOG_PUBLISH_STATUS_PUBLISH);
//			AJAX - use only one comment
			if ($this->arResult["is_ajax_post"] == "Y" && intval($this->arResult["ajax_comment"]) > 0)
			{
				$arFilter["ID"] = $this->arResult["ajax_comment"];
//				for ajax - show hidden comment too, but only once
				$arFilter["PUBLISH_STATUS"] = array(BLOG_PUBLISH_STATUS_PUBLISH, BLOG_PUBLISH_STATUS_READY);
			}
//			PAGEN params
			CPageOption::SetOptionString("main", "nav_page_in_session", "N");
			if (!empty($_REQUEST["FILTER"]))
				$arFilter = array_merge($_REQUEST["FILTER"], $arFilter);
			$arSelectedFields = Array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL",
				"AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "DATE_CREATE", "PUBLISH_STATUS");
			$dbComment = CBlogComment::GetList($arOrder, $arFilter, false, false, $arSelectedFields);
			
			$this->arResult["NAV_RESULT"] = $dbComment;
			$this->arResult["NAV_RESULT"]->NavStart($this->arParams["COMMENTS_COUNT"], false);
			$this->arResult["NAV_STRING"] = $uri->deleteParams(array("PAGEN_" . $this->arResult["NAV_RESULT"]->NavNum))->getUri();
			
//			create params for every COMMENT
			$resComments = Array();
			$textParser = new blogTextParser(false, $this->arParams["PATH_TO_SMILE"]);    // for convert title and text
			while ($comment = $dbComment->GetNext())
			{
//				check HIDDEN comments and unset they from result.
//				or show current hidden comment, but only once in ajax-mode
				if (
					(
						!($this->arResult["Perm"] >= BLOG_PERMS_MODERATE || $this->arParams["BLOG_MODULE_PERMS"] >= BLOG_PERMS_FULL)
						&& $comment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
					)
					&&
					!(
						$comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY && $this->arResult["Perm"] == BLOG_PUBLISH_STATUS_READY
						&& $this->arResult["is_ajax_post"] == "Y" && intval($this->arResult["ajax_comment"]) > 0
						&& $this->arResult["ajax_comment"] == $comment["ID"]
					)
				)
					continue;
				
				$comment = $this->createAdditionalCommentParams($comment, $textParser);
				$resComments[intval($comment["ID"])] = $comment;
				
//				save IDs in another array
				$this->arResult["IDS"][] = $comment["ID"];
				
//				save unsorted comments in another array
				$this->arResult["Comments"][$comment["ID"]] = Array(
					"ID" => $comment["ID"],
					"PARENT_ID" => $comment["PARENT_ID"],
					"PUBLISH_STATUS" => $comment["PUBLISH_STATUS"],
				);
			}
			$this->arResult["CommentsResult"] = $resComments;
			
//			was deleted last element - have no comments
			if (empty($resComments))
				$this->arResult["PUSH&PULL"] = array(
					"ID" => $this->arResult["ajax_comment"],
					"ACTION" => "DELETE"
				);
			
//			RATING
			if ($this->arParams["SHOW_RATING"] == "Y" && !empty($this->arResult["IDS"]))
				$this->arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_COMMENT', $this->arResult["IDS"]);
			
//			set params for view all comments properties
			$this->createCommentsProperties();
//			end PROCESS
			
			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $this->arResult));
		}
	}
	
	protected function createAdditionalCommentsParams()
	{
//		do nothing - in list we create all params alter
	}
	
	
	protected function createAdditionalCommentParams($comment, blogTextParser $textParser)
	{
		$comment = parent::createAdditionalCommentParams($comment, $textParser);
		
//		in new comments we have not TITLE field - add title to message
		$comment["TextFormated"] = $this->addTitleToMessage($comment["TextFormated"], $comment["TITLE"]);
//		AUTHOR params will be specialchared in main.post.list later, use iroginal values
		$comment["AUTHOR"] = array(
			"NAME" => $comment["arUser"]["~NAME"],
			"LAST_NAME" => $comment["arUser"]["~LAST_NAME"],
			"SECOND_NAME" => $comment["arUser"]["~SECOND_NAME"],
			"LOGIN" => $comment["arUser"]["~LOGIN"],
		);
		$comment["POST_TIMESTAMP"] = MakeTimeStamp($comment["DATE_CREATE"]);
		$comment["POST_MESSAGE_TEXT"] = $comment["TextFormated"];
		$comment["~POST_MESSAGE_TEXT"] = $comment["~POST_TEXT"];
		$comment["APPROVED"] = ($comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH ? "Y" : "N");
		$comment["FILES"] = array();
		$comment["UF"] = array();
		$comment["CLASSNAME"] = "";
		$comment["BEFORE_HEADER"] = "";
		$comment["BEFORE_ACTIONS"] = "";
		$comment["AFTER_ACTIONS"] = "";
		$comment["AFTER_HEADER"] = "";
		$comment["BEFORE"] = "";
		$comment["AFTER"] = "";
		$comment["BEFORE_RECORD"] = "";
		$comment["AFTER_RECORD"] = "";
		
//		COMMENT DATA for js
		$jsString = 'top.text' . $comment["ID"] . '= "' . CUtil::JSescape($comment["POST_TEXT"]) . '";';
//		todo: images and files
		if($jsString <> '')
			$comment["BEFORE"] = "<script>".$jsString."</script>";
		
//		AUTHOR registered or unregister params
		if(intval($comment["AUTHOR_ID"]) > 0)
			$comment["AUTHOR"]["AVATAR"] = $comment["BlogUser"]["Avatar_resized"][$this->arParams["AVATAR_SIZE_COMMENT"]."_".$this->arParams["AVATAR_SIZE_COMMENT"]]["src"];
		else
			$comment["AUTHOR"] = array("NAME" => $comment["AUTHOR_NAME"]);
//		it need to preserve name generate in main.post.list. We must use preload values from arUser
		unset($comment["AUTHOR_ID"], $comment["~AUTHOR_ID"]);
		
//		FILES
		if(!empty($this->arResult["arImages"][$comment["ID"]]))
		{
			foreach($this->arResult["arImages"][$comment["ID"]] as $imageId => $val)
			{
				$comment["FILES"][] = array(
					"THUMBNAIL" => $val["small"],
					"SRC" => $val["full"],
					"FILE_SIZE" => $val["FILE_SIZE"],
					"CONTENT_TYPE" => $val["CONTENT_TYPE"],
					"ORIGINAL_NAME" => $val["ORIGINAL_NAME"],
					"FILE_NAME" => $val["ORIGINAL_NAME"]
				);
			}
		}
		
//		PUSH&PULL
		if (intval($this->arResult["ajax_comment"]) == intval($comment["ID"]))
		{
			$this->arResult["PUSH&PULL"] = array(
				"ID" => $comment["ID"],
				"ACTION" => (
					$_GET["delete_comment_id"] == $this->arResult["ajax_comment"] ? "DELETE" : (
						$_GET["show_comment_id"] == $this->arResult["ajax_comment"] || $_GET["hide_comment_id"] == $this->arResult["ajax_comment"] ? "MODERATE" : (
							$_POST["act"] == "edit" ? "EDIT" : "REPLY"
						)
					)
				)
			);
		}
		
		return $comment;
	}
	
	protected function markNewComments()
	{
		$this->saveLastPostView();
		foreach ($this->arResult["CommentsResult"] as $key => $comment)
		{
			if($this->arResult["lastPostView"] <> '' && $this->arResult["lastPostView"] < MakeTimeStamp($comment["DATE_CREATE"]))
				$this->arResult["CommentsResult"][$key]["NEW"] = "Y";
		}
	}
	
	private function addTitleToMessage($message, $title)
	{
		$messageWithTitle = '';
		if($title <> '')
			$messageWithTitle .= "<b>" . $title . "</b><br>";
		$messageWithTitle .= $message;
		
		return $messageWithTitle;
	}
	
	private function createCacheIdByPagination()
	{
		$str = "";
		
		if(!empty($_REQUEST["FILTER"]))
			$str .= "FILTER".serialize($_REQUEST["FILTER"]);
		
		foreach($_REQUEST as $key => $value)
		{
			if(mb_strpos($key, "PAGEN_") !== false)
				$str .= $key."-".$value;
		}
		
		return $str;
	}
}

?>