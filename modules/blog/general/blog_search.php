<?php

use Bitrix\Main\Loader;

class CBlogSearch 
{
	public static function fillSearchPermsWithSonetGroupData($sonetPerms, $authorId, &$arSearchIndex = array(), $arParams = array()): void
	{
		if (is_array($sonetPerms))
		{
			if (
				is_array($arParams)
				&& isset($arParams['INIT_PERMISSIONS'])
				&& $arParams['INIT_PERMISSIONS'] === 'Y'
			)
			{
				$arSearchIndex['PERMISSIONS'] = $sonetPerms;
			}

			if (!in_array('U'.$authorId, $arSearchIndex['PERMISSIONS'], true))
			{
				$arSearchIndex['PERMISSIONS'][] = 'U' . $authorId;
			}

			$sgId = [];

			foreach ($sonetPerms as $perm)
			{
				if (mb_strpos($perm, 'SG') === 0)
				{
					$sgIdTmp = (int)str_replace('SG', '', mb_substr($perm, 0, mb_strpos($perm, '_')));
					if (
						$sgIdTmp > 0
						&& !in_array($sgIdTmp, $sgId, true)
					)
					{
						$sgId[] = $sgIdTmp;
					}
				}
				elseif(preg_match('/^OSG(\d+)_([' . SONET_ROLES_AUTHORIZED . SONET_ROLES_ALL . '])$/', $perm, $matches))
				{
					if (
						!Loader::includeModule('socialnetwork')
						|| !\Bitrix\Socialnetwork\Helper\Workgroup::checkAnyOpened([ (int)$matches[1] ])
					)
					{
						continue;
					}

					if (
						$matches[2] === SONET_ROLES_ALL
						&& !in_array('G2', $arSearchIndex['PERMISSIONS'], true)
					)
					{
						$arSearchIndex['PERMISSIONS'][] = 'G2';
					}
					if (
						$matches[2] === SONET_ROLES_AUTHORIZED
						&& !in_array('AU', $arSearchIndex['PERMISSIONS'], true)
					)
					{
						$arSearchIndex['PERMISSIONS'][] = 'AU';
					}
				}
			}

			if (!empty($sgId))
			{
				$arSearchIndex['PARAMS'] = [
					'socnet_group' => $sgId,
					'entity' => 'socnet_group',
				];
			}
		}
	}

	public static function fillSearchParamsWithMentionData($arMentionedUserID = array(), &$arSearchIndex = array())
	{
		if (!empty($arMentionedUserID))
		{
			if (!isset($arSearchIndex["PARAMS"]))
			{
				$arSearchIndex["PARAMS"] = array();
			}
			$arSearchIndex["PARAMS"]["mentioned_user_id"] = $arMentionedUserID;
		}
	}

	public static function OnSearchReindex($NS=Array(), $oCallback=NULL, $callback_method="")
	{
		global $DB, $USER_FIELD_MANAGER;
		static $blogPostEventIdList = null;

		$arResult = array();
		//CBlogSearch::Trace('OnSearchReindex', 'NS', $NS);
		if($NS["MODULE"]=="blog" && $NS["ID"] <> '')
		{
			$category = mb_substr($NS["ID"], 0, 1);
			$id = intval(mb_substr($NS["ID"], 1));
		}
		else
		{
			$category = 'B';//start with blogs
			$id = 0;//very first id
		}

		//CBlogSearch::Trace('OnSearchReindex', 'category+id', array("CATEGORY"=>$category,"ID"=>$id));
		
		//Reindex blogs
		if($category == 'B')
		{
			$strSql = "
				SELECT
					b.ID
					,bg.SITE_ID
					,b.REAL_URL
					,b.URL
					,".$DB->DateToCharFunction("b.DATE_UPDATE")." as DATE_UPDATE
					,b.NAME
					,b.DESCRIPTION
					,b.OWNER_ID
					,b.SOCNET_GROUP_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
				FROM
					b_blog b
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID)
				WHERE
					b.ACTIVE = 'Y'
					AND b.SEARCH_INDEX = 'Y'
					".($NS["SITE_ID"]!=""?"AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND b.ID > ".$id."
				ORDER BY
					b.ID
			";
			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch())
			{
				if($ar["USE_SOCNET"] == "Y")
				{
					$Result = Array(
							"ID" =>"B".$ar["ID"],
							"BODY" => "",
							"TITLE" => ""
						);

				}
				else
				{
					//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
					$arSite = array(
						$ar["SITE_ID"] => CBlog::PreparePath($ar["URL"], $ar["SITE_ID"], false, $ar["OWNER_ID"], $ar["SOCNET_GROUP_ID"]),
					);
					//CBlogSearch::Trace('OnSearchReindex', 'arSite', $arSite);
					$Result = Array(
						"ID"		=>"B".$ar["ID"],
						"LAST_MODIFIED"	=>$ar["DATE_UPDATE"],
						"TITLE"		=>$ar["NAME"],
						"BODY"		=>blogTextParser::killAllTags($ar["DESCRIPTION"]),
						"SITE_ID"	=>$arSite,
						"PARAM1"	=>"BLOG",
						"PARAM2"	=>$ar["OWNER_ID"],
						"PERMISSIONS"	=>array(2),
						);
					//CBlogSearch::Trace('OnSearchReindex', 'Result', $Result);
				}
				if($oCallback)
				{
					$res = call_user_func(array($oCallback, $callback_method), $Result);
					if(!$res)
						return $Result["ID"];
				}
				else
				{
					$arResult[] = $Result;
				}
			}
			//all blogs indexed so let's start index posts
			$category='P';
			$id=0;
		}
		if($category == 'P')
		{
			$arUser2Blog = Array();
			if(COption::GetOptionString("blog", "socNetNewPerms", "N") == "N")
			{
				$dbB = CBlog::GetList(array(), Array("USE_SOCNET" => "Y", "!OWNER_ID" => false), false, false, Array("ID", "OWNER_ID", "USE_SOCNET", "GROUP_ID"));
				while($arB = $dbB->Fetch())
				{
					$arUser2Blog[$arB["OWNER_ID"]][$arB["GROUP_ID"]] = $arB["ID"];
				}
			}

			$bSonet = false;
			if(IsModuleInstalled("socialnetwork"))
			{
				$bSonet = true;
				if (
					$blogPostEventIdList === null
					&& Loader::includeModule("socialnetwork")
				)
				{
					$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
					$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();
				}
			}

			$parserBlog = new blogTextParser(false, "/bitrix/images/blog/smile/");

			$strSql = "
				SELECT
					bp.ID
					,bg.SITE_ID
					,b.REAL_URL
					,b.URL
					,".$DB->DateToCharFunction("bp.DATE_PUBLISH")." as DATE_PUBLISH
					,bp.TITLE
					,bp.DETAIL_TEXT
					,bp.BLOG_ID
					,b.OWNER_ID
					,bp.CATEGORY_ID
					,b.SOCNET_GROUP_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
					,b.GROUP_ID
					,bp.PATH
					,bp.MICRO
					,bp.PUBLISH_STATUS
					,bp.AUTHOR_ID ".
					($bSonet ? ", BSL.ID as SLID" : "").
				" FROM
					b_blog_post bp
					INNER JOIN b_blog b ON (bp.BLOG_ID = b.ID)
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID) ".
					($bSonet ? "LEFT JOIN b_sonet_log BSL ON (BSL.EVENT_ID in ('".implode("', '", $blogPostEventIdList)."') AND BSL.SOURCE_ID = bp.ID) " : "").
				" WHERE
					bp.DATE_PUBLISH <= ".$DB->CurrentTimeFunction()."
					AND b.ACTIVE = 'Y'
					".($NS["SITE_ID"]!=""?"AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND bp.ID > ".$id."
					
				ORDER BY
					bp.ID
			";

			$limit = 1000;
			$strSql = \Bitrix\Main\Application::getConnection()->getSqlHelper()->getTopSql($strSql, $limit);

			/*		AND bp.PUBLISH_STATUS = '".$DB->ForSQL(BLOG_PUBLISH_STATUS_PUBLISH)."'*/
			//AND b.SEARCH_INDEX = 'Y'
			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch())
			{
				//Check permissions
				$tag = "";
				if($ar["USE_SOCNET"] != "Y")
				{
					$PostPerms = CBlogUserGroup::GetGroupPerms(1, $ar["BLOG_ID"], $ar["ID"], BLOG_PERMS_POST);
					if($PostPerms < BLOG_PERMS_READ)
						continue;
				}
				//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
				if($ar["PATH"] <> '')
				{
					$arSite = array(
						$ar["SITE_ID"] => str_replace("#post_id#", $ar["ID"], $ar["PATH"])
					);
				}
				else
				{
					$arSite = array(
						$ar["SITE_ID"] => CBlogPost::PreparePath($ar["URL"], $ar["ID"], $ar["SITE_ID"], false, $ar["OWNER_ID"], $ar["SOCNET_GROUP_ID"]),
					);
				}

				if($ar["CATEGORY_ID"] <> '')
				{
					$arC = explode(",", $ar["CATEGORY_ID"]);
					$tag = "";
					$arTag = Array();
					foreach($arC as $v)
					{
						$arCategory = CBlogCategory::GetByID($v);
						$arTag[] = $arCategory["NAME"];
					}
					$tag =  implode(",", $arTag);
				}

				$searchContent = blogTextParser::killAllTags($ar["DETAIL_TEXT"]);
				$searchContent .= "\r\n" . $USER_FIELD_MANAGER->OnSearchIndex("BLOG_POST", $ar["ID"]);

				//CBlogSearch::Trace('OnSearchReindex', 'arSite', $arSite);
				$Result = Array(
					"ID"		=> "P".$ar["ID"],
					"LAST_MODIFIED"	=> $ar["DATE_PUBLISH"],
					"TITLE"		=> CSearch::KillTags(blogTextParser::killAllTags($ar["MICRO"] == "Y" ? $ar["TITLE"] : htmlspecialcharsEx($ar["TITLE"]))),
					"BODY"		=> CSearch::KillTags($searchContent),
					"SITE_ID"	=> $arSite,
					"PARAM1"	=> "POST",
					"PARAM2"	=> $ar["BLOG_ID"],
					"PERMISSIONS"	=>array(2),//public
					"TAGS"		=> $tag,
					"USER_ID" => $ar["AUTHOR_ID"],
					"ENTITY_TYPE_ID" => "BLOG_POST",
					"ENTITY_ID" => $ar["ID"],
					);

				if($ar["USE_SOCNET"] == "Y" && CModule::IncludeModule("socialnetwork"))
				{
					$arF = Array();
					if(COption::GetOptionString("blog", "socNetNewPerms", "N") == "N")
					{
						if(intval($ar["SOCNET_GROUP_ID"]) > 0)
						{
							$newBlogId = 0;
							if(intval($arUser2Blog[$ar["AUTHOR_ID"]][$ar["GROUP_ID"]]) > 0)
							{
								$newBlogId = intval($arUser2Blog[$ar["AUTHOR_ID"]][$ar["GROUP_ID"]]);
							}
							else
							{
								$arFields = array(
									"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
									"GROUP_ID" => $ar["GROUP_ID"],
									"ACTIVE" => "Y",
									"ENABLE_COMMENTS" => "Y",
									"ENABLE_IMG_VERIF" => "Y",
									"EMAIL_NOTIFY" => "Y",
									"ENABLE_RSS" => "Y",
									"ALLOW_HTML" => "N",
									"ENABLE_TRACKBACK" => "N",
									"SEARCH_INDEX" => "Y",
									"USE_SOCNET" => "Y",
									"=DATE_CREATE" => $DB->CurrentTimeFunction(),
									"PERMS_POST" => Array( 
										1 => "I",
										2 => "I" ),
									"PERMS_COMMENT" => Array( 
										1 => "P",
										2 => "P" ),
								);
								
								$bRights = false;
								$rsUser = CUser::GetByID($ar["AUTHOR_ID"]);
								$arUser = $rsUser->Fetch();
								if($arUser["NAME"]."".$arUser["LAST_NAME"] == '')
									$arFields["NAME"] = GetMessage("BLG_NAME")." ".$arUser["LOGIN"];
								else
									$arFields["NAME"] = GetMessage("BLG_NAME")." ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
									
								$arFields["URL"] = str_replace(" ", "_", $arUser["LOGIN"])."-blog-".$ar["SITE_ID"];
								$arFields["OWNER_ID"] = $ar["AUTHOR_ID"];
								
								$urlCheck = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arFields["URL"]);
								if ($urlCheck != $arFields["URL"])
								{
									$arFields["URL"] = "u".$arUser["ID"]."-blog-".$ar["SITE_ID"];
								}
								
								if(CBlog::GetByUrl($arFields["URL"]))
								{
									$uind = 0;
									do
									{
										$uind++;
										$arFields["URL"] = $arFields["URL"].$uind;
									}
									while (CBlog::GetByUrl($arFields["URL"]));
								}
								
								$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $ar["AUTHOR_ID"], "blog", "view_post");
								if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
									$bRights = true;

								$blogID = CBlog::Add($arFields);

								if($bRights)
									CBlog::AddSocnetRead($blogID);

								$newBlogId = $blogID;
								$arUser2Blog[$arFields["OWNER_ID"]][$arFields["GROUP_ID"]] = $newBlogId;
							}
							
							if(intval($newBlogId) > 0)
							{
								$arF = Array(
										"BLOG_ID" => $newBlogId,
										"SOCNET_RIGHTS" => Array("SG".$ar["SOCNET_GROUP_ID"]),
									);
							}

							if(intval($ar["SLID"]) > 0)
							{
								CSocNetLog::Delete($ar["SLID"]);
								$ar["SLID"] = 0;
							}

							$arSites = array();
							$rsGroupSite = CSocNetGroup::GetSite($ar["SOCNET_GROUP_ID"]);
							while($arGroupSite = $rsGroupSite->Fetch())
								$arSites[] = $arGroupSite["LID"];
						}
						else
						{
							$newBlogId = 0;
							if($ar["OWNER_ID"] != $ar["AUTHOR_ID"])
							{
								if(intval($arUser2Blog[$ar["AUTHOR_ID"]][$ar["GROUP_ID"]]) > 0)
								{
									$newBlogId = intval($arUser2Blog[$ar["AUTHOR_ID"]][$ar["GROUP_ID"]]);
								}
								else
								{
									$arFields = array(
										"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
										"GROUP_ID" => $ar["GROUP_ID"],
										"ACTIVE" => "Y",
										"ENABLE_COMMENTS" => "Y",
										"ENABLE_IMG_VERIF" => "Y",
										"EMAIL_NOTIFY" => "Y",
										"ENABLE_RSS" => "Y",
										"ALLOW_HTML" => "N",
										"ENABLE_TRACKBACK" => "N",
										"SEARCH_INDEX" => "Y",
										"USE_SOCNET" => "Y",
										"=DATE_CREATE" => $DB->CurrentTimeFunction(),
										"PERMS_POST" => Array( 
											1 => "I",
											2 => "I" ),
										"PERMS_COMMENT" => Array( 
											1 => "P",
											2 => "P" ),
									);
									
									$bRights = false;
									$rsUser = CUser::GetByID($ar["AUTHOR_ID"]);
									$arUser = $rsUser->Fetch();
									if($arUser["NAME"]."".$arUser["LAST_NAME"] == '')
										$arFields["NAME"] = GetMessage("BLG_NAME")." ".$arUser["LOGIN"];
									else
										$arFields["NAME"] = GetMessage("BLG_NAME")." ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
										
									$arFields["URL"] = str_replace(" ", "_", $arUser["LOGIN"])."-blog-".$ar["SITE_ID"];
									$arFields["OWNER_ID"] = $ar["AUTHOR_ID"];
									
									$urlCheck = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arFields["URL"]);
									if ($urlCheck != $arFields["URL"])
									{
										$arFields["URL"] = "u".$arUser["ID"]."-blog-".$ar["SITE_ID"];
									}
									
									if(CBlog::GetByUrl($arFields["URL"]))
									{
										$uind = 0;
										do
										{
											$uind++;
											$arFields["URL"] = $arFields["URL"].$uind;
										}
										while (CBlog::GetByUrl($arFields["URL"]));
									}
									
									$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $ar["AUTHOR_ID"], "blog", "view_post");
									if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
										$bRights = true;

									$blogID = CBlog::Add($arFields);
									if($bRights)
										CBlog::AddSocnetRead($blogID);
									$newBlogId = $blogID;

									$arUser2Blog[$arFields["OWNER_ID"]][$arFields["GROUP_ID"]] = $newBlogId;
								}
								if(intval($ar["SLID"]) > 0)
								{
									CSocNetLog::Delete($ar["SLID"]);
									$ar["SLID"] = 0;
								}
							}
							$arF = Array("SOCNET_RIGHTS" => Array());
							if(intval($newBlogId) > 0)
								$arF["BLOG_ID"] = $newBlogId;

							$arSites = array($ar["SITE_ID"]);
						}

						if(!empty($arF))
						{
							if(intval($arF["BLOG_ID"]) > 0)
							{
								$Result["PARAM2"] = $ar["BLOG_ID"];
								$sqlR = "UPDATE b_blog_post SET BLOG_ID=".intval($arF["BLOG_ID"])." WHERE ID=".intval($ar["ID"]);
								$DB->Query($sqlR);
								$sqlR = "UPDATE b_blog_post_category SET BLOG_ID=".intval($arF["BLOG_ID"])." WHERE POST_ID=".intval($ar["ID"]);
								$DB->Query($sqlR);
								$sqlR = "UPDATE b_blog_image SET BLOG_ID=".intval($arF["BLOG_ID"])." WHERE POST_ID=".intval($ar["ID"]);
								$DB->Query($sqlR);
								$sqlR = "UPDATE b_blog_comment SET BLOG_ID=".intval($arF["BLOG_ID"])." WHERE POST_ID=".intval($ar["ID"]);
								$DB->Query($sqlR);
							}
							$sqlR = "SELECT * FROM b_blog_socnet_rights where POST_ID=".intval($ar["ID"]);
							$dbBB = $DB->Query($sqlR);
							if(!$dbBB->Fetch())
							{
								$arF["SC_PERM"] = CBlogPost::UpdateSocNetPerms($ar["ID"], $arF["SOCNET_RIGHTS"], Array("AUTHOR_ID" => $ar["AUTHOR_ID"]));
							}
							if(intval($arF["BLOG_ID"]) > 0 && $ar["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
							{
								$dbComment = CBlogComment::GetList(Array(), Array("POST_ID" => $ar["ID"]), false, false, Array("ID", "POST_ID", "BLOG_ID", "PATH"));
								if($arComment = $dbComment->Fetch())
								{
									$arParamsComment = Array(
										"BLOG_ID" => $arF["BLOG_ID"],
										"POST_ID" => $ar["ID"],
										"SITE_ID" => $ar["SITE_ID"],
										"PATH" => $arPostSite[$arGroup["SITE_ID"]]."?commentId=#comment_id##com#comment_id#",
										"USE_SOCNET" => "Y",
									);
									CBlogComment::_IndexPostComments($arParamsComment);
								}
							}
						}
					}

					if($ar["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
					{
						if(empty($arF["SC_PERM"]))
						{
							$arF["SC_PERM"] = CBlogPost::GetSocNetPermsCode($ar["ID"]);
						}

						CBlogSearch::fillSearchPermsWithSonetGroupData($arF["SC_PERM"], $ar["AUTHOR_ID"], $Result, array("INIT_PERMISSIONS" => "Y"));

						// get mentions and grats
						CBlogSearch::fillSearchParamsWithMentionData(CBlogPost::GetMentionedUserID($ar), $Result);

						$socnetPerms = false;

						if(intval($ar["SLID"]) <= 0)
						{
							$arAllow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "TABLE" => "N", "LIST" => "N", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "N");
							$text4message = $parserBlog->convert($ar["DETAIL_TEXT"], false, array(), $arAllow, array("isSonetLog"=>true));

							$arSoFields = Array(
								"EVENT_ID" => "blog_post",
								"=LOG_DATE" => $DB->CharToDateFunction($ar["DATE_PUBLISH"], "FULL", SITE_ID),
								"LOG_UPDATE" => $DB->CharToDateFunction($ar["DATE_PUBLISH"], "FULL", SITE_ID),
								"TITLE_TEMPLATE" => "#USER_NAME# add post",
								"TITLE" => $ar["TITLE"],
								"MESSAGE" => $text4message,
								"MODULE_ID" => "blog",
								"CALLBACK_FUNC" => false,
								"SOURCE_ID" => $ar["ID"],
								"ENABLE_COMMENTS" => "Y",
								"ENTITY_TYPE" => SONET_ENTITY_USER,
								"ENTITY_ID" => $ar["AUTHOR_ID"],
								"USER_ID" => $ar["AUTHOR_ID"],
								"URL" => $arSite[$ar["SITE_ID"]],
								"SITE_ID" => $arSites
							);
							$logID = CSocNetLog::Add($arSoFields, false);
							if (intval($logID) > 0)
							{
								$socnetPerms = $arF["SC_PERM"];
								
								if(!in_array("U".$ar["AUTHOR_ID"], $socnetPerms))
									$socnetPerms[] = "U".$ar["AUTHOR_ID"];
								$socnetPerms[] = "SA"; // socnet admin
								CSocNetLog::Update($logID, array("TMP_ID" => $logID, "=LOG_UPDATE" => $arSoFields["LOG_UPDATE"]));
							}
						}
						else
						{
							$socnetPerms = $arF["SC_PERM"];
							
							if(!in_array("U".$ar["AUTHOR_ID"], $socnetPerms))
								$socnetPerms[] = "U".$ar["AUTHOR_ID"];
							$socnetPerms[] = "SA"; // socnet admin
							$logID = $ar["SLID"];
						}

						if (
							intval($logID) > 0
							&& is_array($socnetPerms)
						)
						{
							if (
								in_array("AU", $socnetPerms) 
								|| in_array("G2", $socnetPerms)
							)
							{
								$socnetPermsAdd = array();

								foreach($socnetPerms as $perm_tmp)
								{
									if (preg_match('/^SG(\d+)$/', $perm_tmp, $matches))
									{
										if (
											!in_array("SG".$matches[1]."_".SONET_ROLES_USER, $socnetPerms)
											&& !in_array("SG".$matches[1]."_".SONET_ROLES_MODERATOR, $socnetPerms)
											&& !in_array("SG".$matches[1]."_".SONET_ROLES_OWNER, $socnetPerms)
										)
											$socnetPermsAdd[] = "SG".$matches[1]."_".SONET_ROLES_USER;
										
									}
								}
								if (count($socnetPermsAdd) > 0)
									$socnetPerms = array_merge($socnetPerms, $socnetPermsAdd);
							}

							CSocNetLogRights::DeleteByLogID($logID);
							CSocNetLogRights::Add($logID, $socnetPerms, false, false);
						}
					}
				}

				if($ar["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH && $ar["SEARCH_INDEX"] == "Y")
				{
					//CBlogSearch::Trace('OnSearchReindex', 'Result', $Result);
					if($oCallback)
					{
						$res = call_user_func(array($oCallback, $callback_method), $Result);
						if(!$res)
							return $Result["ID"];
					}
					else
					{
						$arResult[] = $Result;
					}

					$limit--;
					if ($limit <= 0)
					{
						return $Result["ID"];
					}
				}
			}

			//all blog posts indexed so let's start index users
			$category='C';
			$id=0;
			COption::SetOptionString("blog", "socNetNewPerms", "Y");
		}
		if($category == 'C')
		{
			$bSonet = CModule::IncludeModule("socialnetwork");

			$strSql = "
				SELECT
					bc.ID
					,bg.SITE_ID
					,bp.ID as POST_ID
					,b.URL
					,bp.TITLE as POST_TITLE
					,b.OWNER_ID
					,b.SOCNET_GROUP_ID
					,bc.TITLE
					,bc.POST_TEXT
					,bc.POST_ID
					,bc.BLOG_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
					,bc.PATH
					,".$DB->DateToCharFunction("bc.DATE_CREATE")." as DATE_CREATE
					,bc.AUTHOR_ID
				FROM
					b_blog_comment bc
					INNER JOIN b_blog_post bp ON (bp.ID = bc.POST_ID)
					INNER JOIN b_blog b ON (bc.BLOG_ID = b.ID)
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID)
				WHERE
					bc.ID > ".$id." 
					".($NS["SITE_ID"]!=""?" AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND b.SEARCH_INDEX = 'Y'
				ORDER BY
					bc.ID
			";

			$limit = 1000;
			$strSql = \Bitrix\Main\Application::getConnection()->getSqlHelper()->getTopSql($strSql, $limit);

			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch())
			{
				//Check permissions
				$tag = "";
				$PostPerms = CBlogUserGroup::GetGroupPerms(1, $ar["BLOG_ID"], $ar["POST_ID"], BLOG_PERMS_POST);
				if($PostPerms < BLOG_PERMS_READ)
				{
					continue;
				}

				if ($bSonet)
				{
					$handlerManager = new Bitrix\Socialnetwork\CommentAux\HandlerManager();
					if($handlerManager->getHandlerByPostText($ar["POST_TEXT"]))
					{
						continue;
					}
				}

				//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
				if($ar["PATH"] <> '')
				{
					$arSite = array(
						$ar["SITE_ID"] => str_replace("#comment_id#", $ar["ID"], $ar["PATH"])
					);
				}
				else
				{
					$arSite = array(
						$ar["SITE_ID"] => CBlogPost::PreparePath($ar["URL"], $ar["POST_ID"], $ar["SITE_ID"], false, $ar["OWNER_ID"], $ar["SOCNET_GROUP_ID"]),
					);
				}

				$searchContent = blogTextParser::killAllTags($ar["POST_TEXT"]);
				$searchContent .= "\r\n" . $USER_FIELD_MANAGER->OnSearchIndex("BLOG_COMMENT", $ar["ID"]);

				$Result = array(
					"ID" => "C".$ar["ID"],
					"SITE_ID" => $arSite,
					"LAST_MODIFIED" => $ar["DATE_CREATE"],
					"PARAM1" => "COMMENT",
					"PARAM2" => $ar["BLOG_ID"]."|".$ar["POST_ID"],
					"PERMISSIONS" => array(2),
					"TITLE" => CSearch::KillTags($ar["TITLE"]),
					"BODY" => CSearch::KillTags($searchContent),
					"INDEX_TITLE" => false,
					"USER_ID" => (intval($ar["AUTHOR_ID"]) > 0) ? $ar["AUTHOR_ID"] : false,
					"ENTITY_TYPE_ID" => "BLOG_COMMENT",
					"ENTITY_ID" => $ar["ID"],
				);

				if($ar["USE_SOCNET"] == "Y")
				{
					$arSp = CBlogComment::GetSocNetCommentPerms($ar["POST_ID"]);
					CBlogSearch::fillSearchPermsWithSonetGroupData($arSp, $ar["AUTHOR_ID"], $Result, array("INIT_PERMISSIONS" => "Y"));

					// get mentions
					CBlogSearch::fillSearchParamsWithMentionData(CBlogComment::GetMentionedUserID($ar), $Result);
				}

				if($ar["TITLE"] == '')
				{
					$Result["TITLE"] = mb_substr(CSearch::KillTags($searchContent), 0, 100);
				}

				if($oCallback)
				{
					$res = call_user_func(array($oCallback, $callback_method), $Result);
					if(!$res)
						return $Result["ID"];
				}
				else
				{
					$arResult[] = $Result;
				}

				$limit--;
				if ($limit <= 0)
				{
					return $Result["ID"];
				}
			}

			//all blog posts indexed so let's start index users
			$category='U';
			$id=0;
		}

		if($category == 'U')
		{
			$strSql = "
				SELECT
					bu.ID
					,bg.SITE_ID
					,".$DB->DateToCharFunction("bu.LAST_VISIT")." as LAST_VISIT
					,".$DB->DateToCharFunction("u.DATE_REGISTER")." as DATE_REGISTER
					,bu.ALIAS
					,bu.DESCRIPTION
					,bu.INTERESTS
					,u.NAME
					,u.LAST_NAME
					,u.LOGIN
					,bu.USER_ID
					,b.OWNER_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
				FROM
					b_blog_user bu
					INNER JOIN b_user u  ON (u.ID = bu.USER_ID)
					INNER JOIN b_blog b ON (u.ID = b.OWNER_ID)
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID)
				WHERE
					b.ACTIVE = 'Y'
					".($NS["SITE_ID"]!=""?"AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND bu.ID > ".$id."
					AND b.SEARCH_INDEX = 'Y'
				ORDER BY
					bu.ID
			";
			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql);
			while($ar = $rs->Fetch())
			{
				if($ar["USE_SOCNET"] == "Y")
				{
					$Result = Array(
						"ID" =>"U".$ar["ID"],
						"BODY" => "",
						"TITLE" => ""
						);
				}
				else
				{
					//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
					$arSite = array(
						$ar["SITE_ID"] => CBlogUser::PreparePath($ar["USER_ID"], $ar["SITE_ID"]),
					);
					//CBlogSearch::Trace('OnSearchReindex', 'arSite', $arSite);
					$Result = Array(
						"ID"		=>"U".$ar["ID"],
						"LAST_MODIFIED"	=>$ar["LAST_VISIT"],
						"TITLE"		=>CBlogUser::GetUserName($ar["ALIAS"], $ar["NAME"], $ar["LAST_NAME"], $ar["LOGIN"]),
						"BODY"		=>blogTextParser::killAllTags($ar["DESCRIPTION"]." ".$ar["INTERESTS"]),
						"SITE_ID"	=>$arSite,
						"PARAM1"	=>"USER",
						"PARAM2"	=>$ar["ID"],
						"PERMISSIONS"	=>array(2),//public
						//TODO????"URL"		=>$DETAIL_URL,
						);
					if($Result["LAST_MODIFIED"] == '')
						$Result["LAST_MODIFIED"] = $ar["DATE_REGISTER"];
				}

				//CBlogSearch::Trace('OnSearchReindex', 'Result', $Result);
				if($oCallback)
				{
					$res = call_user_func(array($oCallback, $callback_method), $Result);
					if(!$res)
						return $Result["ID"];
				}
				else
				{
					$arResult[] = $Result;
				}
			}
		}

		if($oCallback)
			return false;

		return $arResult;
	}

	public static function Trace($method, $varname, $var)
	{
		//return;
		ob_start();print_r($var);$m=ob_get_contents();ob_end_clean();
		$m=" CBlogSearch::$method:$varname:$m\n";$f=fopen($_SERVER["DOCUMENT_ROOT"]."/debug.log", "a");
		fwrite($f, time().$m);fclose($f);
	}

	public static function SetSoNetFeatureIndexSearch($ID, $arFields)
	{
		if(CModule::IncludeModule("socialnetwork"))
		{
			$feature = CSocNetFeatures::GetByID($ID);
			if($feature["FEATURE"] == "blog")
			{
				if(intval($feature["ENTITY_ID"]) > 0)
				{
					$bRights = false;
					$arFilter = Array("USE_SOCNET" => "Y");

					if($feature["ENTITY_TYPE"] == "U")
					{
						$arFilter["OWNER_ID"] = $feature["ENTITY_ID"];
						$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $feature["ENTITY_ID"], "blog", "view_post");
						if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
						{
							$bRights = true;
						}
					}
					else
					{
						$arFilter["SOCNET_GROUP_ID"] = $feature["ENTITY_ID"];
						$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $feature["ENTITY_ID"], "blog", "view_post");
						if ($featureOperationPerms == SONET_ROLES_ALL)
						{
							$bRights = true;
						}
					}

					$dbBlog = CBlog::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID", "SOCNET_GROUP_ID"));
					if($arBlog = $dbBlog->Fetch())
					{
						if (intval($arBlog["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule("socialnetwork") && method_exists("CSocNetGroup", "GetSite"))
						{
							$arSites = array();
							$rsGroupSite = CSocNetGroup::GetSite($arBlog["SOCNET_GROUP_ID"]);
							while($arGroupSite = $rsGroupSite->Fetch())
							{
								$arSites[] = $arGroupSite["LID"];
							}
						}
						else
						{
							$arSites = array(SITE_ID);
						}

						foreach ($arSites as $site_id_tmp)
						{
							BXClearCache(True, "/".$site_id_tmp."/blog/sonet/");
						}

						if($arFields["ACTIVE"] == "N")
						{
							CBlog::DeleteSocnetRead($arBlog["ID"]);
						}
						else
						{
							if($bRights)
							{
								CBlog::AddSocnetRead($arBlog["ID"]);
							}
							else
							{
								CBlog::DeleteSocnetRead($arBlog["ID"]);
							}
						}
					}
				}
			}
		}
	}

	public static function OnBeforeSocNetFeaturesPermsAdd($arFields)
	{
		self::setSoNetFeaturePermIndexSearchBefore($arFields);
		return true;
	}

	public static function OnBeforeSocNetFeaturesPermsUpdate($ID, $arFields)
	{
		$permFields = \CSocNetFeaturesPerms::getByID($ID);
		self::setSoNetFeaturePermIndexSearchBefore($permFields);
		return true;
	}

	public static function setSoNetFeaturePermIndexSearchBefore($fields)
	{
		if (
			$fields["OPERATION_ID"] == "view_post"
			&& Loader::includeModule("socialnetwork")
		)
		{
			$feature = CSocNetFeatures::GetByID($fields["FEATURE_ID"]);
			if (
				$feature["FEATURE"] == "blog"
				&& $feature["ENTITY_TYPE"] == "G"
			)
			{
				$perm = \CSocNetFeaturesPerms::GetOperationPerm('G', $feature["ENTITY_ID"], 'blog', 'view_post');
				\Bitrix\Blog\Item\PostSocnetRights::set($feature['ENTITY_ID'], $perm);
			}
		}
	}

	public static function SetSoNetFeaturePermIndexSearch($ID, $arFields)
	{
		global $DB;

		$featurePerm = CSocNetFeaturesPerms::GetByID($ID);
		if($featurePerm["OPERATION_ID"] == "view_post")
		{
			if(CModule::IncludeModule("socialnetwork"))
			{
				$feature = CSocNetFeatures::GetByID($featurePerm["FEATURE_ID"]);
				if(
					$feature["FEATURE"] == "blog"
					&& intval($feature["ENTITY_ID"]) > 0
				)
				{
					if(
						$feature["ACTIVE"] == "Y"
						&& (
							($feature["ENTITY_TYPE"] == "U" && $arFields["ROLE"] == "A")
							|| ($feature["ENTITY_TYPE"] == "G" && $arFields["ROLE"] == "N")
						)
					)
					{
						$arFilter = Array("USE_SOCNET" => "Y");
						if($feature["ENTITY_TYPE"] == "U")
						{
							$arFilter["OWNER_ID"] = $feature["ENTITY_ID"];
						}
						else
						{
							$arFilter["SOCNET_GROUP_ID"] = $feature["ENTITY_ID"];
						}
						$dbBlog = CBlog::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID", "SOCNET_GROUP_ID"));
						if($arBlog = $dbBlog->Fetch())
						{
							CBlog::AddSocnetRead($arBlog["ID"]);
						}
					}
					else
					{
						$arFilter = Array("USE_SOCNET" => "Y");
						if($feature["ENTITY_TYPE"] == "U")
						{
							$arFilter["OWNER_ID"] = $feature["ENTITY_ID"];
						}
						else
						{
							$arFilter["SOCNET_GROUP_ID"] = $feature["ENTITY_ID"];
						}
						$dbBlog = CBlog::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID", "SOCNET_GROUP_ID"));
						if($arBlog = $dbBlog->Fetch())
						{
							CBlog::DeleteSocnetRead($arBlog["ID"]);
						}
					}

					if (
						$arBlog
						&& intval($arBlog["SOCNET_GROUP_ID"]) > 0
					)
					{
						$arSites = array();
						$rsGroupSite = CSocNetGroup::GetSite($arBlog["SOCNET_GROUP_ID"]);
						while($arGroupSite = $rsGroupSite->Fetch())
						{
							$arSites[] = $arGroupSite["LID"];
						}
					}
					else
					{
						$arSites = array(SITE_ID);
					}

					foreach ($arSites as $site_id_tmp)
					{
						BXClearCache(True, "/".$site_id_tmp."/blog/sonet/");
					}

					if ($feature["ENTITY_TYPE"] == "G")
					{
						\Bitrix\Blog\PostSocnetRightsTable::recalcGroupPostRights(array(
							'groupId' => $feature["ENTITY_ID"],
							'role' => $arFields["ROLE"]
						));
					}
				}
			}
		}
	}
}
