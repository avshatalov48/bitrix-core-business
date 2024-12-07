<?php

IncludeModuleLangFile(__FILE__);

$GLOBALS["BLOG_COMMENT"] = Array();

class CAllBlogComment
{
	const UF_NAME = 'UF_BLOG_COMMENT_DOC';
	/*************** ADD, UPDATE, DELETE *****************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $APPLICATION;

		if ((is_set($arFields, "BLOG_ID") || $ACTION=="ADD") && intval($arFields["BLOG_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GCM_EMPTY_BLOG_ID"), "EMPTY_BLOG_ID");
			return false;
		}
		elseif (is_set($arFields, "BLOG_ID"))
		{
			$arResult = CBlog::GetByID($arFields["BLOG_ID"]);
			if (!$arResult)
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["BLOG_ID"], GetMessage("BLG_GCM_ERROR_NO_BLOG")), "ERROR_NO_BLOG");
				return false;
			}
		}

		if ((is_set($arFields, "POST_ID") || $ACTION=="ADD") && intval($arFields["POST_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GCM_EMPTY_POST_ID"), "EMPTY_POST_ID");
			return false;
		}
		elseif (is_set($arFields, "POST_ID"))
		{
			$arResult = CBlogPost::GetByID($arFields["POST_ID"]);
			if (!$arResult)
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["POST_ID"], GetMessage("BLG_GCM_ERROR_NO_POST")), "ERROR_NO_POST");
				return false;
			}
		}

		if (is_set($arFields, "PARENT_ID") && $arFields["PARENT_ID"])
		{
			$arResult = CBlogComment::GetByID($arFields["PARENT_ID"]);
			if (!$arResult)
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["PARENT_ID"], GetMessage("BLG_GCM_ERROR_NO_COMMENT")), "ERROR_NO_COMMENT");
				return false;
			}
		}

		if (is_set($arFields, "AUTHOR_ID"))
		{
			if (intval($arFields["AUTHOR_ID"]) <= 0)
			{
				$APPLICATION->ThrowException(GetMessage("BLG_GCM_EMPTY_AUTHOR_ID"), "EMPTY_AUTHOR_ID");
				return false;
			}
			else
			{
				$dbResult = CUser::GetByID($arFields["AUTHOR_ID"]);
				if (!$dbResult->Fetch())
				{
					$APPLICATION->ThrowException(GetMessage("BLG_GCM_ERROR_NO_AUTHOR_ID"), "ERROR_NO_AUTHOR_ID");
					return false;
				}
			}
		}
		else
		{
			if ((is_set($arFields, "AUTHOR_NAME") || $ACTION=="ADD") && $arFields["AUTHOR_NAME"] == '')
			{
				$APPLICATION->ThrowException(GetMessage("BLG_GCM_EMPTY_AUTHOR_NAME"), "EMPTY_AUTHOR_NAME");
				return false;
			}
		}

		if (is_set($arFields, "AUTHOR_EMAIL") && $arFields["AUTHOR_EMAIL"] <> '')
		{
			if (!check_email($arFields["AUTHOR_EMAIL"]))
			{
				$APPLICATION->ThrowException(GetMessage("BLG_GCM_ERROR_AUTHOR_EMAIL"), "ERROR_AUTHOR_EMAIL");
				return false;
			}
		}

		if ((is_set($arFields, "DATE_CREATE") || $ACTION=="ADD") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GCM_ERROR_DATE_CREATE"), "ERROR_DATE_CREATE");
			return false;
		}

		if ((is_set($arFields, "POST_TEXT") || $ACTION=="ADD") && $arFields["POST_TEXT"] == '')
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GCM_EMPTY_POST_TEXT"), "EMPTY_POST_TEXT");
			return false;
		}

		if (!empty($arFields["POST_TEXT"]))
		{
			$arFields["POST_TEXT"] = \Bitrix\Main\Text\Emoji::encode($arFields["POST_TEXT"]);
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB, $USER_FIELD_MANAGER;

		$ID = intval($ID);

		$arResult = CBlogComment::GetByID($ID);

		foreach(GetModuleEvents("blog", "OnBeforeCommentDelete", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, Array($ID))===false)
				return false;
		}

		if ($arResult)
		{
			$DB->Query(
				"UPDATE b_blog_comment SET ".
				"	PARENT_ID = ".((intval($arResult["PARENT_ID"]) > 0) ? intval($arResult["PARENT_ID"]) : "null")." ".
				"WHERE PARENT_ID = ".$ID." ".
				"	AND BLOG_ID = ".intval($arResult["BLOG_ID"])." ".
				"	AND POST_ID = ".intval($arResult["POST_ID"])." ",
				true
			);

			$updateFields = [
				"=NUM_COMMENTS_ALL" => "NUM_COMMENTS_ALL - 1"
			];
			if($arResult["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_PUBLISH)
			{
				$updateFields["=NUM_COMMENTS"] = "NUM_COMMENTS - 1";
			}
			CBlogPost::Update($arResult["POST_ID"], $updateFields);

			$res = CBlogImage::GetList(array(), array("BLOG_ID" => $arResult["BLOG_ID"], "POST_ID"=>$arResult["POST_ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => $ID));
			while($aImg = $res->Fetch())
				CBlogImage::Delete($aImg['ID']);

			$USER_FIELD_MANAGER->Delete("BLOG_COMMENT", $ID);
		}

		unset($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]);

		foreach(GetModuleEvents("blog", "OnCommentDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, Array($ID));

		if (CModule::IncludeModule("search"))
		{
			CSearch::Index("blog", "C".$ID,
				array(
					"TITLE" => "",
					"BODY" => ""
				)
			);
		}

		return $DB->Query("DELETE FROM b_blog_comment WHERE ID = ".$ID."", true);
	}

	//*************** SELECT *********************/
	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);

		if (isset($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]) && is_array($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]) && is_set($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT C.ID, C.BLOG_ID, C.POST_ID, C.PARENT_ID, C.AUTHOR_ID, C.AUTHOR_NAME, ".
				"	C.AUTHOR_EMAIL, C.AUTHOR_IP, C.AUTHOR_IP1, C.TITLE, C.POST_TEXT, ".
				"	".$DB->DateToCharFunction("C.DATE_CREATE", "FULL")." as DATE_CREATE, ".
				"	C.PUBLISH_STATUS, C.PATH, C.SHARE_DEST ".
				"FROM b_blog_comment C ".
				"WHERE C.ID = ".$ID."";
			$dbResult = $DB->Query($strSql);
			if ($arResult = $dbResult->Fetch())
			{
				$GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID] = $arResult;
				return $arResult;
			}
		}

		return False;
	}

	public static function BuildRSS($postID, $blogID, $type = "RSS2.0", $numPosts = 10, $arPathTemplate = Array())
	{
		global $USER;

		$blogID = intval($blogID);
		$postID = intval($postID);
		if($blogID <= 0)
			return false;
		if($postID <= 0)
			return false;
		$numPosts = intval($numPosts);
		$type = mb_strtolower(preg_replace("/[^a-zA-Z0-9.]/is", "", $type));
		if ($type != "rss.92" && $type != "atom.03")
			$type = "rss2.0";

		$rssText = False;

		$arBlog = CBlog::GetByID($blogID);
		if ($arBlog && $arBlog["ACTIVE"] == "Y" && $arBlog["ENABLE_RSS"] == "Y")
		{
			$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
			if($arGroup["SITE_ID"] == SITE_ID)
			{
				$arPost = CBlogPost::GetByID($postID);
				if(!empty($arPost) && $arPost["BLOG_ID"] == $arBlog["ID"] && $arPost["ENABLE_COMMENTS"] == "Y")
				{
					$now = date("r");
					$nowISO = date("Y-m-d\TH:i:s").mb_substr(date("O"), 0, 3).":".mb_substr(date("O"), -2, 2);

					$serverName = "";
					$charset = "";
					$language = "";
					$dbSite = CSite::GetList("sort", "asc", array("LID" => SITE_ID));
					if ($arSite = $dbSite->Fetch())
					{
						$serverName = $arSite["SERVER_NAME"];
						$charset = $arSite["CHARSET"];
						$language = $arSite["LANGUAGE_ID"];
					}

					if ($serverName == '')
					{
						if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
							$serverName = SITE_SERVER_NAME;
						else
							$serverName = COption::GetOptionString("main", "server_name", "");
					}
					$serverName = \Bitrix\Main\Text\HtmlFilter::encode($serverName);

					if ($charset == '')
					{
						if (defined("SITE_CHARSET") && SITE_CHARSET <> '')
							$charset = SITE_CHARSET;
						else
							$charset = "windows-1251";
					}

					if($arPathTemplate["PATH_TO_BLOG"] <> '')
						$blogURL = "http://".$serverName.htmlspecialcharsbx(CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"])));
					else
						$blogURL = "http://".$serverName.htmlspecialcharsbx(CBlog::PreparePath($arBlog["URL"], $arGroup["SITE_ID"]));

					if($arPathTemplate["PATH_TO_POST"] <> '')
						$url = "http://".$serverName.htmlspecialcharsbx(CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arPathTemplate["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"])));
					else
						$url = "http://".$serverName.htmlspecialcharsbx(CBlogPost::PreparePath($arBlog["URL"], $arPost["ID"], $arGroup["SITE_ID"]));

					$dbUser = CUser::GetByID($arPost["AUTHOR_ID"]);
					$arUser = $dbUser->Fetch();

					if($arPathTemplate["USE_SOCNET"] == "Y")
					{
						$blogName = GetMessage("BLG_GCM_RSS_TITLE_SOCNET", Array("#AUTHOR_NAME#" => htmlspecialcharsEx($arUser["NAME"]." ".$arUser["LAST_NAME"]), "#POST_TITLE#" => htmlspecialcharsEx($arPost["TITLE"])));
					}
					else
					{
						$blogName = GetMessage("BLG_GCM_RSS_TITLE", Array("#BLOG_NAME#" => htmlspecialcharsEx($arBlog["NAME"]), "#POST_TITLE#" => htmlspecialcharsEx($arPost["TITLE"])));
					}

					$rssText = "";
					if ($type == "rss.92")
					{
						$rssText .= "<"."?xml version=\"1.0\" encoding=\"".$charset."\"?".">\n\n";
						$rssText .= "<rss version=\".92\">\n";
						$rssText .= " <channel>\n";
						$rssText .= "	<title>".$blogName."</title>\n";
						$rssText .= "	<description>".$blogName."</description>\n";
						$rssText .= "	<link>".$url."</link>\n";
						$rssText .= "	<language>".$language."</language>\n";
						$rssText .= "	<docs>http://backend.userland.com/rss092</docs>\n";
						$rssText .= "\n";
					}
					elseif ($type == "rss2.0")
					{
						$rssText .= "<"."?xml version=\"1.0\" encoding=\"".$charset."\"?".">\n\n";
						$rssText .= "<rss version=\"2.0\">\n";
						$rssText .= " <channel>\n";
						$rssText .= "	<title>".$blogName."</title>\n";
						$rssText .= "	<description>".$blogName."</description>\n";
						//$rssText .= "	<guid>".$url."</guid>\n";
						$rssText .= "	<link>".$url."</link>\n";
						$rssText .= "	<language>".$language."</language>\n";
						$rssText .= "	<docs>http://backend.userland.com/rss2</docs>\n";
						$rssText .= "	<pubDate>".$now."</pubDate>\n";
						$rssText .= "\n";
					}
					elseif ($type == "atom.03")
					{
						$atomID = "tag:".$serverName.",".date("Y-m-d").":".$postID;

						$rssText .= "<"."?xml version=\"1.0\" encoding=\"".$charset."\"?".">\n\n";
						$rssText .= "<feed version=\"0.3\" xmlns=\"http://purl.org/atom/ns#\" xml:lang=\"".$language."\">\n";
						$rssText .= "  <title>".$blogName."</title>\n";
						$rssText .= "  <tagline>".$url."</tagline>\n";
						$rssText .= "  <id>".$atomID."</id>\n";
						$rssText .= "  <link rel=\"alternate\" type=\"text/html\" href=\"".$url."\" />\n";
						$rssText .= "  <modified>".$nowISO."</modified>\n";

						$BlogUser = CBlogUser::GetByID($arPost["AUTHOR_ID"], BLOG_BY_USER_ID);
						$authorP = htmlspecialcharsex(CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"], $arUser["SECOND_NAME"]));
						if($arPathTemplate["PATH_TO_USER"] <> '')
							$authorURLP = "http://".$serverName.htmlspecialcharsbx(CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_USER"], array("user_id"=>$arPost["AUTHOR_ID"])));
						else
							$authorURLP = "http://".$serverName.htmlspecialcharsbx(CBlogUser::PreparePath($arPost["AUTHOR_ID"], $arGroup["SITE_ID"]));

						$rssText .= "  <author>\n";
						$rssText .= "  		<name>".$authorP."</name>\n";
						$rssText .= "  		<uri>".$authorURLP."</uri>\n";
						$rssText .= "  </author>\n";

						$rssText .= "\n";
					}

					$user_id = $USER->GetID();
					if($arPathTemplate["USE_SOCNET"] == "Y")
					{
						$postPerm = CBlogPost::GetSocNetPostPerms($postID);
						if($postPerm > BLOG_PERMS_DENY)
							$postPerm = CBlogComment::GetSocNetUserPerms($postID, $arPost["AUTHOR_ID"]);
					}
					else
						$postPerm = CBlogPost::GetBlogUserCommentPerms($postID, intval($user_id));

					if($postPerm >= BLOG_PERMS_READ)
					{
						$parser = new blogTextParser();
						$arParserParams = Array(
							"imageWidth" => $arPathTemplate["IMAGE_MAX_WIDTH"],
							"imageHeight" => $arPathTemplate["IMAGE_MAX_HEIGHT"],
						);

						CTimeZone::Disable();
						$dbComments = CBlogComment::GetList(
							array("DATE_CREATE" => "DESC"),
							array(
								//"BLOG_ID" => $blogID,
								"POST_ID" => $postID,
								"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
							),
							false,
							array("nTopCount" => $numPosts),
							array("ID", "TITLE", "DATE_CREATE", "POST_TEXT", "AUTHOR_EMAIL", "AUTHOR_ID", "AUTHOR_NAME", "USER_LOGIN", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_NAME", "BLOG_USER_ALIAS")
						);
						CTimeZone::Enable();
						$arImages = Array();
						$dbImages = CBlogImage::GetList(Array(), Array("BLOG_ID" => $blogID, "POST_ID" => $postID, "IS_COMMENT" => "Y", "!COMMENT_ID" => false));
						while($arI = $dbImages->Fetch())
							$arImages[$arI["ID"]] = $arI["FILE_ID"];

						while ($arComments = $dbComments->Fetch())
						{
							$arDate = ParseDateTime($arComments["DATE_CREATE"], CSite::GetDateFormat("FULL", $arGroup["SITE_ID"]));
							$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));

							if(mb_strpos($url, "?") !== false)
								$url1 = $url."&amp;";
							else
								$url1 = $url."?";
							$url1 .= "commentId=".$arComments["ID"]."#com".$arComments["ID"];

							$authorURL = "";
							if(intval($arComments["AUTHOR_ID"]) > 0)
							{
								$author = CBlogUser::GetUserName($arComments["BLOG_USER_ALIAS"], $arComments["USER_NAME"], $arComments["USER_LAST_NAME"], $arComments["USER_LOGIN"], $arComments["USER_SECOND_NAME"]);
								if($arPathTemplate["PATH_TO_USER"] <> '')
									$authorURL = "http://".$serverName.htmlspecialcharsbx(CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_USER"], array("user_id"=>$arComments["AUTHOR_ID"])));
								else
									$authorURL = "http://".$serverName.htmlspecialcharsbx(CBlogUser::PreparePath($arComments["AUTHOR_ID"], $arGroup["SITE_ID"]));
							}
							else
								$author = $arComments["AUTHOR_NAME"];
							$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N");
							if($arPathTemplate["NO_URL_IN_COMMENTS"] == "L" || (intval($arComments["AUTHOR_ID"]) <= 0  && $arPathTemplate["NO_URL_IN_COMMENTS"] == "A"))
								$arAllow["CUT_ANCHOR"] = "Y";

							if($arPathTemplate["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && $arAllow["CUT_ANCHOR"] != "Y" && intval($arComments["AUTHOR_ID"]) > 0)
							{
								$authorityRatingId = CRatings::GetAuthorityRating();
								$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $arComments["AUTHOR_ID"]);
								if($arRatingResult["CURRENT_VALUE"] < $arPathTemplate["NO_URL_IN_COMMENTS_AUTHORITY"])
									$arAllow["CUT_ANCHOR"] = "Y";
							}

							$text = $parser->convert_to_rss($arComments["POST_TEXT"], $arImages, $arAllow, false, $arParserParams);

							$title = GetMessage("BLG_GCM_COMMENT_TITLE", Array("#POST_TITLE#" => htmlspecialcharsEx($arPost["TITLE"]), "#COMMENT_AUTHOR#" => htmlspecialcharsEx($author)));
							/*$title = str_replace(
								array("&", "<", ">", "\""),
								array("&amp;", "&lt;", "&gt;", "&quot;"),
								$title);
							*/
							//$text1 = HTMLToTxt($text, "", Array("\&nbsp;"), 60);
							$text = "<![CDATA[".$text."]]>";


							if ($type == "rss.92")
							{
								$rssText .= "    <item>\n";
								$rssText .= "      <title>".$title."</title>\n";
								$rssText .= "      <description>".$text."</description>\n";
								$rssText .= "      <link>".$url1."</link>\n";
								$rssText .= "    </item>\n";
								$rssText .= "\n";
							}
							elseif ($type == "rss2.0")
							{
								$rssText .= "    <item>\n";
								$rssText .= "      <title>".$title."</title>\n";
								$rssText .= "      <description>".$text."</description>\n";
								$rssText .= "      <link>".$url1."</link>\n";
								$rssText .= "      <guid>".$url1."</guid>\n";
								$rssText .= "      <pubDate>".$date."</pubDate>\n";
								$rssText .= "    </item>\n";
								$rssText .= "\n";
							}
							elseif ($type == "atom.03")
							{
								$atomID = "tag:".$serverName.":".$arBlog["URL"]."/".$arPost["ID"];

								$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
								$dateISO = date("Y-m-d\TH:i:s", $timeISO).mb_substr(date("O", $timeISO), 0, 3).":".mb_substr(date("O", $timeISO), -2, 2);

								$rssText .= "<entry>\n";
								$rssText .= "  <title type=\"text/html\">".$title."</title>\n";
								$rssText .= "  <link rel=\"alternate\" type=\"text/html\" href=\"".$url1."\"/>\n";
								$rssText .= "  <issued>".$dateISO."</issued>\n";
								$rssText .= "  <modified>".$nowISO."</modified>\n";
								$rssText .= "  <id>".$atomID."</id>\n";
								$rssText .= "  <content type=\"text/html\" mode=\"escaped\" xml:lang=\"".$language."\" xml:base=\"".$blogURL."\">\n";
								$rssText .= $text."\n";
								$rssText .= "  </content>\n";
								$rssText .= "  <author>\n";
								$rssText .= "    <name>".htmlspecialcharsex($author)."</name>\n";
								if($authorURL <> '')
									$rssText .= "    <uri>".$authorURL."</uri>\n";
								$rssText .= "  </author>\n";
								$rssText .= "</entry>\n";
								$rssText .= "\n";
							}
						}
					}

					if ($type == "rss.92")
						$rssText .= "  </channel>\n</rss>";
					elseif ($type == "rss2.0")
						$rssText .= "  </channel>\n</rss>";
					elseif ($type == "atom.03")
						$rssText .= "\n\n</feed>";
				}
			}
		}

		return $rssText;
	}

	public static function _IndexPostComments($arParams = Array())
	{
		if(intval($arParams["BLOG_ID"]) <= 0 || intval($arParams["POST_ID"]) <= 0 || !CModule::IncludeModule("search"))
			return false;
		if($arParams["USE_SOCNET"] == "Y")
			$arSp = CBlogComment::GetSocNetCommentPerms($arParams["POST_ID"]);

		$dbComment = CBlogComment::GetList(Array(), Array("BLOG_ID" => $arParams["BLOG_ID"], "POST_ID" => $arParams["POST_ID"], "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH), false, false, Array("ID", "POST_ID", "BLOG_ID", "PUBLISH_STATUS", "PATH", "DATE_CREATE", "POST_TEXT", "TITLE", "AUTHOR_ID"));
		while($arComment = $dbComment->Fetch())
		{
			if($arComment["PATH"] <> '')
				$arComment["PATH"] = str_replace("#comment_id#", $arComment["ID"], $arComment["PATH"]);
			elseif($arParams["PATH"] <> '')
				$arComment["PATH"] = str_replace("#comment_id#", $arComment["ID"], $arParams["PATH"]);
			else
			{
				$arComment["PATH"] = CBlogPost::PreparePath(
							$arParams["BLOG_URL"],
							$arComment["POST_ID"],
							$arParams["SITE_ID"],
							false,
							$arParams["OWNER_ID"],
							$arParams["SOCNET_GROUP_ID"]
						);
			}

			$arSearchIndex = array(
				"SITE_ID" => array($arParams["SITE_ID"] => $arComment["PATH"]),
				"LAST_MODIFIED" => $arComment["DATE_CREATE"],
				"PARAM1" => "COMMENT",
				"PARAM2" => $arComment["BLOG_ID"]."|".$arComment["POST_ID"],
				"PERMISSIONS" => array(2),
				"TITLE" => $arComment["TITLE"],
				"BODY" => blogTextParser::killAllTags($arComment["POST_TEXT"]),
				"INDEX_TITLE" => false,
				"USER_ID" => (intval($arComment["AUTHOR_ID"]) > 0) ? $arComment["AUTHOR_ID"] : false,
				"ENTITY_TYPE_ID" => "BLOG_COMMENT",
				"ENTITY_ID" => $arComment["ID"],
			);
			if($arParams["USE_SOCNET"] == "Y")
			{
				$arSearchIndex["PERMISSIONS"] = $arSp;
				if(!in_array("U".$arComment["AUTHOR_ID"], $arSearchIndex["PERMISSIONS"]))
						$arSearchIndex["PERMISSIONS"][] = "U".$arComment["AUTHOR_ID"];

				if(is_array($arSp))
				{
					$sgId = array();
					foreach($arSp as $perm)
					{
						if(mb_strpos($perm, "SG") !== false)
						{
							$sgIdTmp = str_replace("SG", "", mb_substr($perm, 0, mb_strpos($perm, "_")));
							if(!in_array($sgIdTmp, $sgId) && intval($sgIdTmp) > 0)
								$sgId[] = $sgIdTmp;
						}
					}

					if(!empty($sgId))
					{
						$arSearchIndex["PARAMS"] = array(
							"socnet_group" => $sgId,
							"entity" => "socnet_group",
						);
					}
				}
			}
			if($arComment["TITLE"] == '')
				$arSearchIndex["TITLE"] = mb_substr($arSearchIndex["BODY"], 0, 100);

			CSearch::Index("blog", "C".$arComment["ID"], $arSearchIndex, True);
		}

		return true;
	}

	public static function UpdateLog($commentID, $arBlogUser, $arUser, $arComment, $arPost, $arParams)
	{
		if (!CModule::IncludeModule('socialnetwork'))
		{
			return;
		}

		$AuthorName = CBlogUser::GetUserName(
			$arBlogUser["~ALIAS"] ?? null,
			$arUser["~NAME"] ?? null,
			$arUser["~LAST_NAME"] ?? null,
			$arUser["~LOGIN"] ?? null,
			$arUser["~SECOND_NAME"] ?? null
		);
		$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);

		$arAllow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "N");
		$text4message = $parserBlog->convert($arComment["POST_TEXT"], false, $arParams["IMAGES"], $arAllow, array("isSonetLog"=>true));
		$text4mail = $parserBlog->convert4mail($arComment["POST_TEXT"], $arParams["IMAGES"]);

		$arSoFields = Array(
			"TITLE_TEMPLATE" => htmlspecialcharsback($AuthorName)." ".GetMessage("BLG_SONET_COMMENT_TITLE"),
			"TITLE" => $arPost['~TITLE'],
			"MESSAGE" => $text4message,
			"TEXT_MESSAGE" => $text4mail,
			"TAG" => \Bitrix\Socialnetwork\Util::detectTags($arComment, array('POST_TEXT'))
		);

		$dbRes = CSocNetLogComments::GetList(
			array("ID" => "DESC"),
			array(
				"EVENT_ID"	=> array("blog_comment", "blog_comment_micro"),
				"SOURCE_ID" => $commentID
			),
			false,
			false,
			array("ID")
		);
		while ($arRes = $dbRes->Fetch())
		{
			CSocNetLogComments::Update($arRes["ID"], $arSoFields);
		}
	}

	public static function DeleteLog($commentID)
	{
		if (!CModule::IncludeModule('socialnetwork'))
			return;

		$dbRes = CSocNetLogComments::GetList(
			array("ID" => "DESC"),
			array(
				"EVENT_ID"	=> array("blog_comment", "blog_comment_micro"),
				"SOURCE_ID" => $commentID
			),
			false,
			false,
			array("ID")
		);
		while ($arRes = $dbRes->Fetch())
			CSocNetLogComments::Delete($arRes["ID"]);
	}

	public static function GetSocNetPostsPerms($entity_type, $entity_id)
	{
		global $DB;
		$entity_id = intval($entity_id);

		$type = "U";
		$type2 = "US";
		if($entity_type == "G")
			$type = $type2 = "SG";

		return $DB->Query("SELECT C.ID, C.POST_ID
							FROM b_blog_comment C
							INNER JOIN b_blog_socnet_rights SR ON (C.POST_ID = SR.POST_ID AND SR.ENTITY_TYPE='".$type."' AND SR.ENTITY_ID=".$entity_id." AND SR.ENTITY = '".$type2.$entity_id."')");
	}

	public static function GetSocNetCommentPerms($postID = 0)
	{
		$postID = intval($postID);
		if($postID <= 0)
			return false;

		$arSp = Array();
		$sp = CBlogPost::GetSocnetPerms($postID);
		if(is_array($sp) && !empty($sp))
		{
			foreach($sp as $et => $v)
			{
				foreach($v as $eid => $tv)
				{
					if($et == "U" && in_array($et.$eid, $tv))
					{
						$arSp[] = $et.$eid;
					}
					elseif(in_array($et, Array("U", "SG")))
					{
						$spt = CBlogPost::GetSocnetGroups(($et == "SG" ? "G" : "U"), $eid, "view_comment");
						foreach($spt as $vv)
						{
							if(!in_array($vv, $arSp))
								$arSp[] = $vv;
						}
					}
					else
					{
						$arSp[] = $et.$eid;
					}
				}
			}
		}
		return $arSp;
	}

	public static function GetSocNetUserPerms($postId = 0, $authorId = 0, $userId = 0)
	{
		$permsBySG = false;
		return self::GetSocNetUserPermsNew($postId, $authorId, $userId, $permsBySG);
	}

	public static function GetSocNetUserPermsNew($postId = 0, $authorId = 0, $userId = 0, &$permsBySG = null)
	{
		global $APPLICATION, $USER, $AR_BLOG_PERMS;

		$bCurrent = false;
		if ($permsBySG === null)
		{
			$permsBySG = false;
		}

		if (
			!isset($userId)
			|| intval($userId) <= 0
		)
		{
			$userId = intval($USER->GetID());
			$bCurrent = true;
		}

		$postId = intval($postId);
		$authorId = intval($authorId);
		if ($postId <= 0)
		{
			return false;
		}

		$perms = BLOG_PERMS_DENY;

		$blogModulePermissions = $APPLICATION->GetGroupRight("blog", ($bCurrent ? false : CUser::GetUserGroup($userId)));

		if (
			$blogModulePermissions >= "W"
			|| ($bCurrent ? CSocNetUser::IsCurrentUserModuleAdmin() : CSocNetUser::IsUserModuleAdmin($userId))
		)
		{
			end($AR_BLOG_PERMS);
			$perms = key($AR_BLOG_PERMS);
			reset($AR_BLOG_PERMS);
		}

		$arPerms = CBlogPost::GetSocNetPerms($postId);
		$arGroupsId = array();
		if (
			!empty($arPerms["SG"])
			&& is_array($arPerms["SG"])
		)
		{
			foreach($arPerms["SG"] as $gid => $val)
			{
				//if(!empty($arEntities["SG"][$gid]))
				$arGroupsId[] = $gid;
			}
		}

		if (
			empty($arGroupsId)
			&& $authorId > 0
			&& $userId == $authorId
		)
		{
			$perms = BLOG_PERMS_MODERATE;
		}

		if($perms <= BLOG_PERMS_DENY)
		{
			$arEntities = Array();

			if (!empty(CBlogPost::$arUACCache[$userId]))
			{
				$arEntities = CBlogPost::$arUACCache[$userId];
			}
			else
			{
				$arCodes = CAccess::GetUserCodesArray($userId);
				foreach ($arCodes as $code)
				{
					if (
						preg_match('/^DR([0-9]+)/', $code, $match)
						|| preg_match('/^D([0-9]+)/', $code, $match)
						|| preg_match('/^IU([0-9]+)/', $code, $match)
					)
					{
						$arEntities["DR"][$code] = $code;
					}
					elseif (preg_match('/^SG([0-9]+)_([A-Z])/', $code, $match))
					{
						$arEntities["SG"][$match[1]][$match[2]] = $match[2];
					}
				}
				CBlogPost::$arUACCache[$userId] = $arEntities;
			}

			if (!empty($arEntities["DR"]) && !empty($arPerms["DR"]))
			{
				foreach($arPerms["DR"] as $id => $val)
				{
					if(isset($arEntities["DR"]["DR".$id]))
					{
						$perms = BLOG_PERMS_READ;
						break;
					}
				}
			}

			if($authorId <= 0)
			{
				foreach($arPerms["U"] as $id => $p)
				{
					if(in_array("US".$id, $p))
					{
						$authorId = $id;
						break;
					}
				}
			}

			if (
				$authorId > 0
				&& $userId == $authorId
			)
			{
				$perms = BLOG_PERMS_READ;
			}

			if (
				!empty($arPerms["U"][$userId])
				|| (
					!empty($arPerms["U"][$authorId])
					&& in_array("US".$authorId, $arPerms["U"][$authorId])
				)
				|| $perms == BLOG_PERMS_READ
			)
			{
				if (CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_USER, $authorId, "blog", "write_comment"))
				{
					$perms = BLOG_PERMS_WRITE;
				}
				elseif (CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_USER, $authorId, "blog", "premoderate_comment"))
				{
					$perms = BLOG_PERMS_PREMODERATE;
				}
				elseif (CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_USER, $authorId, "blog", "view_comment"))
				{
					$perms = BLOG_PERMS_READ;
				}
			}

			if (
				$perms <= BLOG_PERMS_FULL
				&& !empty($arGroupsId)
			)
			{
				$operation = Array("full_comment", "moderate_comment", "write_comment", "premoderate_comment");
				if ($perms < BLOG_PERMS_READ)
				{
					$operation[] = "view_comment";
				}

				foreach ($operation as $v)
				{
					if ($perms <= BLOG_PERMS_READ)
					{
						$f = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $arGroupsId, "blog", $v);
						if (
							is_array($f)
							&& !empty($f)
						)
						{
							foreach($f as $gid => $val)
							{
								if (
									(
										!empty($arEntities["SG"][$gid])
										&& in_array($val, $arEntities["SG"][$gid])
									)
									|| $val == SONET_ROLES_ALL
									|| (
										$userId > 0
										&& $val == SONET_ROLES_AUTHORIZED
									)
								)
								{
									switch($v)
									{
										case "full_comment":
											$perms = BLOG_PERMS_FULL;
											$permsBySG = true;
											break;
										case "moderate_comment":
											$perms = BLOG_PERMS_MODERATE;
											$permsBySG = true;
											break;
										case "write_comment":
											$perms = BLOG_PERMS_WRITE;
											$permsBySG = true;
											break;
										case "premoderate_comment":
											$perms = BLOG_PERMS_PREMODERATE;
											$permsBySG = true;
											break;
										case "view_comment":
											$perms = BLOG_PERMS_READ;
											$permsBySG = true;
											break;
									}
								}
							}
						}
					}
				}
			}
		}

		return $perms;
	}

	public static function GetMentionedUserID($arFields)
	{
		$arMentionedUserID = array();

		if (isset($arFields["POST_TEXT"]))
		{
			preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/isu", $arFields["POST_TEXT"], $arMention);
			if (!empty($arMention))
			{
				$arMentionedUserID = array_merge($arMentionedUserID, $arMention[1]);
			}
		}

		return $arMentionedUserID;
	}

	/**
	 * Use component main.post.list to work with LiveFeed
	 * @param int $commentId Comment ID which needs to send.
	 * @param array $arParams Array of settings (DATE_TIME_FORMAT, SHOW_RATING, PATH_TO_USER, AVATAR_SIZE, NAME_TEMPLATE, SHOW_LOGIN)
	 * @return string
	 */
	public static function addLiveComment($commentId = 0, $arParams = array())
	{
		global $USER_FIELD_MANAGER;

		$res = "";
		if(
			$commentId > 0
			&& ($comment = CBlogComment::GetByID($commentId))
			&& ($arPost = CBlogPost::GetByID($comment["POST_ID"]))
		)
		{
			global $DB, $APPLICATION;

			$arParams["DATE_TIME_FORMAT"] = (isset($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));
			$arParams["SHOW_RATING"] = (($arParams["SHOW_RATING"] ?? null) == "N" ? "N" : "Y");

			$arParams["PATH_TO_USER"] = (isset($arParams["PATH_TO_USER"]) ? $arParams["PATH_TO_USER"] : '');
			$arParams["PATH_TO_POST"] = (isset($arParams["PATH_TO_POST"]) ? $arParams["PATH_TO_POST"] : '');

			$arParams["AVATAR_SIZE_COMMENT"] = ($arParams["AVATAR_SIZE_COMMENT"] ?? 0) > 0 ? $arParams["AVATAR_SIZE_COMMENT"] : 0;
			if ($arParams["AVATAR_SIZE_COMMENT"] === 0)
			{
				$arParams["AVATAR_SIZE_COMMENT"] = (($arParams["AVATAR_SIZE"] ?? null) > 0 ? $arParams["AVATAR_SIZE"] : 100);
			}

			$arParams["NAME_TEMPLATE"] = isset($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat();
			$arParams["SHOW_LOGIN"] = (($arParams["SHOW_LOGIN"] ?? null) == "N" ? "N" : "Y");

			$comment["DateFormated"] = FormatDateFromDB($comment["DATE_CREATE"], $arParams["DATE_TIME_FORMAT"], true);
			$timestamp = MakeTimeStamp($comment["DATE_CREATE"]);

			if (
				strcasecmp(LANGUAGE_ID, 'EN') !== 0
				&& strcasecmp(LANGUAGE_ID, 'DE') !== 0
			)
			{
				$comment["DateFormated"] = mb_strtolower($comment["DateFormated"]);
			}

			$comment["UF"] = $USER_FIELD_MANAGER->GetUserFields("BLOG_COMMENT", $commentId, LANGUAGE_ID);

			$arAuthor = CBlogUser::GetUserInfo(
				$comment["AUTHOR_ID"],
				$arParams["PATH_TO_USER"],
				array(
					"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"]
				)
			);

			if (intval($arAuthor["PERSONAL_PHOTO"]) > 0)
			{
				$image_resize = CFile::ResizeImageGet(
					$arAuthor["PERSONAL_PHOTO"],
					array(
						"width" => $arParams["AVATAR_SIZE_COMMENT"],
						"height" => $arParams["AVATAR_SIZE_COMMENT"]
					),
					BX_RESIZE_IMAGE_EXACT
				);
				$arAuthor["PERSONAL_PHOTO_RESIZED"] = array("src" => $image_resize["src"]);
			}

			$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "SHORT_ANCHOR" => "Y");
			$arParserParams = Array(
				"imageWidth" => 800,
				"imageHeight" => 800,
				"pathToUser" => $arParams["PATH_TO_USER"]
			);
			if (!empty($arParams["LOG_ID"]))
			{
				$arParserParams["pathToUserEntityType"] = 'LOG_ENTRY';
				$arParserParams["pathToUserEntityId"] = intval($arParams["LOG_ID"]);
			}

			if ($commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider($comment))
			{
				$comment["AuxType"] = mb_strtolower($commentAuxProvider->getType());
				$comment["TextFormated"] = $commentAuxProvider->getText();

				$commentAuxProvider->setOptions(array(
					'mobile' => true
				));
				$comment["TextFormatedMobile"] = $commentAuxProvider->getText();
			}
			else
			{
				$p = new blogTextParser(false, '');
				$ufCode = "UF_BLOG_COMMENT_FILE";
				if (is_array($comment["UF"][$ufCode]))
				{
					$p->arUserfields = array($ufCode => array_merge($comment["UF"][$ufCode], array("TAG" => "DOCUMENT ID")));
				}

				$comment["TextFormated"] = $p->convert($comment["POST_TEXT"], false, array(), $arAllow, $arParserParams);
				$p->bMobile = true;
				$comment["TextFormatedMobile"] = $p->convert($comment["POST_TEXT"], false, array(), $arAllow, $arParserParams);
			}


			$comment["TextFormatedJS"] = CUtil::JSEscape(htmlspecialcharsBack($comment["POST_TEXT"]));
			$comment["TITLE"] = CUtil::JSEscape(htmlspecialcharsBack($comment["TITLE"]));

			$authorType = false;
			if (
				isset($arAuthor["EXTERNAL_AUTH_ID"])
				&& $arAuthor["EXTERNAL_AUTH_ID"] == 'email'
			)
			{
				$authorType = 'EMAIL';
			}
			elseif (
				IsModuleInstalled('extranet')
				&& (
					empty($arAuthor["UF_DEPARTMENT"])
					|| (
						is_array($arAuthor["UF_DEPARTMENT"])
						&& empty($arAuthor["UF_DEPARTMENT"][0])
					)
				)
			)
			{
				$authorType = 'EXTRANET';
			}

			$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));
			$res = $APPLICATION->IncludeComponent(
				"bitrix:main.post.list",
				"",
				array(
					"TEMPLATE_ID" => 'BLOG_COMMENT_BG_',
					"RATING_TYPE_ID" => ($arParams["SHOW_RATING"] == "Y" ? "BLOG_COMMENT" : ""),
					"ENTITY_XML_ID" => "BLOG_".$arPost["ID"],
					"RECORDS" => array(
						$commentId => array(
							"ID" => $comment["ID"],
							"RATING_VOTE_ID" => 'BLOG_COMMENT_'.$comment["ID"].'-'.(time()+rand(0, 1000)),
							"NEW" => (($arParams["FOLLOW"] ?? null) != "N" && ($comment["NEW"] ?? null) == "Y" ? "Y" : "N"),
							"AUX" => (!empty($arParams["AUX"]) ? $arParams["AUX"] : ''),
							"AUX_LIVE_PARAMS" => (!empty($arParams["AUX_LIVE_PARAMS"]) ? $arParams["AUX_LIVE_PARAMS"] : ''),
							"APPROVED" => ($comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH ? "Y" : "N"),
							"POST_TIMESTAMP" => $timestamp,
							"POST_TIME" => $comment["DATE_CREATE_TIME"] ?? null,
							"POST_DATE" => $comment["DateFormated"],
							"AUTHOR" => array(
								"ID" => $arAuthor["ID"],
								"NAME" => $arAuthor["~NAME"],
								"LAST_NAME" => $arAuthor["~LAST_NAME"],
								"SECOND_NAME" => $arAuthor["~SECOND_NAME"],
								"LOGIN" => $arAuthor["~LOGIN"],
								"PERSONAL_GENDER" => $arAuthor["~PERSONAL_GENDER"],
								"AVATAR" => $arAuthor["PERSONAL_PHOTO_resized"]["src"] ?? null,
								"EXTERNAL_AUTH_ID" => (isset($arAuthor["EXTERNAL_AUTH_ID"]) ? $arAuthor["EXTERNAL_AUTH_ID"] : ''),
								"TYPE" => $authorType
							),
							"FILES" => false,
							"UF" => $comment["UF"],
							"~POST_MESSAGE_TEXT" => $comment["POST_TEXT"],
							"WEB" => array(
								"POST_TIME" => $comment["DATE_CREATE_TIME"] ?? null,
								"POST_DATE" => $comment["DateFormated"],
								"CLASSNAME" => "",
								"POST_MESSAGE_TEXT" => $comment["TextFormated"],
								"AFTER" => <<<HTML
<script>top.text{$commentId} = text{$commentId} = '{$comment["TextFormatedJS"]}';top.title{$commentId} = title{$commentId} = '{$comment["TITLE"]}';top.arComFiles{$commentId} = [];</script>
HTML
							),
							"MOBILE" => array(
								"POST_TIME" => $comment["DATE_CREATE_TIME"] ?? null,
								"POST_DATE" => $comment["DateFormated"],
								"CLASSNAME" => "",
								"POST_MESSAGE_TEXT" => $comment["TextFormatedMobile"]
							)
						)
					),
					"NAV_STRING" => "",
					"NAV_RESULT" => "",
					"PREORDER" => "N",
					"RIGHTS" => array(
						"MODERATE" => "N",
						"EDIT" => "N",
						"DELETE" => "N"
					),
					"VISIBLE_RECORDS_COUNT" => 1,

					"ERROR_MESSAGE" => "",
					"OK_MESSAGE" => "",
					"RESULT" => $commentId,
					"PUSH&PULL" => array(
						"ACTION" => "REPLY",
						"ID" => $commentId,
						"AUTHOR_ID" => $arAuthor["ID"]
					),
					"MODE" => (
						is_array($arParams) && isset($arParams["MODE"])
							? $arParams["MODE"]
							: "PLAIN"
					),
					"VIEW_URL" => (!empty($arParams["PATH_TO_POST"]) ? $arParams["PATH_TO_POST"] : ""),
					"EDIT_URL" => "",
					"MODERATE_URL" => "",
					"DELETE_URL" => "",
					"AUTHOR_URL" => "",

					"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],

					"DATE_TIME_FORMAT" => "",
					"LAZYLOAD" => "",

					"NOTIFY_TAG" => "",
					"NOTIFY_TEXT" => "",
					"SHOW_MINIMIZED" => "Y",
					"SHOW_POST_FORM" => (!empty($arParams["CAN_USER_COMMENT"]) && $arParams["CAN_USER_COMMENT"] ? "Y" : "N"),

					"IMAGE_SIZE" => "",
					"mfi" => "",
					'CURRENT_USER_ID' => (isset($arParams['CURRENT_USER_ID']) ? (int)$arParams['CURRENT_USER_ID'] : 0),
				),
				array(),
				null
			);
			if (
				$eventHandlerID !== false
				&& intval($eventHandlerID) > 0
			)
			{
				RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
			}
		}
		return $res;
	}

	public static function BuildUFFields($arUF)
	{
		global $APPLICATION;

		$arResult = array(
			"AFTER" => "",
			"AFTER_MOBILE" => ""
		);

		if (
			is_array($arUF)
			&& count($arUF) > 0
		)
		{
			ob_start();

			$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));

			foreach ($arUF as $FIELD_NAME => $arUserField)
			{
				if(!empty($arUserField["VALUE"]))
				{
					$APPLICATION->IncludeComponent(
						"bitrix:system.field.view",
						$arUserField["USER_TYPE"]["USER_TYPE_ID"],
						array(
							"arUserField" => $arUserField,
							"MOBILE" => "Y"
						),
						null,
						array("HIDE_ICONS"=>"Y")
					);
				}
			}
			if (
				$eventHandlerID !== false
				&& intval($eventHandlerID) > 0
			)
			{
				RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
			}

			$arResult["AFTER_MOBILE"] = ob_get_clean();

			ob_start();

			$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));

			foreach ($arUF as $FIELD_NAME => $arUserField)
			{
				if(!empty($arUserField["VALUE"]))
				{
					$APPLICATION->IncludeComponent(
						"bitrix:system.field.view",
						$arUserField["USER_TYPE"]["USER_TYPE_ID"],
						array(
							"arUserField" => $arUserField
						),
						null,
						array("HIDE_ICONS"=>"Y")
					);
				}
			}
			if (
				$eventHandlerID !== false
				&& intval($eventHandlerID) > 0
			)
			{
				RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
			}

			$arResult["AFTER"] .= ob_get_clean();
		}

		return $arResult;
	}
}
