<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/general/blog_comment.php");

class CBlogComment extends CAllBlogComment
{
	/*************** ADD, UPDATE, DELETE *****************/
	public static function Add($arFields, $bSearchIndex = true)
	{
		global $DB, $USER_FIELD_MANAGER;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CBlogComment::CheckFields("ADD", $arFields))
			return false;
		elseif(!$USER_FIELD_MANAGER->CheckFields("BLOG_COMMENT", 0, $arFields, (isset($arFields["AUTHOR_ID"]) && intval($arFields["AUTHOR_ID"]) > 0 ? intval($arFields["AUTHOR_ID"]) : false)))
			return false;

		foreach(GetModuleEvents("blog", "OnBeforeCommentAdd", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, Array(&$arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_blog_comment", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($arInsert[0] <> '')
				$arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if ($arInsert[1] <> '')
				$arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$ID = False;
		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_blog_comment(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql);

			$ID = intval($DB->LastID());
		}

		if ($ID)
		{
			$USER_FIELD_MANAGER->Update("BLOG_COMMENT", $ID, $arFields, (isset($arFields["AUTHOR_ID"]) && intval($arFields["AUTHOR_ID"]) > 0 ? intval($arFields["AUTHOR_ID"]) : false));

			$arComment = CBlogComment::GetByID($ID);

			$arPostFields = array("=NUM_COMMENTS_ALL" => "NUM_COMMENTS_ALL + 1");
			if ($arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
			{
				$arPostFields["=NUM_COMMENTS"] = "NUM_COMMENTS + 1";
			}
			CBlogPost::Update($arComment["POST_ID"], $arPostFields, false);
		}

		$arBlog = CBlog::GetByID($arComment["BLOG_ID"]);
		if ($arBlog["USE_SOCNET"] == "Y")
		{
			$arFields["SC_PERM"] = CBlogComment::GetSocNetCommentPerms($arComment["POST_ID"]);
		}

		foreach(GetModuleEvents("blog", "OnCommentAdd", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, Array($ID, &$arFields));
		}

		if (
			$bSearchIndex
			&& CModule::IncludeModule("search")
		)
		{
			if (CBlogUserGroup::GetGroupPerms(1, $arComment["BLOG_ID"], $arComment["POST_ID"], BLOG_PERMS_POST) >= BLOG_PERMS_READ)
			{
				if (
					$arBlog["SEARCH_INDEX"] == "Y"
					&& $arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
				)
				{
					$arGroup = CBlogGroup::GetByID(isset($arFields["SEARCH_GROUP_ID"]) && intval($arFields["SEARCH_GROUP_ID"]) > 0 ? $arFields["SEARCH_GROUP_ID"] : $arBlog["GROUP_ID"]);

					if (($arFields["PATH"] ?? '') <> '')
					{
						$arFields["PATH"] = str_replace("#comment_id#", $ID, $arFields["PATH"]);
						$arCommentSite = array($arGroup["SITE_ID"] => $arFields["PATH"]);
					}
					else
					{
						$arCommentSite = array(
							$arGroup["SITE_ID"] => CBlogPost::PreparePath(
								$arBlog["URL"],
								$arComment["POST_ID"],
								$arGroup["SITE_ID"],
								false,
								$arBlog["OWNER_ID"],
								$arBlog["SOCNET_GROUP_ID"]
							)
						);
					}

					if (
						$arBlog["USE_SOCNET"] == "Y"
						&& CModule::IncludeModule("extranet")
					)
					{
						$arPostSiteExt = CExtranet::GetSitesByLogDestinations($arFields["SC_PERM"]);
						foreach($arPostSiteExt as $lid)
						{
							if (!array_key_exists($lid, $arCommentSite))
							{
								$arCommentSite[$lid] = str_replace(
									array("#user_id#", "#post_id#"),
									array($arBlog["OWNER_ID"], $arComment["POST_ID"]),
									COption::GetOptionString("socialnetwork", "userblogpost_page", false, $lid)
								);
							}
						}
					}

					$searchContent = blogTextParser::killAllTags($arComment["POST_TEXT"]);
					$searchContent .= "\r\n" . $USER_FIELD_MANAGER->OnSearchIndex("BLOG_COMMENT", $arComment["ID"]);

					$arSearchIndex = array(
						"SITE_ID" => $arCommentSite,
						"LAST_MODIFIED" => $arComment["DATE_CREATE"],
						"PARAM1" => "COMMENT",
						"PARAM2" => $arComment["BLOG_ID"]."|".$arComment["POST_ID"],
						"PERMISSIONS" => array(2),
						"TITLE" => CSearch::KillTags($arComment["TITLE"]),
						"BODY" => CSearch::KillTags($searchContent),
						"INDEX_TITLE" => false,
						"USER_ID" => (intval($arComment["AUTHOR_ID"]) > 0) ? $arComment["AUTHOR_ID"] : false,
						"ENTITY_TYPE_ID" => "BLOG_COMMENT",
						"ENTITY_ID" => $arComment["ID"],
					);

					if($arBlog["USE_SOCNET"] == "Y")
					{
						CBlogSearch::fillSearchPermsWithSonetGroupData($arFields["SC_PERM"], $arComment["AUTHOR_ID"], $arSearchIndex, array("INIT_PERMISSIONS" => "Y"));
					}
					
					if (
						$arBlog["USE_SOCNET"] == "Y"
						|| mb_strpos($arBlog["URL"], "idea_") === 0
					)
					{
						// get mentions
						CBlogSearch::fillSearchParamsWithMentionData(CBlogComment::GetMentionedUserID($arComment), $arSearchIndex);
					}

					if($arComment["TITLE"] == '')
					{
						$arSearchIndex["TITLE"] = mb_substr(CSearch::KillTags($searchContent), 0, 100);
					}

					CSearch::Index("blog", "C".$ID, $arSearchIndex);
				}
			}
		}

		return $ID;
	}

	public static function Update($ID, $arFields, $bSearchIndex = true)
	{
		global $DB, $USER_FIELD_MANAGER;

		$ID = intval($ID);
		
		if (($arFields["PATH"] ?? null) <> '')
		{
			$arFields["PATH"] = str_replace("#comment_id#", $ID, $arFields["PATH"]);
		}

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CBlogComment::CheckFields("UPDATE", $arFields, $ID))
			return false;
		elseif(!$USER_FIELD_MANAGER->CheckFields("BLOG_COMMENT", $ID, $arFields, (isset($arFields["AUTHOR_ID"]) && intval($arFields["AUTHOR_ID"]) > 0 ? intval($arFields["AUTHOR_ID"]) : false)))
			return false;

		foreach(GetModuleEvents("blog", "OnBeforeCommentUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, Array($ID, &$arFields))===false)
				return false;
		}

		$strUpdate = $DB->PrepareUpdate("b_blog_comment", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '')
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if ($strUpdate <> '')
		{
			if (!empty($arFields["PUBLISH_STATUS"]))
			{
				$arComment = CBlogComment::GetByID($ID);
				if($arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
					CBlogPost::Update($arComment["POST_ID"], array("=NUM_COMMENTS" => "NUM_COMMENTS - 1"), false);
				elseif($arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
					CBlogPost::Update($arComment["POST_ID"], array("=NUM_COMMENTS" => "NUM_COMMENTS + 1"), false);
			}
			
			$strSql =
				"UPDATE b_blog_comment SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql);
			unset($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]);

			$USER_FIELD_MANAGER->Update("BLOG_COMMENT", $ID, $arFields, (isset($arFields["AUTHOR_ID"]) && intval($arFields["AUTHOR_ID"]) > 0 ? intval($arFields["AUTHOR_ID"]) : false));

			$arComment = CBlogComment::GetByID($ID);
			$arBlog = CBlog::GetByID($arComment["BLOG_ID"]);
			if($arBlog["USE_SOCNET"] == "Y")
				$arFields["SC_PERM"] = CBlogComment::GetSocNetCommentPerms($arComment["POST_ID"]);

			foreach(GetModuleEvents("blog", "OnCommentUpdate", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, Array($ID, &$arFields));
			
			if ($bSearchIndex && CModule::IncludeModule("search"))
			{
				$newPostPerms = CBlogUserGroup::GetGroupPerms(1, $arComment["BLOG_ID"], $arComment["POST_ID"], BLOG_PERMS_POST);
				
				if ($arBlog["SEARCH_INDEX"] != "Y" || $arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
				{
					CSearch::Index("blog", "C".$ID,
						array(
							"TITLE" => "",
							"BODY" => ""
						)
					);
				}
				else
				{
					$arGroup = CBlogGroup::GetByID(isset($arFields["SEARCH_GROUP_ID"]) && intval($arFields["SEARCH_GROUP_ID"]) > 0 ? $arFields["SEARCH_GROUP_ID"] : $arBlog["GROUP_ID"]);

					if (($arFields["PATH"] ?? '') <> '')
					{
						$arFields["PATH"] = str_replace("#comment_id#", $ID, $arFields["PATH"]);
						$arPostSite = array($arGroup["SITE_ID"] => $arFields["PATH"]);
					}
					elseif(($arComment["PATH"] ?? '') <> '')
					{
						$arComment["PATH"] = str_replace("#comment_id#", $ID, $arComment["PATH"]);
						$arPostSite = array($arGroup["SITE_ID"] => $arComment["PATH"]);
					}
					else
					{
						$arPostSite = array(
							$arGroup["SITE_ID"] => CBlogPost::PreparePath(
									$arBlog["URL"],
									$arComment["POST_ID"],
									$arGroup["SITE_ID"],
									false,
									$arBlog["OWNER_ID"],
									$arBlog["SOCNET_GROUP_ID"]
								)
						);
					}

					$searchContent = blogTextParser::killAllTags($arComment["POST_TEXT"]);
					$searchContent .= "\r\n" . $USER_FIELD_MANAGER->OnSearchIndex("BLOG_COMMENT", $arComment["ID"]);

					$arSearchIndex = array(
						"SITE_ID" => $arPostSite,
						"LAST_MODIFIED" => $arComment["DATE_CREATE"],
						"PARAM1" => "COMMENT",
						"PARAM2" => $arComment["BLOG_ID"]."|".$arComment["POST_ID"],
						"PERMISSIONS" => array(2),
						"TITLE" => CSearch::KillTags($arComment["TITLE"]),
						"BODY" => CSearch::KillTags($searchContent),
						"USER_ID" => (intval($arComment["AUTHOR_ID"]) > 0) ? $arComment["AUTHOR_ID"] : false,
						"ENTITY_TYPE_ID" => "BLOG_COMMENT",
						"ENTITY_ID" => $arComment["ID"],
					);

					if($arBlog["USE_SOCNET"] == "Y")
					{
						CBlogSearch::fillSearchPermsWithSonetGroupData($arFields["SC_PERM"], $arComment["AUTHOR_ID"], $arSearchIndex, array("INIT_PERMISSIONS" => "Y"));
					}

					if (
						$arBlog["USE_SOCNET"] == "Y"
						|| mb_strpos($arBlog["URL"], "idea_") === 0
					)
					{
						// get mentions
						CBlogSearch::fillSearchParamsWithMentionData(CBlogComment::GetMentionedUserID($arComment), $arSearchIndex);
					}

					if($arComment["TITLE"] == '')
					{
						//$arPost = CBlogPost::GetByID($arComment["POST_ID"]);
						$arSearchIndex["TITLE"] = mb_substr(CSearch::KillTags($searchContent), 0, 100);
					}

					CSearch::Index("blog", "C".$ID, $arSearchIndex, True);
				}
			}

			return $ID;
		}

		return False;
	}

	//*************** SELECT *********************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB, $USER_FIELD_MANAGER, $APPLICATION, $USER;

		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("BLOG_COMMENT", "C.ID");
		$obUserFieldsSql->SetSelect($arSelectFields);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT");
		if(in_array("*", $arSelectFields))
			$arSelectFields = array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "SHARE_DEST", "DATE_CREATE", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_EMAIL", "USER", "BLOG_USER_ALIAS", "BLOG_USER_AVATAR", "BLOG_URL", "BLOG_OWNER_ID", "BLOG_SOCNET_GROUP_ID", "BLOG_ACTIVE", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "BLOG_USE_SOCNET", "PERMS", "PUBLISH_STATUS");
		if((array_key_exists("BLOG_GROUP_SITE_ID", $arFilter) || in_array("BLOG_GROUP_SITE_ID", $arSelectFields)) && !in_array("BLOG_URL", $arSelectFields))
			$arSelectFields[] = "BLOG_URL";
		

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "C.ID", "TYPE" => "int"),
				"BLOG_ID" => array("FIELD" => "C.BLOG_ID", "TYPE" => "int"),
				"POST_ID" => array("FIELD" => "C.POST_ID", "TYPE" => "int"),
				"PARENT_ID" => array("FIELD" => "C.PARENT_ID", "TYPE" => "int"),
				"AUTHOR_ID" => array("FIELD" => "C.AUTHOR_ID", "TYPE" => "int"),
				"AUTHOR_NAME" => array("FIELD" => "C.AUTHOR_NAME", "TYPE" => "string"),
				"AUTHOR_EMAIL" => array("FIELD" => "C.AUTHOR_EMAIL", "TYPE" => "string"),
				"AUTHOR_IP" => array("FIELD" => "C.AUTHOR_IP", "TYPE" => "string"),
				"AUTHOR_IP1" => array("FIELD" => "C.AUTHOR_IP1", "TYPE" => "string"),
				"TITLE" => array("FIELD" => "C.TITLE", "TYPE" => "string"),
				"POST_TEXT" => array("FIELD" => "C.POST_TEXT", "TYPE" => "string"),
				"DATE_CREATE" => array("FIELD" => "C.DATE_CREATE", "TYPE" => "datetime"),
				"DATE_CREATE_TS" => array("FIELD" => "UNIX_TIMESTAMP(C.DATE_CREATE)", "TYPE" => "int"),
				"PATH" => array("FIELD" => "C.PATH", "TYPE" => "string"),
				"PUBLISH_STATUS" => array("FIELD" => "C.PUBLISH_STATUS", "TYPE" => "string"),
				"HAS_PROPS" => array("FIELD" => "C.HAS_PROPS", "TYPE" => "string"),
				"SHARE_DEST" => array("FIELD" => "C.SHARE_DEST", "TYPE" => "string"),

				"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER_SECOND_NAME" => array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				
				"BLOG_USER_ALIAS" => array("FIELD" => "BU.ALIAS", "TYPE" => "string", "FROM" => "LEFT JOIN b_blog_user BU ON (C.AUTHOR_ID = BU.USER_ID)"),
				"BLOG_USER_AVATAR" => array("FIELD" => "BU.AVATAR", "TYPE" => "int", "FROM" => "LEFT JOIN b_blog_user BU ON (C.AUTHOR_ID = BU.USER_ID)"),
				
				"BLOG_URL" => array("FIELD" => "B.URL", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				"BLOG_OWNER_ID" => array("FIELD" => "B.OWNER_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				"BLOG_SOCNET_GROUP_ID" => array("FIELD" => "B.SOCNET_GROUP_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				"BLOG_ACTIVE" => array("FIELD" => "B.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				"BLOG_GROUP_ID" => array("FIELD" => "B.GROUP_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				"BLOG_USE_SOCNET" => array("FIELD" => "B.USE_SOCNET", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				"BLOG_NAME" => array("FIELD" => "B.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				
				"BLOG_GROUP_SITE_ID" => array("FIELD" => "BG.SITE_ID", "TYPE" => "string", "FROM" => "
						INNER JOIN b_blog BGS ON (C.BLOG_ID = BGS.ID)
						INNER JOIN b_blog_group BG ON (BGS.GROUP_ID = BG.ID)"),
				"PERMS" => Array(),
				
				"SOCNET_BLOG_READ" => array("FIELD" => "BSR.BLOG_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_blog_socnet BSR ON (C.BLOG_ID = BSR.BLOG_ID)"),
				
				"POST_CODE" => array("FIELD" => "BP.CODE", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post BP ON (C.POST_ID = BP.ID)"),
				"POST_TITLE" => array("FIELD" => "BP.TITLE", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post BP ON (C.POST_ID = BP.ID)"),
				"BLOG_POST_PUBLISH_STATUS" => array("FIELD" => "BP.PUBLISH_STATUS", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post BP ON (C.POST_ID = BP.ID)"),
				"BLOG_POST_MICRO" => array("FIELD" => "BP.MICRO", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post BP ON (C.POST_ID = BP.ID)"),
			);
				
		if(isset($arFilter["GROUP_CHECK_PERMS"]))
		{
			if(is_array($arFilter["GROUP_CHECK_PERMS"]))
			{
				foreach($arFilter["GROUP_CHECK_PERMS"] as $val)
				{
					if(intval($val)>0)
					{
						$arFields["POST_PERM_".$val] = Array(
								"FIELD" => "BUGP".$val.".PERMS", 
								"TYPE" => "string", 
								"FROM" => "LEFT JOIN b_blog_user_group_perms BUGP".$val." 
											ON (C.BLOG_ID = BUGP".$val.".BLOG_ID 
												AND C.POST_ID = BUGP".$val.".POST_ID 
												AND BUGP".$val.".USER_GROUP_ID = ".$val." 
												AND BUGP".$val.".PERMS_TYPE = '".BLOG_PERMS_COMMENT."')"
							);
						$arSelectFields[] = "POST_PERM_".$val;
					}
				}
			}
			else
			{
				if(intval($arFilter["GROUP_CHECK_PERMS"])>0)
				{
					$arFields["POST_PERM_".$arFilter["GROUP_CHECK_PERMS"]] = Array(
							"FIELD" => "BUGP.PERMS", 
							"TYPE" => "string", 
							"FROM" => "LEFT JOIN b_blog_user_group_perms BUGP 
										ON (C.BLOG_ID = BUGP.BLOG_ID 
											AND C.POST_ID = BUGP.POST_ID 
											AND BUGP.USER_GROUP_ID = ".$arFilter["GROUP_CHECK_PERMS"]." 
											AND BUGP.PERMS_TYPE = '".BLOG_PERMS_COMMENT."')"
						);
					$arSelectFields[] = "POST_PERM_".$arFilter["GROUP_CHECK_PERMS"];
				}
			}
			unset($arFilter["GROUP_CHECK_PERMS"]);
		}
		
		// rating variable	
		if ( 
			in_array("RATING_TOTAL_VOTES", $arSelectFields) || 
			in_array("RATING_TOTAL_POSITIVE_VOTES", $arSelectFields) || 
			in_array("RATING_TOTAL_NEGATIVE_VOTES", $arSelectFields) || 
			array_key_exists("RATING_TOTAL_VALUE", $arOrder) || 
			array_key_exists("RATING_TOTAL_VOTES", $arOrder)
		)
		{
			$arFields["RATING_TOTAL_VALUE"] = array("FIELD" => $DB->IsNull('RV.TOTAL_VALUE', '0'), "TYPE" => "double", "FROM" => "LEFT JOIN b_rating_voting RV ON ( RV.ENTITY_TYPE_ID = 'BLOG_COMMENT' AND RV.ENTITY_ID = C.ID )");
			$arFields["RATING_TOTAL_VOTES"] = array("FIELD" => $DB->IsNull('RV.TOTAL_VOTES', '0'), "TYPE" => "int", "FROM" => "LEFT JOIN b_rating_voting RV ON ( RV.ENTITY_TYPE_ID = 'BLOG_COMMENT' AND RV.ENTITY_ID = C.ID )");
			$arFields["RATING_TOTAL_POSITIVE_VOTES"] = array("FIELD" => $DB->IsNull('RV.TOTAL_POSITIVE_VOTES', '0'), "TYPE" => "int", "FROM" => "LEFT JOIN b_rating_voting RV ON ( RV.ENTITY_TYPE_ID = 'BLOG_COMMENT' AND RV.ENTITY_ID = C.ID )");
			$arFields["RATING_TOTAL_NEGATIVE_VOTES"] = array("FIELD" => $DB->IsNull('RV.TOTAL_NEGATIVE_VOTES', '0'), "TYPE" => "int", "FROM" => "LEFT JOIN b_rating_voting RV ON ( RV.ENTITY_TYPE_ID = 'BLOG_COMMENT' AND RV.ENTITY_ID = C.ID )");
		}

		$strSqlUFFilter = '';
		$bNeedDistinct = false;
		$blogModulePermissions = $APPLICATION->GetGroupRight("blog");
		if ($blogModulePermissions < "W")
		{
			$userId = ((is_object($USER) && $USER->IsAuthorized()) ? $USER->GetID() : 0);
			$arUserGroups = CBlogUser::GetUserGroups($userId, 0, "Y", BLOG_BY_USER_ID);
			$strUserGroups = "0";
			foreach($arUserGroups as $v)
				$strUserGroups .= ",".intval($v);

			$arFields["PERMS"] = array("FIELD" => "UGP.PERMS", "TYPE" => "char", "FROM" => "INNER JOIN b_blog_user_group_perms UGP ON (C.POST_ID = UGP.POST_ID AND C.BLOG_ID = UGP.BLOG_ID AND UGP.USER_GROUP_ID IN (".$strUserGroups.") AND UGP.PERMS_TYPE = '".BLOG_PERMS_COMMENT."')");
			$bNeedDistinct = true;
		}		
		else
		{
			$arFields["PERMS"] = array("FIELD" => "'W'", "TYPE" => "string");
		}

		$arSqls = CBlog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, $obUserFieldsSql);
		if(array_key_exists("FOR_USER", $arFilter))
		{
			if(intval($arFilter["FOR_USER"]) > 0) //authorized user
			{
					$arSqls["FROM"] .=
								" INNER JOIN b_blog_socnet_rights SR ON (C.POST_ID = SR.POST_ID) " .
								" LEFT JOIN b_user_access UA ON (UA.ACCESS_CODE = SR.ENTITY AND UA.USER_ID = ".intval($arFilter["FOR_USER"]).") ";
					if($arSqls["WHERE"] <> '')
						$arSqls["WHERE"] .= " AND ";
					$arSqls["WHERE"] .= " (UA.USER_ID is not NULL OR SR.ENTITY = 'AU') ";
			}
			else
			{
				$arSqls["FROM"] .=
							" INNER JOIN b_blog_socnet_rights SR ON (C.POST_ID = SR.POST_ID) ".
							" INNER JOIN b_user_access UA ON (UA.ACCESS_CODE = SR.ENTITY AND UA.USER_ID = 0)";
			}
			$bNeedDistinct = true;
		}

		if($bNeedDistinct)
			$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);
		else
			$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		$r = $obUserFieldsSql->GetFilter();
		if($r <> '')
			$strSqlUFFilter = " (".$r.") ";

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
					$obUserFieldsSql->GetSelect()." ".
				"FROM b_blog_comment C ".
				"	".$arSqls["FROM"]." ".
					$obUserFieldsSql->GetJoin("C.ID")." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." "; 
			if($arSqls["WHERE"] <> '' && $strSqlUFFilter <> '')
				$strSql .= " AND ".$strSqlUFFilter." ";
			elseif($arSqls["WHERE"] == '' && $strSqlUFFilter <> '')
				$strSql .= " WHERE ".$strSqlUFFilter." ";

			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}
		
		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
				$obUserFieldsSql->GetSelect()." ".
			"FROM b_blog_comment C ".
			"	".$arSqls["FROM"]." ".
				$obUserFieldsSql->GetJoin("C.ID")." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." "; 
		if($arSqls["WHERE"] <> '' && $strSqlUFFilter <> '')
			$strSql .= " AND ".$strSqlUFFilter." ";
		elseif($arSqls["WHERE"] == '' && $strSqlUFFilter <> '')
			$strSql .= " WHERE ".$strSqlUFFilter." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT(".($bNeedDistinct? "DISTINCT ": "")."C.ID) as CNT ".
					$obUserFieldsSql->GetSelect()." ".
				"FROM b_blog_comment C ".
				"	".$arSqls["FROM"]." ".
				$obUserFieldsSql->GetJoin("C.ID")." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." "; 
			if($arSqls["WHERE"] <> '' && $strSqlUFFilter <> '')
				$strSql_tmp .= " AND ".$strSqlUFFilter." ";
			elseif($arSqls["WHERE"] == '' && $strSqlUFFilter <> '')
				$strSql_tmp .= " WHERE ".$strSqlUFFilter." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";
			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("BLOG_POST"));
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("BLOG_POST"));
		}
		return $dbRes;
	}
}
