<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Blog\Integration;

IncludeModuleLangFile(__FILE__);

class CAllBlogPost
{
	public static $arSocNetPostPermsCache = array();
	public static $arUACCache = array();
	public static $arBlogPostCache = array();
	public static $arBlogPostIdCache = array();
	public static $arBlogPCCache = array();
	public static $arBlogUCache = array();

	const UF_NAME = 'UF_BLOG_POST_DOC';

	public static function CanUserEditPost($id, $userId): bool
	{
		$id = (int)$id;
		$userId = (int)$userId;

		$blogModulePermissions = CMain::getGroupRight("blog");
		if ($blogModulePermissions >= "W")
		{
			return true;
		}

		$arPost = CBlogPost::GetByID($id);
		if (!$arPost)
		{
			return false;
		}

		if (CBlog::IsBlogOwner($arPost["BLOG_ID"], $userId))
		{
			return true;
		}

		$arBlogUser = CBlogUser::GetByID($userId, BLOG_BY_USER_ID);
		if ($arBlogUser && $arBlogUser["ALLOW_POST"] != "Y")
		{
			return false;
		}

		if (CBlogPost::GetBlogUserPostPerms($id, $userId) < BLOG_PERMS_WRITE)
		{
			return false;
		}

		if ((int)$arPost['AUTHOR_ID'] === $userId)
		{
			return true;
		}

		return false;
	}

	public static function CanUserDeletePost($id, $userId): bool
	{
		$id = (int)$id;
		$userId = (int)$userId;

		$blogModulePermissions = CMain::getGroupRight("blog");
		if ($blogModulePermissions >= "W")
		{
			return true;
		}

		$arPost = CBlogPost::GetByID($id);
		if (!$arPost)
		{
			return false;
		}

		if (CBlog::IsBlogOwner($arPost["BLOG_ID"], $userId))
		{
			return true;
		}

		$arBlogUser = CBlogUser::GetByID($userId, BLOG_BY_USER_ID);
		if ($arBlogUser && $arBlogUser["ALLOW_POST"] != "Y")
		{
			return false;
		}

		$perms = CBlogPost::GetBlogUserPostPerms($id, $userId);
		if ($perms <= BLOG_PERMS_WRITE && $userId != $arPost["AUTHOR_ID"])
		{
			return false;
		}

		if ($perms > BLOG_PERMS_WRITE)
		{
			return true;
		}

		if ((int)$arPost['AUTHOR_ID'] === $userId)
		{
			return true;
		}

		return false;
	}

	public static function GetBlogUserPostPerms($id, $userId)
	{
		$id = (int)$id;
		$userId = (int)$userId;

		$arAvailPerms = array_keys($GLOBALS["AR_BLOG_PERMS"]);
		$blogModulePermissions = CMain::getGroupRight('blog');
		if ($blogModulePermissions >= "W")
		{
			return $arAvailPerms[count($arAvailPerms) - 1];
		}

		$arPost = CBlogPost::GetByID($id);
		if (!$arPost)
		{
			return $arAvailPerms[0];
		}

		if (CBlog::IsBlogOwner($arPost["BLOG_ID"], $userId))
		{
			return $arAvailPerms[count($arAvailPerms) - 1];
		}

		$arUserGroups = CBlogUser::GetUserGroups($userId, $arPost["BLOG_ID"], "Y", BLOG_BY_USER_ID);
		$permGroups = CBlogUser::GetUserPerms($arUserGroups, $arPost["BLOG_ID"], $id, BLOG_PERMS_POST, BLOG_BY_USER_ID);

//		if for user unset option "WRITE TO BLOG", they can only read (even if all user can write), or smaller rights, if group have smaller
		$arBlogUser = CBlogUser::GetByID($userId, BLOG_BY_USER_ID);
		if ($arBlogUser && $arBlogUser["ALLOW_POST"] != "Y")
		{
			if ($permGroups && in_array(BLOG_PERMS_READ, $arAvailPerms))
			{
				return min(BLOG_PERMS_READ, $permGroups);
			}
			else
			{
				return $arAvailPerms[0];
			}
		}

		if ($permGroups)
		{
			return $permGroups;
		}

		return $arAvailPerms[0];
	}

	public static function GetBlogUserCommentPerms($id, $userId)
	{
		$id = (int)$id;
		$userId = (int)$userId;

		$arAvailPerms = array_keys($GLOBALS["AR_BLOG_PERMS"]);

		$blogModulePermissions = CMain::getGroupRight("blog");
		if ($blogModulePermissions >= "W")
		{
			return $arAvailPerms[count($arAvailPerms) - 1];
		}

		if ($id > 0)
		{
			if (!($arPost = CBlogPost::GetByID($id)))
			{
				return $arAvailPerms[0];
			}
			else
			{
				$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
				if ($arBlog["ENABLE_COMMENTS"] != "Y")
				{
					return $arAvailPerms[0];
				}

				if (CBlog::IsBlogOwner($arPost["BLOG_ID"], $userId))
				{
					return $arAvailPerms[count($arAvailPerms) - 1];
				}

				$arUserGroups = CBlogUser::GetUserGroups($userId, $arPost["BLOG_ID"], "Y", BLOG_BY_USER_ID);
				$permGroups = CBlogUser::GetUserPerms($arUserGroups, $arPost["BLOG_ID"], $id, BLOG_PERMS_COMMENT, BLOG_BY_USER_ID);

//				if for user unset option "WRITE TO BLOG", they can only read (even if all user can write), or smaller rights, if group have smaller
				$arBlogUser = CBlogUser::GetByID($userId, BLOG_BY_USER_ID);
				if ($arBlogUser && $arBlogUser["ALLOW_POST"] != "Y")
				{
					if ($permGroups && in_array(BLOG_PERMS_READ, $arAvailPerms))
					{
						return min(BLOG_PERMS_READ, $permGroups);
					}
					else
					{
						return $arAvailPerms[0];
					}
				}

				if ($permGroups)
				{
					return $permGroups;
				}
			}
		}
		else
		{
			return $arAvailPerms[0];
		}

		return $arAvailPerms[0];
	}

	/*************** ADD, UPDATE, DELETE *****************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $APPLICATION;

		if ((is_set($arFields, "DETAIL_TEXT") || $ACTION=="ADD") && trim(str_replace("\xc2\xa0", ' ', $arFields["DETAIL_TEXT"]), " \t\n\r\0\x0B\xA0") == '')
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GP_EMPTY_DETAIL_TEXT"), "EMPTY_DETAIL_TEXT");
			return false;
		}

		if ((is_set($arFields, "TITLE") || $ACTION=="ADD") && $arFields["TITLE"] == '')
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GP_EMPTY_TITLE"), "EMPTY_TITLE");
			return false;
		}

		if ((is_set($arFields, "BLOG_ID") || $ACTION=="ADD") && intval($arFields["BLOG_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GP_EMPTY_BLOG_ID"), "EMPTY_BLOG_ID");
			return false;
		}
		elseif (is_set($arFields, "BLOG_ID"))
		{
			$arResult = CBlog::GetByID($arFields["BLOG_ID"]);
			if (!$arResult)
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["BLOG_ID"], GetMessage("BLG_GP_ERROR_NO_BLOG")), "ERROR_NO_BLOG");
				return false;
			}
		}

		if ((is_set($arFields, "AUTHOR_ID") || $ACTION=="ADD") && intval($arFields["AUTHOR_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GP_EMPTY_AUTHOR_ID"), "EMPTY_AUTHOR_ID");
			return false;
		}
		elseif (is_set($arFields, "AUTHOR_ID"))
		{
			$dbResult = CUser::GetByID($arFields["AUTHOR_ID"]);
			if (!$dbResult->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("BLG_GP_ERROR_NO_AUTHOR"), "ERROR_NO_AUTHOR");
				return false;
			}
		}

		if (is_set($arFields, "DATE_CREATE") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GP_ERROR_DATE_CREATE"), "ERROR_DATE_CREATE");
			return false;
		}

		if (is_set($arFields, "DATE_PUBLISH") && (!$DB->IsDate($arFields["DATE_PUBLISH"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("BLG_GP_ERROR_DATE_PUBLISH"), "ERROR_DATE_PUBLISH");
			return false;
		}


		$arFields["PREVIEW_TEXT_TYPE"] = mb_strtolower($arFields["PREVIEW_TEXT_TYPE"] ?? '');
		if ((is_set($arFields, "PREVIEW_TEXT_TYPE") || $ACTION=="ADD") && $arFields["PREVIEW_TEXT_TYPE"] != "text" && $arFields["PREVIEW_TEXT_TYPE"] != "html")
			$arFields["PREVIEW_TEXT_TYPE"] = "text";

		if ((is_set($arFields, "DETAIL_TEXT_TYPE") || $ACTION=="ADD") && mb_strtolower($arFields["DETAIL_TEXT_TYPE"]) != "text" && mb_strtolower($arFields["DETAIL_TEXT_TYPE"]) != "html")
			$arFields["DETAIL_TEXT_TYPE"] = "text";
		if (($arFields["DETAIL_TEXT_TYPE"] ?? '') <> '')
		{
			$arFields["DETAIL_TEXT_TYPE"] = mb_strtolower($arFields["DETAIL_TEXT_TYPE"]);
		}

		$arStatus = array_keys($GLOBALS["AR_BLOG_PUBLISH_STATUS"]);
		if ((is_set($arFields, "PUBLISH_STATUS") || $ACTION=="ADD") && !in_array($arFields["PUBLISH_STATUS"], $arStatus))
			$arFields["PUBLISH_STATUS"] = $arStatus[0];

		if (
			(
				is_set($arFields, "ENABLE_TRACKBACK")
				|| $ACTION == "ADD"
			)
			&& ($arFields["ENABLE_TRACKBACK"] ?? '') != "Y"
			&& ($arFields["ENABLE_TRACKBACK"] ?? '') != "N"
		)
		{
			$arFields["ENABLE_TRACKBACK"] = "Y";
		}

		if (
			(
				is_set($arFields, "ENABLE_COMMENTS")
				|| $ACTION == "ADD"
			)
			&& ($arFields["ENABLE_COMMENTS"] ?? '') != "Y"
			&& ($arFields["ENABLE_COMMENTS"] ?? '') != "N"
		)
		{
			$arFields["ENABLE_COMMENTS"] = "Y";
		}

		if (!empty($arFields["ATTACH_IMG"]))
		{
			$res = CFile::CheckImageFile($arFields["ATTACH_IMG"], 0, 0, 0);
			if ($res <> '')
			{
				$APPLICATION->ThrowException(GetMessage("BLG_GP_ERROR_ATTACH_IMG").": ".$res, "ERROR_ATTACH_IMG");
				return false;
			}
		}
		else
			$arFields["ATTACH_IMG"] = false;

		if (is_set($arFields, "NUM_COMMENTS"))
			$arFields["NUM_COMMENTS"] = intval($arFields["NUM_COMMENTS"]);
		if (is_set($arFields, "NUM_COMMENTS_ALL"))
			$arFields["NUM_COMMENTS_ALL"] = intval($arFields["NUM_COMMENTS_ALL"]);
		if (is_set($arFields, "NUM_TRACKBACKS"))
			$arFields["NUM_TRACKBACKS"] = intval($arFields["NUM_TRACKBACKS"]);
		if (is_set($arFields, "FAVORITE_SORT"))
		{
			$arFields["FAVORITE_SORT"] = intval($arFields["FAVORITE_SORT"]);
			if($arFields["FAVORITE_SORT"] <= 0)
				$arFields["FAVORITE_SORT"] = false;
		}

		if (is_set($arFields, "CODE") && $arFields["CODE"] <> '')
		{
			$arFields["CODE"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arFields["CODE"]));
//			preserve collision between numeric code and post ID.
			$arFields["CODE"] = is_numeric($arFields["CODE"]) ? "_".$arFields["CODE"] : $arFields["CODE"];

			if (in_array(mb_strtolower($arFields["CODE"]), $GLOBALS["AR_BLOG_POST_RESERVED_CODES"]))
			{
				$APPLICATION->ThrowException(str_replace("#CODE#", $arFields["CODE"], GetMessage("BLG_GP_RESERVED_CODE")), "CODE_RESERVED");
				return false;
			}

			$arFilter = Array(
				"CODE" => $arFields["CODE"]
			);
			if(intval($ID) > 0)
			{
				$arPost = CBlogPost::GetByID($ID);
				$arFilter["!ID"] = $arPost["ID"];
				$arFilter["BLOG_ID"] = $arPost["BLOG_ID"];
			}
			else
			{
				if(intval($arFields["BLOG_ID"]) > 0)
					$arFilter["BLOG_ID"] = $arFields["BLOG_ID"];
			}

			$dbItem = CBlogPost::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID", "CODE", "BLOG_ID"));
			if($dbItem->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("BLG_GP_CODE_EXIST", Array("#CODE#" => $arFields["CODE"])), "CODE_EXIST");
				return false;
			}
		}

		if (!empty($arFields["TITLE"]))
		{
			$arFields["TITLE"] = \Bitrix\Main\Text\Emoji::encode($arFields["TITLE"]);
		}

		if (!empty($arFields["DETAIL_TEXT"]))
		{
			$arFields["DETAIL_TEXT"] = \Bitrix\Main\Text\Emoji::encode($arFields["DETAIL_TEXT"]);
		}

		return True;
	}

	public static function SetPostPerms($ID, $arPerms = array(), $permsType = BLOG_PERMS_POST)
	{
		global $DB;

		$ID = intval($ID);
		$permsType = (($permsType == BLOG_PERMS_COMMENT) ? BLOG_PERMS_COMMENT : BLOG_PERMS_POST);
		if(!is_array($arPerms))
			$arPerms = array();

		$arPost = CBlogPost::GetByID($ID);
		if ($arPost)
		{
			$arInsertedGroups = array();
			foreach ($arPerms as $key => $value)
			{
				$dbGroupPerms = CBlogUserGroupPerms::GetList(
					array(),
					array(
						"BLOG_ID" => $arPost["BLOG_ID"],
						"USER_GROUP_ID" => $key,
						"PERMS_TYPE" => $permsType,
						"POST_ID" => $arPost["ID"]
					),
					false,
					false,
					array("ID")
				);
				if ($arGroupPerms = $dbGroupPerms->Fetch())
				{
					CBlogUserGroupPerms::Update(
						$arGroupPerms["ID"],
						array(
							"PERMS" => $value,
							"AUTOSET" => "N"
						)
					);
				}
				else
				{
					CBlogUserGroupPerms::Add(
						array(
							"BLOG_ID" => $arPost["BLOG_ID"],
							"USER_GROUP_ID" => $key,
							"PERMS_TYPE" => $permsType,
							"POST_ID" => $arPost["ID"],
							"AUTOSET" => "N",
							"PERMS" => $value
						)
					);
				}

				$arInsertedGroups[] = $key;
			}

			$dbResult = CBlogUserGroupPerms::GetList(
				array(),
				array(
					"BLOG_ID" => $arPost["BLOG_ID"],
					"PERMS_TYPE" => $permsType,
					"POST_ID" => 0,
					"!USER_GROUP_ID" => $arInsertedGroups
				),
				false,
				false,
				array("ID", "USER_GROUP_ID", "PERMS")
			);
			while ($arResult = $dbResult->Fetch())
			{
				$dbGroupPerms = CBlogUserGroupPerms::GetList(
					array(),
					array(
						"BLOG_ID" => $arPost["BLOG_ID"],
						"USER_GROUP_ID" => $arResult["USER_GROUP_ID"],
						"PERMS_TYPE" => $permsType,
						"POST_ID" => $arPost["ID"]
					),
					false,
					false,
					array("ID")
				);
				if ($arGroupPerms = $dbGroupPerms->Fetch())
				{
					CBlogUserGroupPerms::Update(
						$arGroupPerms["ID"],
						array(
							"PERMS" => $arResult["PERMS"],
							"AUTOSET" => "Y"
						)
					);
				}
				else
				{
					CBlogUserGroupPerms::Add(
						array(
							"BLOG_ID" => $arPost["BLOG_ID"],
							"USER_GROUP_ID" => $arResult["USER_GROUP_ID"],
							"PERMS_TYPE" => $permsType,
							"POST_ID" => $arPost["ID"],
							"AUTOSET" => "Y",
							"PERMS" => $arResult["PERMS"]
						)
					);
				}
			}
		}
	}

	public static function Delete($ID)
	{
		global $DB, $CACHE_MANAGER, $USER_FIELD_MANAGER;

		$ID = intval($ID);

		$arPost = CBlogPost::GetByID($ID);
		if ($arPost)
		{
			foreach(GetModuleEvents("blog", "OnBeforePostDelete", true) as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, Array($ID))===false)
					return false;
			}

			$dbResult = CBlogComment::GetList(
				array(),
				array("POST_ID" => $ID),
				false,
				false,
				array("ID")
			);
			while ($arResult = $dbResult->Fetch())
			{
				if (!CBlogComment::Delete($arResult["ID"]))
					return False;
			}

			$dbResult = CBlogUserGroupPerms::GetList(
				array(),
				array("POST_ID" => $ID, "BLOG_ID" => $arPost["BLOG_ID"]),
				false,
				false,
				array("ID")
			);
			while ($arResult = $dbResult->Fetch())
			{
				if (!CBlogUserGroupPerms::Delete($arResult["ID"]))
					return False;
			}

			$dbResult = CBlogTrackback::GetList(
				array(),
				array("POST_ID" => $ID, "BLOG_ID" => $arPost["BLOG_ID"]),
				false,
				false,
				array("ID")
			);
			while ($arResult = $dbResult->Fetch())
			{
				if (!CBlogTrackback::Delete($arResult["ID"]))
					return False;
			}

			$dbResult = CBlogPostCategory::GetList(
				array(),
				array("POST_ID" => $ID, "BLOG_ID" => $arPost["BLOG_ID"]),
				false,
				false,
				array("ID")
			);
			while ($arResult = $dbResult->Fetch())
			{
				if (!CBlogPostCategory::Delete($arResult["ID"]))
					return False;
			}

			$strSql =
				"SELECT F.ID ".
				"FROM b_blog_post P, b_file F ".
				"WHERE P.ID = ".$ID." ".
				"	AND P.ATTACH_IMG = F.ID ";
			$z = $DB->Query($strSql);
			while ($zr = $z->Fetch())
				CFile::Delete($zr["ID"]);

			CBlogPost::DeleteSocNetPostPerms($ID);

			unset(static::$arBlogPostCache[$ID]);

			$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);

			$result = $DB->Query("DELETE FROM b_blog_post WHERE ID = ".$ID, true);

			if (intval($arBlog["LAST_POST_ID"]) == $ID)
				CBlog::SetStat($arPost["BLOG_ID"]);

			if ($result)
			{
				$res = CBlogImage::GetList(array(), array("POST_ID"=>$ID, "IS_COMMENT" => "N"));
				while($aImg = $res->Fetch())
					CBlogImage::Delete($aImg['ID']);
			}
			if ($result)
				$USER_FIELD_MANAGER->Delete("BLOG_POST", $ID);

			foreach(GetModuleEvents("blog", "OnPostDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, Array($ID, &$result));

			if (CModule::IncludeModule("search"))
			{
				CSearch::Index("blog", "P".$ID,
					array(
						"TITLE" => "",
						"BODY" => ""
					)
				);
				//CSearch::DeleteIndex("blog", false, "COMMENT", $arPost["BLOG_ID"]."|".$ID);
			}
			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->ClearByTag("blog_post_".$ID);
			}

			return $result;
		}
		else
			return false;
	}

	//*************** SELECT *********************/
	public static function PreparePath($blogUrl, $postID = 0, $siteID = False, $is404 = True, $userID = 0, $groupID = 0)
	{
		$blogUrl = Trim($blogUrl);
		$postID = intval($postID);
		$groupID = intval($groupID);
		$userID = intval($userID);

		if (!$siteID)
		{
			$siteID = SITE_ID;
		}

		$dbPath = CBlogSitePath::GetList(array(), array("SITE_ID"=>$siteID));
		while ($arPath = $dbPath->Fetch())
		{
			if ($arPath["TYPE"] <> '')
			{
				$arPaths[$arPath["TYPE"]] = $arPath["PATH"];
			}
			else
			{
				$arPaths["OLD"] = $arPath["PATH"];
			}
		}

		if($postID > 0)
		{
			if($groupID > 0)
			{
				if($arPaths["H"] <> '')
				{
					$result = str_replace("#blog#", $blogUrl, $arPaths["H"]);
					$result = str_replace("#post_id#", $postID, $result);
					$result = str_replace("#user_id#", $userID, $result);
					$result = str_replace("#group_id#", $groupID, $result);
				}
				elseif($arPaths["G"] <> '')
				{
					$result = str_replace("#blog#", $blogUrl, $arPaths["G"]);
					$result = str_replace("#user_id#", $userID, $result);
					$result = str_replace("#group_id#", $groupID, $result);
				}
			}
			elseif($arPaths["P"] <> '')
			{
				$result = str_replace("#blog#", $blogUrl, $arPaths["P"]);
				$result = str_replace("#post_id#", $postID, $result);
				$result = str_replace("#user_id#", $userID, $result);
			}
			elseif($arPaths["B"] <> '')
			{
				$result = str_replace("#blog#", $blogUrl, $arPaths["B"]);
				$result = str_replace("#user_id#", $userID, $result);
			}
			else
			{
				if($is404)
					$result = htmlspecialcharsbx($arPaths["OLD"])."/".htmlspecialcharsbx($blogUrl)."/".$postID.".php";
				else
					$result = htmlspecialcharsbx($arPaths["OLD"])."/post.php?blog=".$blogUrl."&post_id=".$postID;
			}
		}
		else
		{
			if($arPaths["B"] <> '')
			{
				$result = str_replace("#blog#", $blogUrl, $arPaths["B"]);
				$result = str_replace("#user_id#", $userID, $result);
			}
			else
			{
				if($is404)
					$result = htmlspecialcharsbx($arPaths["OLD"])."/".htmlspecialcharsbx($blogUrl)."/";
				else
					$result = htmlspecialcharsbx($arPaths["OLD"])."/post.php?blog=".$blogUrl;
			}
		}

		return $result;
	}

	function PreparePath2Post($realUrl, $url, $arParams = array())
	{
		return CBlogPost::PreparePath(
			$url,
			isset($arParams["POST_ID"]) ? $arParams["POST_ID"] : 0,
			isset($arParams["SITE_ID"]) ? $arParams["SITE_ID"] : False
		);
	}

	public static function CounterInc($ID)
	{
		global $DB;
		$ID = intval($ID);
		if(!is_array($_SESSION["BLOG_COUNTER"]))
			$_SESSION["BLOG_COUNTER"] = Array();
		if(in_array($ID, $_SESSION["BLOG_COUNTER"]))
			return;
		$_SESSION["BLOG_COUNTER"][] = $ID;
		$strSql =
			"UPDATE b_blog_post SET ".
			"	VIEWS =  ".$DB->IsNull("VIEWS", 0)." + 1 ".
			"WHERE ID=".$ID;
		$DB->Query($strSql);
	}

	public static function Notify($arPost, $arBlog, $arParams)
	{
		global $DB;
		if(empty($arBlog))
		{
			$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
		}

		$siteId = (!empty($arParams['SITE_ID']) ? $arParams['SITE_ID'] : SITE_ID);

		$arImages = $arOwner = Array();
		$parserBlog = false;
		$text4mail = $serverName = $AuthorName = "";

		if (
			$arParams["bSoNet"]
			|| (
				$arBlog["EMAIL_NOTIFY"] == "Y"
				&& $arParams["user_id"] != $arBlog["OWNER_ID"]
			)
		)
		{
			$BlogUser = CBlogUser::GetByID($arParams["user_id"], BLOG_BY_USER_ID);
			$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);
			$res = CUser::GetByID($arBlog["OWNER_ID"]);
			$arOwner = $res->GetNext();
			$dbUser = CUser::GetByID($arParams["user_id"]);
			$arUser = $dbUser->Fetch();
			$AuthorName = CBlogUser::GetUserNameEx($arUser, $BlogUser, $arParams);
			$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"] ?? false);
			$text4mail = $arPost["DETAIL_TEXT"];
			if($arPost["DETAIL_TEXT_TYPE"] == "html")
			{
				$text4mail = HTMLToTxt($text4mail);
			}

			$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"], "IS_COMMENT" => "N"));
			while ($arImage = $res->Fetch())
			{
				$arImages[$arImage['ID']] = $arImage['FILE_ID'];
			}

			$text4mail = $parserBlog->convert4mail($text4mail, $arImages);
			$serverName = ((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
		}

		if (
			!$arParams["bSoNet"]
			&& $arBlog["EMAIL_NOTIFY"] == "Y"
			&& $arParams["user_id"] != $arBlog["OWNER_ID"]
			&& intval($arBlog["OWNER_ID"]) > 0
		) // Send notification to email
		{
			CEvent::Send(
				"NEW_BLOG_MESSAGE",
				$siteId,
				array(
					"BLOG_ID" => $arBlog["ID"],
					"BLOG_NAME" => htmlspecialcharsBack($arBlog["NAME"]),
					"BLOG_URL" => $arBlog["URL"],
					"MESSAGE_TITLE" => $arPost["TITLE"],
					"MESSAGE_TEXT" => $text4mail,
					"MESSAGE_DATE" => GetTime(MakeTimeStamp($arPost["DATE_PUBLISH"])-CTimeZone::GetOffset(), "FULL"),
					"MESSAGE_PATH" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => $arPost["ID"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])),
					"AUTHOR" => $AuthorName,
					"EMAIL_FROM" => COption::GetOptionString("main","email_from", "nobody@nobody.com"),
					"EMAIL_TO" => $arOwner["EMAIL"]
				)
			);
		}

		if(
			$arParams["bSoNet"]
			&& $arPost["ID"]
			&& CModule::IncludeModule("socialnetwork")
			&& $parserBlog
		)
		{
			if($arPost["DETAIL_TEXT_TYPE"] == "html" && $arParams["allowHTML"] == "Y" && $arBlog["ALLOW_HTML"] == "Y")
			{
				$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
				if($arParams["allowVideo"] != "Y")
				{
					$arAllow["VIDEO"] = "N";
				}
				$text4message = $parserBlog->convert($arPost["DETAIL_TEXT"], false, $arImages, $arAllow);
			}
			else
			{
				$arAllow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "TABLE" => "N", "LIST" => "N", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "N");
				$text4message = $parserBlog->convert($arPost["DETAIL_TEXT"], false, $arImages, $arAllow, array("isSonetLog"=>true));
			}

			$arSoFields = Array(
				"EVENT_ID" => (
					isset($arPost["UF_BLOG_POST_IMPRTNT"])
					&& intval($arPost["UF_BLOG_POST_IMPRTNT"]) > 0
						? Integration\Socialnetwork\Log::EVENT_ID_POST_IMPORTANT
						: Integration\Socialnetwork\Log::EVENT_ID_POST
				),
				"=LOG_DATE" => (
					($arPost["DATE_PUBLISH"] ?? '') <> ''
						? (
							MakeTimeStamp($arPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL", SITE_ID)) > time()+CTimeZone::GetOffset()
								? $DB->CharToDateFunction($arPost["DATE_PUBLISH"], "FULL", SITE_ID)
								: $DB->CurrentTimeFunction()
						)
						:
						$DB->CurrentTimeFunction()
				),
				"TITLE_TEMPLATE" => "#USER_NAME# ".GetMessage("BLG_SONET_TITLE"),
				"TITLE" => $arPost["TITLE"],
				"MESSAGE" => $text4message,
				"TEXT_MESSAGE" => $text4mail,
				"MODULE_ID" => "blog",
				"CALLBACK_FUNC" => false,
				"SOURCE_ID" => $arPost["ID"],
				"ENABLE_COMMENTS" => (array_key_exists("ENABLE_COMMENTS", $arPost) && $arPost["ENABLE_COMMENTS"] == "N" ? "N" : "Y")
			);

			$arSoFields["RATING_TYPE_ID"] = "BLOG_POST";
			$arSoFields["RATING_ENTITY_ID"] = intval($arPost["ID"]);

			if ($arParams["bGroupMode"] ?? false)
			{
				$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_GROUP;
				$arSoFields["ENTITY_ID"] = $arParams["SOCNET_GROUP_ID"];
				$arSoFields["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"], "post_id" => $arPost["ID"]));
			}
			else
			{
				$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_USER;
				$arSoFields["ENTITY_ID"] = $arBlog["OWNER_ID"];
				$arSoFields["URL"] = CComponentEngine::MakePathFromTemplate(
					$arParams["PATH_TO_POST"],
					[
						"blog" => $arBlog["URL"],
						"user_id" => $arBlog["OWNER_ID"],
						"group_id" => $arParams["SOCNET_GROUP_ID"] ?? null,
						"post_id" => $arPost["ID"],
					]
				);
			}

			if (intval($arParams["user_id"]) > 0)
			{
				$arSoFields["USER_ID"] = $arParams["user_id"];
			}

			$post = \Bitrix\Blog\Item\Post::getById($arPost["ID"]);
			$arSoFields["TAG"] = $post->getTags();

			$logID = CSocNetLog::Add($arSoFields, false);

			if (intval($logID) > 0)
			{
				$socnetPerms = \Bitrix\Socialnetwork\ComponentHelper::getBlogPostSocNetPerms(array(
					'postId' => $arPost["ID"],
					'authorId' => $arPost["AUTHOR_ID"]
				));

				$postFields = $post->getFields();
				$inlineAttachedObjectsIdList = array();

				if (preg_match_all('/\[DISK\s+FILE\s+ID\s*=\s*([n]*\d+)\s*\]/isu', $postFields['DETAIL_TEXT'], $matches))
				{
					if (!empty($matches[1]))
					{
						$inlineFileList = $matches[1];

						foreach($inlineFileList as $key => $value)
						{
							if (
								preg_match('/^n(\d+)/isu', $value, $matches)
								&& !empty($matches[1])
								&& intval($matches[1]) > 0
							)
							{
								$res = \Bitrix\Disk\AttachedObject::getList(array(
									'filter' => array(
										'=ENTITY_TYPE' => \Bitrix\Disk\Uf\BlogPostConnector::className(),
										'ENTITY_ID' => $postFields['ID'],
										'OBJECT_ID' => intval($matches[1])
									),
									'select' => array('ID')
								));
								foreach ($res as $attachedObjectFields)
								{
									$inlineAttachedObjectsIdList[] = $attachedObjectFields['ID'];
								}
							}
						}
					}
				}

				CSocNetLog::Update($logID, array("TMP_ID" => $logID));

				$hasVideoTransforming = (
					!empty($inlineAttachedObjectsIdList)
					&& Integration\Disk\Transformation::getStatus(array(
						'attachedIdList' => $inlineAttachedObjectsIdList
					))
				);

				if ($hasVideoTransforming)
				{
					$socnetPerms = array("SA", "U".$arPost["AUTHOR_ID"]);
				}

				CSocNetLogRights::deleteByLogID($logID);
				CSocNetLogRights::add($logID, $socnetPerms);

				$updateFields = array(
					"TRANSFORM" => ($hasVideoTransforming ? 'Y' : 'N')
				);

				if (Loader::includeModule("extranet"))
				{
					$updateFields["SITE_ID"] = CExtranet::getSitesByLogDestinations($socnetPerms, $arPost["AUTHOR_ID"], $siteId);
				}

				CSocNetLog::update($logID, $updateFields);

				if (Loader::includeModule('crm'))
				{
					CCrmLiveFeedComponent::processCrmBlogPostRights($logID, $arSoFields, $arPost, 'new');
				}

				Integration\Socialnetwork\CounterPost::increment(array(
					'socnetPerms' => $socnetPerms,
					'logId' => $logID,
					'logEventId' => $arSoFields["EVENT_ID"],
					'sendToAuthor' => (
						!empty($arParams["SEND_COUNTER_TO_AUTHOR"])
						&& $arParams["SEND_COUNTER_TO_AUTHOR"] == "Y"
					)
				));

				if ($hasVideoTransforming)
				{
					CUserOptions::setOption("socialnetwork", "~log_videotransform_popup_show", "Y");
					CUserOptions::setOption("socialnetwork", "~log_videotransform_post_url", $arSoFields["URL"]);
					CUserOptions::setOption("socialnetwork", "~log_videotransform_post_id", $arPost["ID"]);
				}

				return $logID;
			}
		}

		return false;
	}

	public static function UpdateLog($postID, $arPost, $arBlog, $arParams)
	{
		static $blogPostEventIdList = null;

		if (!CModule::IncludeModule('socialnetwork'))
		{
			return;
		}

		$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);

		preg_match("#^(.*?)<cut[\s]*(/>|>).*?$#is", $arPost["DETAIL_TEXT"], $arMatches);
		if (count($arMatches) <= 0)
		{
			preg_match("#^(.*?)\[cut[\s]*(/\]|\]).*?$#is", $arPost["DETAIL_TEXT"], $arMatches);
		}

		$cut_suffix = (count($arMatches) > 0 ? "#CUT#" : "");

		$arImages = Array();
		$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$postID, "BLOG_ID"=>$arBlog["ID"], "IS_COMMENT" => "N"));
		while ($arImage = $res->Fetch())
		{
			$arImages[$arImage['ID']] = $arImage['FILE_ID'];
		}

		if (
			($arPost["DETAIL_TEXT_TYPE"] ?? null) === "html"
			&& $arParams["allowHTML"] === "Y"
			&& $arBlog["ALLOW_HTML"] === "Y"
		)
		{
			$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
			if($arParams["allowVideo"] != "Y")
			{
				$arAllow["VIDEO"] = "N";
			}
			$text4message = $parserBlog->convert($arPost["DETAIL_TEXT"], true, $arImages, $arAllow);
		}
		else
		{
			$arAllow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "TABLE" => "N", "LIST" => "N", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "N");
			$text4message = $parserBlog->convert($arPost["DETAIL_TEXT"], true, $arImages, $arAllow, array("isSonetLog"=>true));
		}

		$text4message .= $cut_suffix;

		$eventId = Integration\Socialnetwork\Log::EVENT_ID_POST;
		if (
			isset($arPost['UF_BLOG_POST_IMPRTNT'])
			&& (int)$arPost['UF_BLOG_POST_IMPRTNT'] > 0
		)
		{
			$eventId = Integration\Socialnetwork\Log::EVENT_ID_POST_IMPORTANT;
		}
		elseif (
			isset($arPost['UF_GRATITUDE'])
			&& (int)$arPost['UF_GRATITUDE'] > 0
		)
		{
			$eventId = Integration\Socialnetwork\Log::EVENT_ID_POST_GRAT;
		}
		elseif (
			isset($arPost['UF_BLOG_POST_VOTE'])
			&& (int)$arPost['UF_BLOG_POST_VOTE'] > 0
		)
		{
			$eventId = Integration\Socialnetwork\Log::EVENT_ID_POST_GRAT;
		}

		$arSoFields = array(
			"TITLE_TEMPLATE" => "#USER_NAME# ".GetMessage("BLG_SONET_TITLE"),
			"TITLE" => $arPost["TITLE"],
			"MESSAGE" => $text4message,
			"TEXT_MESSAGE" => $text4message,
			"ENABLE_COMMENTS" => (
				array_key_exists("ENABLE_COMMENTS", $arPost)
				&& $arPost["ENABLE_COMMENTS"] === "N"
					? "N"
					: "Y"
			),
			"EVENT_ID" => $eventId
		);

		if ($blogPostEventIdList === null)
		{
			$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
			$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();
		}

		$dbRes = CSocNetLog::GetList(
			array("ID" => "DESC"),
			array(
				"EVENT_ID" => $blogPostEventIdList,
				"SOURCE_ID" => $postID
			),
			false,
			false,
			array("ID", "ENTITY_TYPE", "ENTITY_ID", "EVENT_ID", "USER_ID")
		);
		if ($arLog = $dbRes->Fetch())
		{
			CSocNetLog::Update($arLog["ID"], $arSoFields);
			$socnetPerms = CBlogPost::GetSocNetPermsCode($postID);

			$profileBlogPost = false;
			foreach($socnetPerms as $perm)
			{
				if (preg_match('/^UP(\d+)$/', $perm, $matches))
				{
					$profileBlogPost = true;
					break;
				}
			}

			if(
				!$profileBlogPost
				&& !in_array("U".$arPost["AUTHOR_ID"], $socnetPerms)
			)
			{
				$socnetPerms[] = "U".$arPost["AUTHOR_ID"];
				if (CModule::IncludeModule("extranet"))
				{
					CSocNetLog::Update($arLog["ID"], array(
						"SITE_ID" => CExtranet::GetSitesByLogDestinations($socnetPerms, $arPost["AUTHOR_ID"], ($arParams['SITE_ID'] ?? false))
					));
				}
				$socnetPerms[] = "SA"; // socnet admin
			}

			\CSocNetLogRights::deleteByLogID($arLog["ID"]);
			\CSocNetLogRights::add($arLog["ID"], $socnetPerms);

			if (Loader::includeModule('crm'))
			{
				CCrmLiveFeedComponent::processCrmBlogPostRights($arLog["ID"], $arLog, $arPost, 'edit');
			}
		}
	}

	public static function DeleteLog($postID, $bMicroblog = false)
	{
		global $USER_FIELD_MANAGER;

		static $blogPostEventIdList = null;

		if (!CModule::IncludeModule('socialnetwork'))
		{
			return;
		}

		$dbComment = CBlogComment::GetList(
			array(),
			array(
				"POST_ID" => $postID,
			),
			false,
			false,
			array("ID")
		);

		while ($arComment = $dbComment->Fetch())
		{
			$dbRes = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => Array("blog_comment", "blog_comment_micro"),
					"SOURCE_ID" => $arComment["ID"]
				),
				false,
				false,
				array("ID")
			);
			while ($arRes = $dbRes->Fetch())
				CSocNetLog::Delete($arRes["ID"]);
		}

		if ($blogPostEventIdList === null)
		{
			$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
			$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();
		}

		$dbRes = CSocNetLog::GetList(
			array("ID" => "DESC"),
			array(
				"EVENT_ID" => $blogPostEventIdList,
				"SOURCE_ID" => $postID
			),
			false,
			false,
			array("ID")
		);
		while ($arRes = $dbRes->Fetch())
		{
			CSocNetLog::Delete($arRes["ID"]);
		}

		$arPostFields = $USER_FIELD_MANAGER->getUserFields('BLOG_POST', $postID, LANGUAGE_ID);
		if (
			!empty($arPostFields['UF_GRATITUDE'])
			&& !empty($arPostFields['UF_GRATITUDE']['VALUE'])
			&& intval($arPostFields['UF_GRATITUDE']['VALUE']) > 0
			&& Loader::includeModule('iblock')
		)
		{
			\CIBlockElement::delete(intval($arPostFields['UF_GRATITUDE']['VALUE']));
		}
	}

	public static function GetID($code, $blogID)
	{
		$postID = false;
		$blogID = intval($blogID);

		$code = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($code));
		if($code == '' || intval($blogID) <= 0)
			return false;

		if (
			!empty(static::$arBlogPostIdCache[$blogID."_".$code])
			&& intval(static::$arBlogPostIdCache[$blogID."_".$code]) > 0)
		{
			return static::$arBlogPostIdCache[$blogID."_".$code];
		}
		else
		{
			$arFilter = Array("CODE" => $code);
			if(intval($blogID) > 0)
				$arFilter["BLOG_ID"] = $blogID;
			$dbPost = CBlogPost::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID"));
			if($arPost = $dbPost->Fetch())
			{
				static::$arBlogPostIdCache[$blogID."_".$code] = $arPost["ID"];
				$postID = $arPost["ID"];
			}
		}

		return $postID;
	}

	public static function GetPostID($postID, $code, $allowCode = false)
	{
		$postID = intval($postID);
		$code = preg_replace("/[^a-zA-Z0-9_-]/is", "", trim($code ?? ''));
		if($code == '' && intval($postID) <= 0)
			return false;

		if($allowCode && $code <> '')
			return $code;

		return $postID;
	}

	public static function AddSocNetPerms($ID, $perms = array(), $arPost = array())
	{
		global $CACHE_MANAGER;

		if(intval($ID) <= 0)
			return false;

		$arResult = Array();

		// D - department
		// U - user
		// SG - socnet group
		// DR - department and hier
		// G - user group
		// AU - authorized user
		// CRMCONTACT - CRM contact
		//$bAU = false;

		if(
			empty($perms)
			|| in_array('UA', $perms, true)
			|| in_array('G2', $perms, true)
		) //if default rights or for everyone
		{
			CBlogPost::__AddSocNetPerms($ID, "U", $arPost["AUTHOR_ID"], "US".$arPost["AUTHOR_ID"]); // for myself
			$perms1 = CBlogPost::GetSocnetGroups("U", $arPost["AUTHOR_ID"]);
			foreach($perms1 as $val)
			{
				if($val <> '')
				{
					CBlogPost::__AddSocNetPerms($ID, "U", $arPost["AUTHOR_ID"], $val);

					if(!in_array($val, $arResult))
					{
						$arResult[] = $val;
					}
				}
			}
		}
		if(!empty($perms))
		{
			$perms = array_unique($perms);

			foreach($perms as $val)
			{
				if($val == "UA")
				{
					continue;
				}

				if($val <> '')
				{
					if (
						preg_match('/^(CRMCONTACT)(\d+)$/i', $val, $matches)
						|| preg_match('/^(DR)(\d+)$/i', $val, $matches)
						|| preg_match('/^(SG)(\d+)$/i', $val, $matches)
						|| preg_match('/^(AU)(\d+)$/i', $val, $matches)
						|| preg_match('/^(U)(\d+)$/i', $val, $matches)
						|| preg_match('/^(UP)(\d+)$/i', $val, $matches)
						|| preg_match('/^(D)(\d+)$/i', $val, $matches)
						|| preg_match('/^(G)(\d+)$/i', $val, $matches)
					)
					{
						$scT = $matches[1];
						$scID = $matches[2];
					}
					else
					{
						continue;
					}

					if($scT == "SG")
					{
						$permsNew = CBlogPost::GetSocnetGroups("G", $scID);
						foreach($permsNew as $val1)
						{
							CBlogPost::__AddSocNetPerms($ID, $scT, $scID, $val1);
							if(!in_array($val1, $arResult))
							{
								$arResult[] = $val1;
							}
						}
					}

					CBlogPost::__AddSocNetPerms($ID, $scT, $scID, $val);
					if(!in_array($val, $arResult))
					{
						$arResult[] = $val;
					}
				}
			}
		}

		BXClearCache(true, "/blog/getsocnetperms/".$ID);
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->ClearByTag("blog_post_getsocnetperms_".$ID);
		}

		return $arResult;
	}

	public static function UpdateSocNetPerms($ID, $perms = array(), $arPost = array())
	{
		global $DB;
		$ID = intval($ID);
		if($ID <= 0)
		{
			return false;
		}

		$strSql = "DELETE FROM b_blog_socnet_rights WHERE POST_ID=".$ID;
		$DB->Query($strSql);

		return CBlogPost::AddSocNetPerms($ID, $perms, $arPost);
	}

	public static function __AddSocNetPerms($ID, $entityType = "", $entityID = 0, $entity = null)
	{
		global $DB;

		static $allowedTypes = false;

		if ($allowedTypes === false)
		{
			$allowedTypes = Array("D", "U", "UP", "SG", "DR", "G", "AU");
			if (IsModuleInstalled('crm'))
			{
				$allowedTypes[] = "CRMCONTACT";
			}
		}

		if(intval($ID) > 0 && $entityType <> '' && $entity <> '' && in_array($entityType, $allowedTypes))
		{
			$arSCFields = Array("POST_ID" => $ID, "ENTITY_TYPE" => $entityType, "ENTITY_ID" => intval($entityID), "ENTITY" => $entity);
			$arSCInsert = $DB->PrepareInsert("b_blog_socnet_rights", $arSCFields);

			if ($arSCInsert[0] <> '')
			{
				$strSql =
					"INSERT INTO b_blog_socnet_rights(".$arSCInsert[0].") ".
					"VALUES(".$arSCInsert[1].")";
				$DB->Query($strSql);
				return true;
			}
		}
		return false;
	}

	public static function GetSocNetGroups($entity_type, $entity_id, $operation = "view_post")
	{
		$entity_id = intval($entity_id);
		if($entity_id <= 0)
			return false;
		if(!CModule::IncludeModule("socialnetwork"))
			return false;
		$feature = "blog";

		$arResult = array();

		if($entity_type == "G")
		{
			$prefix = "SG".$entity_id."_";
			$letter = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $entity_id, $feature, $operation);
			$arResult = array_merge($arResult, self::getFullGroupRoleSet($letter, $prefix));
		}
		else
		{
			$prefix = "SU".$entity_id."_";
			$letter = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $entity_id, $feature, $operation);
			switch($letter)
			{
				case "A"://All
					$arResult[] = 'G2';
					break;
				case "C"://Authorized
					$arResult[] = 'AU';
					break;
				case "E"://Friends of friends (has no rights yet) so it counts as
				case "M"://Friends
					$arResult[] = $prefix.'M';
					break;
				case "Z"://Personal
					$arResult[] = $prefix.'Z';
					break;
			}
		}

		return $arResult;
	}

	public static function getFullGroupRoleSet($role = "", $prefix = "")
	{
		$result = array();

		switch($role)
		{
			case SONET_ROLES_ALL:
				$result[] = 'O'.$prefix.SONET_ROLES_ALL;
				$result[] = 'O'.$prefix.SONET_ROLES_AUTHORIZED;
				$result[] = $prefix.SONET_ROLES_USER;
				$result[] = $prefix.SONET_ROLES_MODERATOR;
				$result[] = $prefix.SONET_ROLES_OWNER;
				break;
			case SONET_ROLES_AUTHORIZED:
				$result[] = 'O'.$prefix.SONET_ROLES_AUTHORIZED;
				$result[] = $prefix.SONET_ROLES_USER;
				$result[] = $prefix.SONET_ROLES_MODERATOR;
				$result[] = $prefix.SONET_ROLES_OWNER;
				break;
			case SONET_ROLES_USER:
				$result[] = 'O'.$prefix.SONET_ROLES_AUTHORIZED;
				$result[] = $prefix.SONET_ROLES_USER;
				$result[] = $prefix.SONET_ROLES_MODERATOR;
				$result[] = $prefix.SONET_ROLES_OWNER;
				break;
			case SONET_ROLES_MODERATOR:
				$result[] = $prefix.SONET_ROLES_MODERATOR;
				$result[] = $prefix.SONET_ROLES_OWNER;
				break;
			case SONET_ROLES_OWNER:
				$result[] = $prefix.SONET_ROLES_OWNER;
				break;
		}

		return $result;
	}

	public static function getSocNetPerms($ID, $useCache = true)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intval($ID);
		if($ID <= 0)
			return false;

		$arResult = array();

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = 'blog_post_getsocnetperms_'.$ID;
		$cacheDir = '/blog/getsocnetperms/'.$ID;

		$obCache = new CPHPCache;
		if(
			$obCache->InitCache($cacheTtl, $cacheId, $cacheDir)
			&& $useCache
		)
		{
			$arResult = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();

			$strSql = "SELECT SR.ENTITY_ID, SR.ENTITY_TYPE, SR.ENTITY FROM b_blog_socnet_rights SR
				INNER JOIN b_blog_post P ON (P.ID = SR.POST_ID)
				WHERE SR.POST_ID=".$ID." ORDER BY SR.ENTITY ASC";
			$dbRes = $DB->Query($strSql);
			while($arRes = $dbRes->Fetch())
			{
				$arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]][] = $arRes["ENTITY"];
			}

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("blog_post_getsocnetperms_".$ID);
				$CACHE_MANAGER->EndTagCache();
			}
			$obCache->EndDataCache($arResult);
		}

		return $arResult;
	}

	public static function GetSocNetPermsName($ID)
	{
		global $DB;
		$ID = intval($ID);
		if($ID <= 0)
			return false;

		$arResult = Array();
		$strSql = "SELECT SR.ENTITY_TYPE, SR.ENTITY_ID, SR.ENTITY,
						U.NAME as U_NAME, U.LAST_NAME as U_LAST_NAME, U.SECOND_NAME as U_SECOND_NAME, U.LOGIN as U_LOGIN, U.PERSONAL_PHOTO as U_PERSONAL_PHOTO, U.EXTERNAL_AUTH_ID as U_EXTERNAL_AUTH_ID,
						EL.NAME as EL_NAME
					FROM b_blog_socnet_rights SR
					INNER JOIN b_blog_post P
						ON (P.ID = SR.POST_ID)
					LEFT JOIN b_user U
						ON (U.ID = SR.ENTITY_ID AND SR.ENTITY_TYPE = 'U')
					LEFT JOIN b_iblock_section EL
						ON (EL.ID = SR.ENTITY_ID AND SR.ENTITY_TYPE = 'DR' AND EL.ACTIVE = 'Y')
					WHERE
						SR.POST_ID = " . $ID . "
					ORDER BY SR.ID ASC
					LIMIT 300
					";

		$dbRes = $DB->Query($strSql);
		while($arRes = $dbRes->GetNext())
		{
			if (
				!isset($arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]])
				 || !is_array($arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]]))
			{
				$arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]] = $arRes;
			}
			if (
				!isset($arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]]["ENTITY"])
				|| !is_array($arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]]["ENTITY"])
			)
			{
				$arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]]["ENTITY"] = [];
			}
			$arResult[$arRes["ENTITY_TYPE"]][$arRes["ENTITY_ID"]]["ENTITY"][] = $arRes["ENTITY"];
		}
		return $arResult;
	}

	public static function GetSocNetPermsCode($ID)
	{
		global $DB;
		$ID = intval($ID);
		if($ID <= 0)
			return false;

		$arResult = Array();
		$strSql = "SELECT SR.ENTITY FROM b_blog_socnet_rights SR
						INNER JOIN b_blog_post P ON (P.ID = SR.POST_ID)
						WHERE SR.POST_ID=".$ID."
						ORDER BY SR.ENTITY ASC";
		$dbRes = $DB->Query($strSql);
		while($arRes = $dbRes->Fetch())
		{
			if(!in_array($arRes["ENTITY"], $arResult))
				$arResult[] = $arRes["ENTITY"];
		}
		return $arResult;
	}

	public static function ChangeSocNetPermission($entity_type, $entity_id, $operation)
	{
		global $DB;
		$entity_id = intval($entity_id);
		$perms = CBlogPost::GetSocnetGroups($entity_type, $entity_id, $operation);
		$type = "U";
		$type2 = "US";
		if($entity_type == "G")
			$type = $type2 = "SG";
		$DB->Query("DELETE FROM b_blog_socnet_rights
					WHERE
						ENTITY_TYPE = '".$type."'
						AND ENTITY_ID = ".$entity_id."
						AND ENTITY <> '".$type2.$entity_id."'
						AND ENTITY <> '".$type.$entity_id."'
						");
		foreach($perms as $val)
		{
			$DB->Query("INSERT INTO b_blog_socnet_rights (POST_ID, ENTITY_TYPE, ENTITY_ID, ENTITY)
						SELECT SR.POST_ID, SR.ENTITY_TYPE, SR.ENTITY_ID, '".$DB->ForSql($val)."' FROM b_blog_socnet_rights SR
						WHERE SR.ENTITY = '".$type2.$entity_id."'");
		}
	}

	public static function GetSocNetPostsPerms($entity_type, $entity_id)
	{
		global $DB;
		$entity_id = intval($entity_id);
		if($entity_id <= 0)
			return false;

		$type = "U";
		$type2 = "US";
		if($entity_type == "G")
			$type = $type2 = "SG";

		$arResult = Array();
		$dbRes = $DB->Query("
			SELECT SR.POST_ID, SR.ENTITY, SR.ENTITY_ID, SR.ENTITY_TYPE FROM b_blog_socnet_rights SR
			WHERE
				SR.POST_ID IN (SELECT POST_ID FROM b_blog_socnet_rights WHERE ENTITY_TYPE='".$type."' AND ENTITY_ID=".$entity_id." AND ENTITY = '".$type.$entity_id."')
				AND SR.ENTITY <> '".$type2.$entity_id."'
		");
		while($arRes = $dbRes->Fetch())
		{
			$arResult[$arRes["POST_ID"]]["PERMS"][] = $arRes["ENTITY"];
			$arResult[$arRes["POST_ID"]]["PERMS_FULL"][$arRes["ENTITY_TYPE"].$arRes["ENTITY_ID"]] = Array("TYPE" => $arRes["ENTITY_TYPE"], "ID" => $arRes["ENTITY_ID"]);
		}
		return $arResult;
	}

	public static function GetSocNetPostPerms(
		$postId = 0,
		$bNeedFull = false,
		$userId = false,
		$postAuthor = 0
	)
	{
		global $USER;

		$cId = md5(serialize(func_get_args()));

		if (
			is_array($postId)
			&& isset($postId["POST_ID"])
		)
		{
			$arParams = $postId;
			$postId = intval($arParams["POST_ID"]);
			$bNeedFull = (isset($arParams["NEED_FULL"]) ? $arParams["NEED_FULL"] : false);
			$userId = (isset($arParams["USER_ID"]) ? $arParams["USER_ID"] : false);
			$postAuthor = (isset($arParams["POST_AUTHOR_ID"]) ? $arParams["POST_AUTHOR_ID"] : 0);
			$bPublic = (isset($arParams["PUBLIC"]) ? $arParams["PUBLIC"] : false);
			$logId = (isset($arParams["LOG_ID"]) ? intval($arParams["LOG_ID"]) : false);
			$bIgnoreAdmin = (isset($arParams["IGNORE_ADMIN"]) ? $arParams["IGNORE_ADMIN"] : false);
		}
		else
		{
			$bPublic = $logId = $bIgnoreAdmin = false;
		}

		if(!$userId)
		{
			$userId = intval($USER->GetID());
			$bByUserId = false;
		}
		else
		{
			$userId = intval($userId);
			$bByUserId = true;
		}
		$postId = intval($postId);
		if($postId <= 0)
		{
			return false;
		}

		if (!empty(static::$arSocNetPostPermsCache[$cId]))
		{
			return static::$arSocNetPostPermsCache[$cId];
		}

		if (!CModule::IncludeModule("socialnetwork"))
		{
			return false;
		}

		$perms = BLOG_PERMS_DENY;
		$arAvailPerms = array_keys($GLOBALS["AR_BLOG_PERMS"]);

		if(!$bByUserId)
		{
			if (CSocNetUser::IsCurrentUserModuleAdmin())
			{
				$perms = $arAvailPerms[count($arAvailPerms) - 1]; // max
			}
		}
		elseif(
			!$bIgnoreAdmin
			&& CSocNetUser::IsUserModuleAdmin($userId)
		)
		{
			$perms = $arAvailPerms[count($arAvailPerms) - 1]; // max
		}

		if(intval($postAuthor) <= 0)
		{
			$dbPost = CBlogPost::GetList(
				[],
				["ID" => $postId],
				false,
				false,
				[
					"ID",
					"AUTHOR_ID",
				]
			);
			$arPost = $dbPost->Fetch();
		}
		else
		{
			$arPost["AUTHOR_ID"] = $postAuthor;
		}

		if (($arPost["AUTHOR_ID"] ?? null) == $userId)
		{
			$perms = BLOG_PERMS_FULL;
		}

		if($perms <= BLOG_PERMS_DENY)
		{
			$arPerms = CBlogPost::GetSocNetPerms($postId);

			if (
				intval($userId) > 0
				&& IsModuleInstalled('mail')
			) // check for email authorization users
			{
				$rsUsers = CUser::GetList(
					"ID",
					"asc",
					array(
						"ID" => $userId
					),
					array(
						"FIELDS" => array("ID", "EXTERNAL_AUTH_ID"),
						"SELECT" => array("UF_DEPARTMENT")
					)
				);

				if($arUser = $rsUsers->Fetch())
				{
					if ($arUser["EXTERNAL_AUTH_ID"] == 'email')
					{
						return (
							isset($arPerms["U"])
							&& isset($arPerms["U"][$userId])
								? BLOG_PERMS_WRITE
								: BLOG_PERMS_DENY
						);
					}
					elseif (
						$bPublic
						&& (
							!is_array($arUser["UF_DEPARTMENT"])
							|| empty($arUser["UF_DEPARTMENT"])
							|| intval($arUser["UF_DEPARTMENT"][0]) <= 0
						)
						&& CModule::IncludeModule('extranet')
						&& ($extranet_site_id = CExtranet::GetExtranetSiteID()) // for extranet users in public section
					)
					{
						if ($logId)
						{
							$arPostSite = array();
							$rsLogSite = CSocNetLog::GetSite($logId);
							while ($arLogSite = $rsLogSite->Fetch())
							{
								$arPostSite[] = $arLogSite["LID"];
							}

							if (!in_array($extranet_site_id, $arPostSite))
							{
								return BLOG_PERMS_DENY;
							}
						}
						else
						{
							return BLOG_PERMS_DENY;
						}
					}
				}
				else
				{
					return BLOG_PERMS_DENY;
				}
			}

			$arEntities = Array();
			if (!empty(static::$arUACCache[$userId]))
			{
				$arEntities = static::$arUACCache[$userId];
			}
			else
			{
				$arCodes = CAccess::GetUserCodesArray($userId);
				foreach($arCodes as $code)
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
				static::$arUACCache[$userId] = $arEntities;
			}

			foreach($arPerms as $t => $val)
			{
				foreach($val as $id => $p)
				{
					if(!is_array($p))
					{
						$p = array();
					}
					if($userId > 0 && $t == "U" && $userId == $id)
					{
						$perms = BLOG_PERMS_WRITE;
						if(in_array("US".$userId, $p)) // if author
							$perms = BLOG_PERMS_FULL;
						break;
					}
					if(
						in_array("G2", $p)
						|| ($userId > 0 && in_array("AU", $p))
					)
					{
						if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
						{
							$perms = BLOG_PERMS_WRITE;
						}
						else
						{
							$currentUserType = self::getCurrentUserType($userId);

							if ($currentUserType === 'employee')
							{
								$perms = BLOG_PERMS_WRITE;
							}
							elseif (
								$currentUserType === 'extranet'
								&& Loader::includeModule('extranet')
								&& ($extranetSiteId = CExtranet::getExtranetSiteId())
							)
							{
								$res = \Bitrix\Socialnetwork\LogTable::getList([
									'filter' => [
										'=SOURCE_ID' => $postId,
										'@EVENT_ID' => (new \Bitrix\Socialnetwork\Livefeed\BlogPost)->getEventId(),
									],
									'select' => [ 'ID' ],
								]);
								if ($logFields = $res->fetch())
								{
									$res = \Bitrix\Socialnetwork\LogSiteTable::getList([
										'filter' => [
											'=LOG_ID' => $logFields['ID'],
											'=SITE_ID' => $extranetSiteId,
										],
										'select' => [ 'LOG_ID' ],
									]);
									if ($res->fetch())
									{
										$perms = BLOG_PERMS_WRITE;
									}
								}
							}
						}

						if ($perms === BLOG_PERMS_WRITE)
						{
							break;
						}
					}
					if($t == "SG")
					{
						if(!empty($arEntities["SG"][$id]))
						{
							foreach($arEntities["SG"][$id] as $gr)
							{
								if(in_array("SG".$id."_".$gr, $p))
								{
									$perms = BLOG_PERMS_READ;
									break;
								}
							}
						}
					}

					if($t == "DR" && !empty($arEntities["DR"]))
					{
						if(in_array("DR".$id, $arEntities["DR"]))
						{
							$perms = BLOG_PERMS_WRITE;
							break;
						}
					}
				}

				if($perms > BLOG_PERMS_DENY)
				{
					break;
				}
			}

			if(
				$perms <= BLOG_PERMS_READ
				&& !empty($arPerms['SG'])
			) // check OSG
			{
				$openedWorkgroupsList = [];
				foreach ($arPerms['SG'] as $arSGPerm)
				{
					if (empty($arSGPerm))
					{
						continue;
					}

					foreach($arSGPerm as $sgPerm)
					{
						if (!preg_match('/^OSG(\d+)_'.(!$userId ? SONET_ROLES_ALL : SONET_ROLES_AUTHORIZED).'$/', $sgPerm, $matches))
						{
							continue;
						}

						$openedWorkgroupsList[] = (int)$matches[1];
					}
				}



				if (
					!empty($openedWorkgroupsList)
					&& Loader::includeModule('socialnetwork')
					&& \Bitrix\Socialnetwork\Helper\Workgroup::checkAnyOpened($openedWorkgroupsList)
					&& (
						!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
						|| self::getCurrentUserType($userId) === 'employee'
					)
				)
				{
					$perms = BLOG_PERMS_READ;
				}
			}

			if(
				$bNeedFull
				&& $perms < BLOG_PERMS_FULL
			)
			{
				$arGroupsId = Array();
				if(!empty($arPerms["SG"]))
				{
					foreach($arPerms["SG"] as $gid => $val)
					{
						if(!empty($arEntities["SG"][$gid]))
							$arGroupsId[] = $gid;
					}
				}

				$operation = Array("full_post", "moderate_post", "write_post", "premoderate_post");
				if(!empty($arGroupsId))
				{
					foreach($operation as $v)
					{
						if($perms <= BLOG_PERMS_READ)
						{
							$f = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $arGroupsId, "blog", $v);
							if(is_array($f))
							{
								foreach($f as $gid => $val)
								{
									if(in_array($val, $arEntities["SG"][$gid]))
									{
										switch($v)
										{
											case "full_post":
												$perms = BLOG_PERMS_FULL;
												break;
											case "moderate_post":
												$perms = BLOG_PERMS_MODERATE;
												break;
											case "write_post":
												$perms = BLOG_PERMS_WRITE;
												break;
											case "premoderate_post":
												$perms = BLOG_PERMS_PREMODERATE;
												break;
										}
									}
								}
							}
						}
					}

					// check if landing
					if ($perms < BLOG_PERMS_READ)
					{
						$res = \Bitrix\Socialnetwork\WorkgroupTable::getList([
							'filter' => [
								'@ID' => $arGroupsId,
								'ACTIVE' => 'Y',
								'LANDING' => 'Y'
							],
							'select' => ['ID']
						]);
						if ($res->fetch())
						{
							$perms = BLOG_PERMS_READ;
						}
					}
				}
			}
		}

		static::$arSocNetPostPermsCache[$cId] = $perms;

		return $perms;
	}

	private static function getCurrentUserType($userId)
	{
		static $currentUserType = null;

		if ($userId <= 0)
		{
			return null;
		}

		if (
			$currentUserType === null
			&& Loader::includeModule('intranet')
		)
		{
			$res = \Bitrix\Intranet\UserTable::getList([
				'filter' => [
					'ID' => $userId,
				],
				'select' => [ 'ID', 'USER_TYPE' ],
			]);
			if ($userFields = $res->fetch())
			{
				$currentUserType = $userFields['USER_TYPE'];
			}
		}

		return $currentUserType;
	}

	public static function NotifyIm($arParams)
	{
		static $blogPostEventIdList = null;

		$arUserIDSent = array();

		if (!CModule::IncludeModule("im"))
		{
			return $arUserIDSent;
		}

		$arUsers = array();

		if(!empty($arParams["TO_USER_ID"]))
		{
			foreach($arParams["TO_USER_ID"] as $val)
			{
				$val = intval($val);
				if (
					$val > 0
					&& $val != $arParams["FROM_USER_ID"]
				)
				{
					$arUsers[] = $val;
				}
			}
		}
		if(!empty($arParams["TO_SOCNET_RIGHTS"]))
		{
			foreach($arParams["TO_SOCNET_RIGHTS"] as $v)
			{
				if(mb_substr($v, 0, 1) == "U")
				{
					$u = intval(mb_substr($v, 1));
					if (
						$u > 0
						&& !in_array($u, $arUsers)
						&& (
							!array_key_exists("U", $arParams["TO_SOCNET_RIGHTS_OLD"])
							|| empty($arParams["TO_SOCNET_RIGHTS_OLD"]["U"][$u])
						)
						&& $u != $arParams["FROM_USER_ID"]
					)
					{
						$arUsers[] = $u;
					}
				}
			}
		}

		if (!empty($arUsers))
		{
			$rsUser = \Bitrix\Main\UserTable::getList(array(
				'order' => array(),
				'filter' => array(
					"ID" => $arUsers,
					"=ACTIVE" => "Y",
					"!=EXTERNAL_AUTH_ID" => 'email'
				),
				'select' => array("ID")
			));

			$arUsers = array();

			while ($arUser = $rsUser->fetch())
			{
				$arUsers[] = $arUser["ID"];
			}
		}

		$arMessageFields = array(
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"TO_USER_ID" => "",
			"FROM_USER_ID" => $arParams["FROM_USER_ID"],
			"NOTIFY_TYPE" => IM_NOTIFY_FROM,
			"NOTIFY_ANSWER" => "Y",
			"NOTIFY_MODULE" => "blog",
			"PARSE_LINK" => "N"
		);

		$aditGM = $authorName = $authorAvatarUrl = "";
		if(intval($arParams["FROM_USER_ID"]) > 0)
		{
			$dbUser = CUser::GetByID($arParams["FROM_USER_ID"]);
			if($arUser = $dbUser->Fetch())
			{
				if($arUser["PERSONAL_GENDER"] == "F")
				{
					$aditGM = "_FEMALE";
				}

				if (!empty($arUser["PERSONAL_PHOTO"]))
				{
					$avatarSize = (isset($arParams["PUSH_AVATAR_SIZE"]) && intval($arParams["PUSH_AVATAR_SIZE"]) > 0 ? intval($arParams["PUSH_AVATAR_SIZE"]) : 100);
					$imageResized = CFile::resizeImageGet(
						$arUser["PERSONAL_PHOTO"],
						array(
							"width" => $avatarSize,
							"height" => $avatarSize
						),
						BX_RESIZE_IMAGE_EXACT
					);
					if ($imageResized)
					{
						$authorAvatarUrl = \Bitrix\Im\Common::getPublicDomain().$imageResized["src"];
					}
				}

				$authorName = (
					$arUser
						? CUser::FormatName(CSite::GetNameFormat(), $arUser, true)
						: GetMessage("BLG_GP_PUSH_USER")
				);
			}
		}

		if (CModule::IncludeModule("socialnetwork"))
		{
			if ($blogPostEventIdList === null)
			{
				$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
				$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();
			}

			$rsLog = CSocNetLog::GetList(
				array(),
				array(
					"EVENT_ID" => $blogPostEventIdList,
					"SOURCE_ID" => $arParams["ID"]
				),
				false,
				false,
				array("ID")
			);
			if ($arLog = $rsLog->Fetch())
			{
				$arMessageFields["LOG_ID"] = $arLog["ID"];
			}
		}

		$arTitle = self::processNotifyTitle($arParams["TITLE"]);
		$arParams["TITLE"] = $arTitle['TITLE'];
		$arParams["TITLE_OUT"] = $arTitle['TITLE_OUT'];
		$bTitleEmpty = $arTitle['IS_TITLE_EMPTY'];

		$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
		$urlOriginal = $arParams["URL"];

		if (IsModuleInstalled("extranet"))
		{
			$user_path = COption::GetOptionString("socialnetwork", "user_page", false, SITE_ID);
			if (
				$user_path <> ''
				&& mb_strpos($arParams["URL"], $user_path) === 0
			)
			{
				$arParams["URL"] = str_replace($user_path, "#USER_PATH#", $arParams["URL"]);
			}
		}

		// notify mentioned users
		if(!empty($arParams["MENTION_ID"]))
		{
			if(!is_array($arParams["MENTION_ID_OLD"] ?? null))
			{
				$arParams["MENTION_ID_OLD"] = Array();
			}

			$arUserIdToMention = $arNewRights = array();

			foreach($arParams["MENTION_ID"] as $val)
			{
				$val = intval($val);
				if (
					intval($val) > 0
					&& !in_array($val, $arParams["MENTION_ID_OLD"] ?? [])
					&& $val != $arParams["FROM_USER_ID"]
				)
				{
					$postPerm = CBlogPost::GetSocNetPostPerms(array(
						"POST_ID" => $arParams["ID"],
						"NEED_FULL" => true,
						"USER_ID" => $val,
						"IGNORE_ADMIN" => true
					));

					if (
						$postPerm >= BLOG_PERMS_READ
						|| $arParams["TYPE"] === "COMMENT"
					)
					{
						$arUserIdToMention[] = $val;
					}
				}
			}

			$arUserIdToMention = array_unique($arUserIdToMention);

			foreach($arUserIdToMention as $val)
			{
				$val = (int)$val;
				$arMessageFields["TO_USER_ID"] = $val;

				if (IsModuleInstalled("extranet"))
				{
					$arTmp = CSocNetLogTools::ProcessPath(
						array(
							"URL" => $arParams["URL"],
						),
						$val,
						SITE_ID
					);
					$url = $arTmp["URLS"]["URL"];

					$serverName = (
					mb_strpos($url, "http://") === 0
						|| mb_strpos($url, "https://") === 0
							? ""
							: $arTmp["SERVER_NAME"]
					);
				}
				else
				{
					$url = $arParams["URL"];
				}

				$arMessageFields["PUSH_PARAMS"] = array(
					"ACTION" => "mention"
				);

				if (!empty($authorAvatarUrl))
				{
					$arMessageFields["PUSH_PARAMS"]["ADVANCED_PARAMS"] = array(
						'avatarUrl' => $authorAvatarUrl,
						'senderName' => $authorName
					);
				}

				if ($arParams["TYPE"] === "POST")
				{
					$arMessageFields["NOTIFY_EVENT"] = "mention";
					$arMessageFields["NOTIFY_TAG"] = "BLOG|POST_MENTION|".$arParams["ID"];
					$arMessageFields["NOTIFY_SUB_TAG"] = "BLOG|POST_MENTION|".$arParams["ID"].'|'.$val;

					if (!$bTitleEmpty)
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_6".$aditGM,
							array(
								"#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>"
							),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
								"BLG_GP_IM_6".$aditGM,
								array(
									"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
								),
								$languageId
						)." ".$serverName.$url."";
						$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_PUSH_6".$aditGM,
							array(
								"#name#" => htmlspecialcharsbx($authorName),
								"#title#" => htmlspecialcharsbx($arParams["TITLE"])
							),
							$languageId
						);
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_6A".$aditGM,
							array(
								"#post#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">". Loc::getMessage("BLG_GP_IM_6B", null, $languageId) ."</a>"
							),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_6A".$aditGM,
							array(
								"#post#" => Loc::getMessage("BLG_GP_IM_6B", null, $languageId)
							),
							$languageId
						)." ".$serverName.$url."";
						$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_PUSH_6A".$aditGM,
							array(
								"#name#" => htmlspecialcharsbx($authorName),
								"#post#" => Loc::getMessage("BLG_GP_IM_6B", null, $languageId)
							),
							$languageId
						);
					}
				}
				elseif ($arParams["TYPE"] === "COMMENT")
				{
					$arMessageFields["NOTIFY_EVENT"] = "mention_comment";
					$arMessageFields["NOTIFY_TAG"] = "BLOG|COMMENT_MENTION|".$arParams["ID"].'|'.$arParams["COMMENT_ID"];
					$arMessageFields["NOTIFY_SUB_TAG"] = "BLOG|COMMENT_MENTION|".$arParams["COMMENT_ID"].'|'.$val;

					$commentCropped = truncateText($arParams["BODY"], 100);

					if (!$bTitleEmpty)
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_71".$aditGM,
							array(
								"#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>",
								"#comment#" => $commentCropped
							),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_71".$aditGM,
							array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"]),
								"#comment#" => $arParams["BODY"]
							),
							$languageId
						)." ".$serverName.$url."";
						$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_PUSH_71".$aditGM,
							array(
								"#name#" => htmlspecialcharsbx($authorName),
								"#title#" => htmlspecialcharsbx($arParams["TITLE"]),
								"#comment#" => $commentCropped
							),
							$languageId
						);
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_71A".$aditGM,
							array(
								"#post#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_7B", null, $languageId)."</a>",
								"#comment#" => $commentCropped
							),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_71A".$aditGM,
							array(
								"#post#" => Loc::getMessage("BLG_GP_IM_7B", null, $languageId),
								"#comment#" => $arParams["BODY"]
							)
						)." ".$serverName.$url."";
						$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_PUSH_71A".$aditGM,
							array(
								"#name#" => htmlspecialcharsbx($authorName),
								"#post#" => Loc::getMessage("BLG_GP_IM_7B", null, $languageId),
								"#comment#" => $commentCropped
							),
							$languageId
						);
					}
				}

				$arMessageFields["PUSH_PARAMS"]["TAG"] = $arMessageFields["NOTIFY_TAG"];

				$ID = CIMNotify::Add($arMessageFields);
				$arUserIDSent[] = $val;

				if (
					(int)$ID > 0
					&& (int)$arMessageFields["LOG_ID"] > 0
				)
				{
					foreach(GetModuleEvents("blog", "OnBlogPostMentionNotifyIm", true) as $arEvent)
					{
						ExecuteModuleEventEx($arEvent, Array($ID, $arMessageFields));
					}
				}
			}
		}

		$notifySubTag = false;
		// notify 'to' users and an author
		if (!empty($arUsers))
		{
			if($arParams["TYPE"] === "POST")
			{
				$arMessageFields["PUSH_PARAMS"] = array(
					"ACTION" => "post"
				);

				if (!empty($authorAvatarUrl))
				{
					$arMessageFields["PUSH_PARAMS"]["ADVANCED_PARAMS"] = array(
						'avatarUrl' => $authorAvatarUrl,
						'senderName' => $authorName
					);
				}

				$arMessageFields["NOTIFY_EVENT"] = "post";

				$notifySubTag = $arMessageFields["NOTIFY_TAG"] = "BLOG|POST|".$arParams["ID"];

				if (!$bTitleEmpty)
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_1_MSGVER_1".$aditGM,
						array(
							"#title#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>"
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_1_MSGVER_1".$aditGM,
							array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."";
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_1".$aditGM,
						array(
							"#name#" => $authorName,
							"#title#" => $arParams["TITLE"]
						),
						$languageId
					);
				}
				else
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_1A".$aditGM,
						array(
							"#post#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_1B", null, $languageId)."</a>"
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_1A".$aditGM,
							array(
								"#post#" => Loc::getMessage("BLG_GP_IM_1B", null, $languageId)
							)
						)." ".$serverName.$arParams["URL"]."";
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_1A".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#post#" => Loc::getMessage("BLG_GP_IM_1B", null, $languageId)
						),
						$languageId
					);
				}
			}
			elseif($arParams["TYPE"] === "COMMENT")
			{
				$arMessageFields["PUSH_PARAMS"] = array(
					"ACTION" => "comment"
				);

				if (!empty($authorAvatarUrl))
				{
					$arMessageFields["PUSH_PARAMS"]["ADVANCED_PARAMS"] = array(
						'avatarUrl' => $authorAvatarUrl,
						'senderName' => $authorName
					);
				}

				$arMessageFields["NOTIFY_EVENT"] = "comment";

				$arMessageFields["NOTIFY_TAG"] = "BLOG|COMMENT|".$arParams["ID"].'|'.$arParams["COMMENT_ID"];
				$notifySubTag = "BLOG|COMMENT|".$arParams["COMMENT_ID"];

				$commentCropped = truncateText($arParams["BODY"], 100);

				if (!$bTitleEmpty)
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_41".$aditGM,
						array(
							"#title#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>",
							"#comment#" => $commentCropped
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_41".$aditGM,
							array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"]),
								"#comment#" => $arParams["BODY"]
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."\n\n".$arParams["BODY"];
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_41".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#title#" => htmlspecialcharsbx($arParams["TITLE"]),
							"#comment#" => $commentCropped
						),
						$languageId
					);

					$arMessageFields["NOTIFY_MESSAGE_AUTHOR"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_51".$aditGM,
						array(
							"#title#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>",
							"#comment#" => $commentCropped
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_AUTHOR_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_51".$aditGM,
							array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"]),
								"#comment#" => $arParams["BODY"]
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."\n\n".$arParams["BODY"];
					$arMessageFields["PUSH_MESSAGE_AUTHOR"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_51".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#title#" => htmlspecialcharsbx($arParams["TITLE"]),
							"#comment#" => $commentCropped
						),
						$languageId
					);
				}
				else
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_41A".$aditGM,
						array(
							"#post#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_4B", null, $languageId)."</a>",
							"#comment#" => $commentCropped
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_41A".$aditGM,
							array(
								"#post#" => Loc::getMessage("BLG_GP_IM_4B", null, $languageId),
								"#comment#" => $arParams["BODY"]
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."\n\n".$arParams["BODY"];
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_41A".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#post#" => Loc::getMessage("BLG_GP_IM_4B", null, $languageId),
							"#comment#" => $commentCropped
						),
						$languageId
					);

					$arMessageFields["NOTIFY_MESSAGE_AUTHOR"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_51A".$aditGM,
						array(
							"#post#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_5B", null, $languageId)."</a>",
							"#comment#" => $commentCropped
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_AUTHOR_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_51A".$aditGM,
							Array(
								"#post#" => Loc::getMessage("BLG_GP_IM_5B", null, $languageId),
								"#comment#" => $arParams["BODY"]
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."\n\n".$arParams["BODY"];
					$arMessageFields["PUSH_MESSAGE_AUTHOR"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_51A".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#post#" => Loc::getMessage("BLG_GP_IM_5B", null, $languageId),
							"#comment#" => $commentCropped
						),
						$languageId
					);
				}
			}
			elseif($arParams["TYPE"] === "SHARE")
			{
				$arMessageFields["PUSH_PARAMS"] = array(
					"ACTION" => "share"
				);

				if (!empty($authorAvatarUrl))
				{
					$arMessageFields["PUSH_PARAMS"]["ADVANCED_PARAMS"] = array(
						'avatarUrl' => $authorAvatarUrl,
						'senderName' => $authorName
					);
				}

				$arMessageFields["NOTIFY_EVENT"] = "share";
				$arMessageFields["NOTIFY_TAG"] = "BLOG|SHARE|".$arParams["ID"];
				$notifySubTag = "BLOG|POST|".$arParams["ID"];

				if (!$bTitleEmpty)
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_8".$aditGM,
						array(
							"#title#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>"
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_8".$aditGM,
							Array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."";
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_8".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#title#" => htmlspecialcharsbx($arParams["TITLE"])
						),
						$languageId
					);
				}
				else
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_8A".$aditGM,
						array(
							"#post#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_8B", null, $languageId)."</a>"
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_8A".$aditGM,
							array(
								"#post#" => Loc::getMessage("BLG_GP_IM_8B", null, $languageId)
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."";
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_8A".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#post#" => Loc::getMessage("BLG_GP_IM_8B", null, $languageId)
						),
						$languageId
					);
				}
			}
			elseif($arParams["TYPE"] === "SHARE2USERS")
			{
				$arMessageFields["PUSH_PARAMS"] = array(
					"ACTION" => "share2users"
				);

				if (!empty($authorAvatarUrl))
				{
					$arMessageFields["PUSH_PARAMS"]["ADVANCED_PARAMS"] = array(
						'avatarUrl' => $authorAvatarUrl,
						'senderName' => $authorName
					);
				}

				$arMessageFields["NOTIFY_EVENT"] = "share2users";
				$arMessageFields["NOTIFY_TAG"] = "BLOG|SHARE2USERS|".$arParams["ID"];
				$notifySubTag = "BLOG|POST|".$arParams["ID"];

				if (!$bTitleEmpty)
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_9".$aditGM,
						array(
							"#title#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>"
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_9".$aditGM,
							array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."";
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_9".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#title#" => htmlspecialcharsbx($arParams["TITLE"])
						),
						$languageId
					);
				}
				else
				{
					$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_IM_9A".$aditGM,
						array(
							"#post#" => "<a href=\"".$arParams["URL"]."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_9B", null, $languageId)."</a>"
						),
						$languageId
					);
					$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_9A".$aditGM,
							array(
								"#post#" => Loc::getMessage("BLG_GP_IM_9B", null, $languageId)
							),
							$languageId
						)." ".$serverName.$arParams["URL"]."";
					$arMessageFields["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
						"BLG_GP_PUSH_9A".$aditGM,
						array(
							"#name#" => htmlspecialcharsbx($authorName),
							"#post#" => Loc::getMessage("BLG_GP_IM_9B", null, $languageId)
						),
						$languageId
					);
				}
			}

			$arMessageFields["PUSH_PARAMS"]["TAG"] = $arMessageFields["NOTIFY_TAG"];
		}

		foreach($arUsers as $v)
		{
			if(
				in_array($v, $arUserIDSent)
				|| (
					!empty($arParams["EXCLUDE_USERS"])
					&& (int)$arParams["EXCLUDE_USERS"][$v] > 0
				)
			)
			{
				continue;
			}

			if (IsModuleInstalled("extranet"))
			{
				$arTmp = CSocNetLogTools::ProcessPath(
					array(
						"URL" => $arParams["URL"],
					),
					$v,
					SITE_ID
				);
				$url = $arTmp["URLS"]["URL"];

				$serverName = (
				mb_strpos($url, "http://") === 0
				|| mb_strpos($url, "https://") === 0
					? ""
					: $arTmp["SERVER_NAME"]
				);

				if($arParams["TYPE"] === "POST")
				{
					if (!$bTitleEmpty)
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_1_MSGVER_1".$aditGM,
							Array("#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>"),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_1_MSGVER_1".$aditGM,
							Array("#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])),
							$languageId
						)." (".$serverName.$url.")";
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_1A".$aditGM,
							Array("#post#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_1B", null, $languageId)."</a>"),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_1A".$aditGM,
							Array("#post#" => Loc::getMessage("BLG_GP_IM_1B", null, $languageId)),
							$languageId
						)." (".$serverName.$url.")";
					}
				}
				elseif($arParams["TYPE"] === "COMMENT")
				{
					$commentCropped = truncateText($arParams["BODY"], 100);

					if (!$bTitleEmpty)
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_41".$aditGM, array(
							"#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>",
							"#comment#" => $commentCropped
						), $languageId);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_41".$aditGM, array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"]),
								"#comment#" => $arParams["BODY"]
							), $languageId)." ".$serverName.$url;
						$arMessageFields["NOTIFY_MESSAGE_AUTHOR"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_51".$aditGM, array(
							"#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>",
							"#comment#" => $commentCropped
						), $languageId);
						$arMessageFields["NOTIFY_MESSAGE_AUTHOR_OUT"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_51".$aditGM, array(
								"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"]),
								"#comment#" => $arParams["BODY"]
							), $languageId)." ".$serverName.$url;
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_41A".$aditGM, array(
							"#post#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_4B", null, $languageId)."</a>",
							"#comment#" => $commentCropped
						), $languageId);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_41A".$aditGM, array(
								"#post#" => Loc::getMessage("BLG_GP_IM_4B", null, $languageId),
								"#comment#" => $arParams["BODY"]
							), $languageId)." ".$serverName.$url;
						$arMessageFields["NOTIFY_MESSAGE_AUTHOR"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_51A".$aditGM, array(
							"#post#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_5B", null, $languageId)."</a>",
							"#comment#" => $commentCropped
						), $languageId);
						$arMessageFields["NOTIFY_MESSAGE_AUTHOR_OUT"] = fn (?string $languageId = null) => Loc::getMessage("BLG_GP_IM_51A".$aditGM, array(
								"#post#" => Loc::getMessage("BLG_GP_IM_5B", null, $languageId),
								"#comment#" => $arParams["BODY"]
							), $languageId)." ".$serverName.$url;
					}
				}
				elseif($arParams["TYPE"] === "SHARE")
				{
					if (!$bTitleEmpty)
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_8".$aditGM,
							Array("#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>"),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_8".$aditGM,
							Array("#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])),
							$languageId
						)." ".$serverName.$url."";
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_8A".$aditGM,
							Array("#post#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".Loc::getMessage("BLG_GP_IM_8B", null, $languageId)."</a>"),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_8A".$aditGM,
							Array("#post#" => Loc::getMessage("BLG_GP_IM_8B", null, $languageId)),
							$languageId
						)." ".$serverName.$url."";
					}
				}
				elseif($arParams["TYPE"] === "SHARE2USERS")
				{
					if (!$bTitleEmpty)
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_9".$aditGM,
							Array("#title#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">".htmlspecialcharsbx($arParams["TITLE"])."</a>"),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_9".$aditGM,
							Array("#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])),
							$languageId
						)." ".$serverName.$url."";
					}
					else
					{
						$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_9A".$aditGM,
							Array("#post#" => "<a href=\"".$url."\" class=\"bx-notifier-item-action\">"
								.Loc::getMessage(
									"BLG_GP_IM_9B",
									null,
									$languageId
								)
								."</a>"
							),
							$languageId
						);
						$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
							"BLG_GP_IM_9A".$aditGM,
							Array("#post#" => Loc::getMessage("BLG_GP_IM_9B", null, $languageId)),
							$languageId
						)." ".$serverName.$url."";
					}
				}
			}

			$arMessageFieldsTmp = $arMessageFields;
			if($arParams["TYPE"] === "COMMENT")
			{
				if($arParams["AUTHOR_ID"] == $v)
				{
					$arMessageFieldsTmp["NOTIFY_MESSAGE"] = $arMessageFields["NOTIFY_MESSAGE_AUTHOR"];
					$arMessageFieldsTmp["NOTIFY_MESSAGE_OUT"] = $arMessageFields["NOTIFY_MESSAGE_AUTHOR_OUT"];
					$arMessageFieldsTmp["PUSH_MESSAGE"] = $arMessageFields["PUSH_MESSAGE_AUTHOR"];
				}
			}

			$arMessageFieldsTmp["TO_USER_ID"] = $v;
			if ($notifySubTag)
			{
				$arMessageFieldsTmp["NOTIFY_SUB_TAG"] = $notifySubTag."|".$v;
			}

			CIMNotify::Add($arMessageFieldsTmp);

			$arUserIDSent[] = $v;
		}

		// notify sonet groups subscribers
		if (
			$arParams["TYPE"] === "POST"
			&& !empty($arParams["TO_SOCNET_RIGHTS"])
		)
		{
			$arGroupsId = array();
			foreach($arParams["TO_SOCNET_RIGHTS"] as $perm_tmp)
			{
				if (
					preg_match('/^SG(\d+)_'.SONET_ROLES_USER.'$/', $perm_tmp, $matches)
					|| preg_match('/^SG(\d+)$/', $perm_tmp, $matches)
				)
				{
					$group_id_tmp = $matches[1];
					if (
						$group_id_tmp > 0
						&& (
							!array_key_exists("SG", $arParams["TO_SOCNET_RIGHTS_OLD"])
							|| empty($arParams["TO_SOCNET_RIGHTS_OLD"]["SG"][$group_id_tmp])
						)
					)
					{
						$arGroupsId[] = $group_id_tmp;
					}
				}
			}

			if (!empty($arGroupsId))
			{
				$arTitle = self::processNotifyTitle($arParams["TITLE"]);
				$title = $arTitle['TITLE'];
				$title_out = $arTitle['TITLE_OUT'];

				$arNotifyParams = array(
					"LOG_ID" => $arMessageFields["LOG_ID"],
					"GROUP_ID" => $arGroupsId,
					"NOTIFY_MESSAGE" => "",
					"FROM_USER_ID" => $arParams["FROM_USER_ID"],
					"URL" => $arParams["URL"],
					"MESSAGE" => fn (?string $languageId = null) => Loc::getMessage(
						"SONET_IM_NEW_POST",
						Array("#title#" => "[URL=#URL#]".$title."[/URL]"),
						$languageId
					),
					"MESSAGE_OUT" => fn (?string $languageId = null) => Loc::getMessage(
						"SONET_IM_NEW_POST",
						Array("#title#" => $title_out),
						$languageId
					)." #URL#",
					"MESSAGE_CHAT" => GetMessage("SONET_IM_NEW_POST_CHAT".$aditGM, Array(
						"#title#" => "[URL=#URL#]".$title_out."[/URL]",
					)),
					"EXCLUDE_USERS" => array_merge([$arParams["FROM_USER_ID"]], $arUserIDSent),
					"PERMISSION" => array(
						"FEATURE" => "blog",
						"OPERATION" => "view_post"
					)
				);

				$arUserIDSentBySubscription = CSocNetSubscription::NotifyGroup($arNotifyParams);
				if (!$arUserIDSentBySubscription)
				{
					$arUserIDSentBySubscription = array();
				}
				$arUserIDSent = array_merge($arUserIDSent, $arUserIDSentBySubscription);
			}
		}

		if (
			!empty($arParams['GRAT_DATA'])
			&& is_array($arParams['GRAT_DATA'])
			&& !empty($arParams['GRAT_DATA']['USERS'])
			&& is_array($arParams['GRAT_DATA']['USERS'])
		)
		{
			$arMessageFieldsGrat = $arMessageFields;
			$arMessageFieldsGrat["NOTIFY_EVENT"] = 'grat';
			$arMessageFieldsGrat["NOTIFY_TAG"] = "BLOG|POST|".$arParams["ID"];
			$arMessageFieldsGrat["PUSH_PARAMS"] = [
				"ACTION" => "post",
				"TAG" => $arMessageFieldsGrat["NOTIFY_TAG"]
			];
			if (!empty($authorAvatarUrl))
			{
				$arMessageFieldsGrat["PUSH_PARAMS"]["ADVANCED_PARAMS"] = array(
					'avatarUrl' => $authorAvatarUrl,
					'senderName' => $authorName
				);
			}

			$arMessageFieldsGrat["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage('SONET_IM_POST_GRAT_NEW', [
				"#link_post_start#" => "<a href=\"".$urlOriginal."\" class=\"bx-notifier-item-action\">",
				"#link_post_end#" => "</a>",
				"#title#" => htmlspecialcharsbx($arParams["TITLE"])
			], $languageId);

			$arMessageFieldsGrat["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage('SONET_IM_POST_GRAT_NEW', [
				"#link_post_start#" => "",
				"#link_post_end#" => "",
				"#title#" => htmlspecialcharsbx($arParams["TITLE"])
			], $languageId)." ".$serverName.$urlOriginal."";
			$arMessageFieldsGrat["PUSH_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage('SONET_PUSH_POST_GRAT_NEW', [
				"#name#" => htmlspecialcharsbx($authorName),
				"#title#" => htmlspecialcharsbx($arParams["TITLE"])
			], $languageId);

			foreach($arParams['GRAT_DATA']['USERS'] as $gratUserId)
			{
				if (
					in_array($gratUserId, $arUserIDSent)
					|| $arParams["FROM_USER_ID"] == $gratUserId
				)
				{
					continue;
				}

				$postPerm = CBlogPost::GetSocNetPostPerms(array(
					"POST_ID" => $arParams["ID"],
					"NEED_FULL" => true,
					"USER_ID" => $gratUserId,
					"IGNORE_ADMIN" => true
				));

				if ($postPerm < BLOG_PERMS_READ)
				{
					continue;
				}

				$arMessageFieldsTmp = $arMessageFieldsGrat;
				$arMessageFieldsTmp['TO_USER_ID'] = $gratUserId;
				$arMessageFieldsTmp['NOTIFY_SUB_TAG'] = "BLOG|POST|".$arParams["ID"]."|".$gratUserId;

				CIMNotify::Add($arMessageFieldsTmp);
				$arUserIDSent[] = $gratUserId;
			}
		}

		return $arUserIDSent;
	}

	public static function NotifyImReady($arParams = array())
	{
		$arUserIDSent = array();
		$moderatorList = array();

		if (
			!Loader::includeModule("im")
			|| !Loader::includeModule("socialnetwork")
		)
		{
			return $arUserIDSent;
		}

		if (!in_array($arParams['TYPE'], array('POST', 'COMMENT')))
		{
			return $arUserIDSent;
		}

		if (
			isset($arParams["TO_SOCNET_RIGHTS"])
			&& is_array($arParams["TO_SOCNET_RIGHTS"])
			&& !empty($arParams["TO_SOCNET_RIGHTS"])
		)
		{
			$arGroupChecked = array();
			foreach($arParams["TO_SOCNET_RIGHTS"] as $code)
			{

				if (preg_match('/^SG(\d+)/', $code, $matches))
				{
					$sonetGroupId = intval($matches[1]);

					if (in_array($sonetGroupId, $arGroupChecked))
					{
						break;
					}

					$arGroupChecked[] = $sonetGroupId;

					if ($sonetGroupId > 0)
					{
						$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(
							SONET_ENTITY_GROUP,
							$sonetGroupId,
							'blog',
							($arParams['TYPE'] === 'POST' ? 'moderate_post' : 'moderate_comment')
						);

						if ($featureOperationPerms)
						{
							$res = \Bitrix\Socialnetwork\UserToGroupTable::getList(array(
								'filter' => array(
									'<=ROLE' => $featureOperationPerms,
									'GROUP_ID' => $sonetGroupId,
									'=GROUP.ACTIVE' => 'Y'
								),
								'select' => array('USER_ID')
							));
							while ($relation = $res->fetch())
							{
								if (!isset($moderatorList[$relation['USER_ID']]))
								{
									$moderatorList[$relation['USER_ID']] = array(
										'USER_ID' => $relation['USER_ID'],
										'GROUP_ID' => $sonetGroupId
									);
								}
							}
						}
					}
				}
			}
		}

		if (!empty($moderatorList))
		{
			$arMessageFields = array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
				"NOTIFY_MODULE" => "blog",
			);

			$arTitle = self::processNotifyTitle($arParams["TITLE"]);
			$arParams["TITLE"] = $arTitle['TITLE'];
			$arParams["TITLE_OUT"] = $arTitle['TITLE_OUT'];
			$bTitleEmpty = $arTitle['IS_TITLE_EMPTY'];
			$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
			$moderationUrl = \Bitrix\Main\Config\Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'workgroups/').'group/#group_id#/blog/moderation/';

			if ($arParams["TYPE"] === "POST")
			{
				$arMessageFields["NOTIFY_EVENT"] = "moderate_post";
				$arMessageFields["NOTIFY_TAG"] = "BLOG|MODERATE_POST|".$arParams["POST_ID"];

				$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
					(!$bTitleEmpty ? "SONET_IM_NEW_POST_TO_MODERATE_W_TITLE" : "SONET_IM_NEW_POST_TO_MODERATE_WO_TITLE"),
					array(
						"#link_mod_start#" => "<a href=\"#MODERATION_URL#\" class=\"bx-notifier-item-action\">",
						"#link_mod_end#" => "</a>",
						"#title#" => htmlspecialcharsbx($arParams["TITLE"])
					),
					$languageId
				);

				$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
					(!$bTitleEmpty ? "SONET_IM_NEW_POST_TO_MODERATE_W_TITLE" : "SONET_IM_NEW_POST_TO_MODERATE_WO_TITLE"),
					array(
						"#link_mod_start#" => "",
						"#link_mod_end#" => "",
						"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
					),
					$languageId
				)." #SERVER_NAME##MODERATION_URL#";
			}
			else
			{
				$arMessageFields["NOTIFY_EVENT"] = "moderate_comment";
				$arMessageFields["NOTIFY_TAG"] = "BLOG|COMMENT|".$arParams["POST_ID"].'|'.$arParams["COMMENT_ID"];

				$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
					(!$bTitleEmpty ? "SONET_IM_NEW_COMMENT_TO_MODERATE_W_TITLE" : "SONET_IM_NEW_COMMENT_TO_MODERATE_WO_TITLE"),
					array(
						"#link_com_start#" => "<a href=\"#COMMENT_URL#\" class=\"bx-notifier-item-action\">",
						"#link_com_end#" => "</a>",
						"#title#" => htmlspecialcharsbx($arParams["TITLE"])
					),
					$languageId
				);

				$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
						(!$bTitleEmpty ? "SONET_IM_NEW_COMMENT_TO_MODERATE_W_TITLE" : "SONET_IM_NEW_COMMENT_TO_MODERATE_WO_TITLE"),
						array(
							"#link_com_start#" => "",
							"#link_com_end#" => "",
							"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
						),
						$languageId
					)." #SERVER_NAME##COMMENT_URL#";
			}

			foreach($moderatorList as $moderator)
			{
				$moderatorId = $moderator['USER_ID'];
				$groupId = $moderator['GROUP_ID'];

				if ($moderatorId != $arParams["FROM_USER_ID"])
				{
					$arMessageFieldsCurrent = $arMessageFields;
					$arMessageFieldsCurrent["TO_USER_ID"] = $moderatorId;

					$userModerationUrl = str_replace('#group_id#', $groupId, $moderationUrl);
					$userCommentUrl = $arParams['COMMENT_URL'] ?? null;

					if (IsModuleInstalled("extranet"))
					{
						$arTmp = CSocNetLogTools::ProcessPath(
							array(
								"MODERATION_URL" => $userModerationUrl,
								"COMMENT_URL" => (isset($arParams['COMMENT_URL']) ? $arParams['COMMENT_URL'] : '')
							),
							$moderatorId,
							SITE_ID
						);

						$userModerationUrl = $arTmp["URLS"]["MODERATION_URL"];
						$userCommentUrl = $arTmp["URLS"]["COMMENT_URL"];

						$serverName = (
						mb_strpos($userModerationUrl, "http://") === 0
							|| mb_strpos($userModerationUrl, "https://") === 0
								? ""
								: $arTmp["SERVER_NAME"]
						);
					}

					$notifyMessage = clone $arMessageFields["NOTIFY_MESSAGE"];
					$notifyMessageOut = clone $arMessageFields["NOTIFY_MESSAGE_OUT"];

					$arMessageFieldsCurrent["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => str_replace(
						array('#MODERATION_URL#', '#COMMENT_URL#'),
						array($userModerationUrl, $userCommentUrl),
						$notifyMessage($languageId)
					);
					$arMessageFieldsCurrent["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => str_replace(
						array('#MODERATION_URL#', '#SERVER_NAME#', '#COMMENT_URL#'),
						array($userModerationUrl, $serverName, $userCommentUrl),
						$notifyMessageOut($languageId)
					);

					CIMNotify::Add($arMessageFieldsCurrent);

					$arUserIDSent[] = $moderatorId;
				}
			}
		}

		return $arUserIDSent;
	}

	public static function NotifyImPublish($arParams = array())
	{
		if (
			!Loader::includeModule("im")
			|| !Loader::includeModule("socialnetwork")
		)
		{
			return false;
		}

		if (!in_array($arParams['TYPE'], array('POST', 'COMMENT')))
		{
			return false;
		}

		$arTitle = self::processNotifyTitle($arParams["TITLE"]);
		$arParams["TITLE"] = $arTitle['TITLE'];
		$arParams["TITLE_OUT"] = $arTitle['TITLE_OUT'];
		$bTitleEmpty = $arTitle['IS_TITLE_EMPTY'];
		$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));

		$arMessageFields = array(
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
			"NOTIFY_MODULE" => "blog",
			"TO_USER_ID" => $arParams["TO_USER_ID"]
		);

		if ($arParams["TYPE"] === "POST")
		{
			$arMessageFields["NOTIFY_EVENT"] = "published_post";
			$arMessageFields["NOTIFY_TAG"] = "BLOG|POST|".$arParams["POST_ID"];

			$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
				(!$bTitleEmpty ? "SONET_IM_NEW_POST_PUBLISHED_W_TITLE" : "SONET_IM_NEW_POST_PUBLISHED_WO_TITLE"),
				array(
					"#link_post_start#" => "<a href=\"#POST_URL#\" class=\"bx-notifier-item-action\">",
					"#link_post_end#" => "</a>",
					"#title#" => htmlspecialcharsbx($arParams["TITLE"])
				),
				$languageId
			);

			$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
					(!$bTitleEmpty ? "SONET_IM_NEW_POST_PUBLISHED_W_TITLE" : "SONET_IM_NEW_POST_PUBLISHED_WO_TITLE"),
					array(
						"#link_post_start#" => "",
						"#link_post_end#" => "",
						"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
					),
					$languageId
				)." #SERVER_NAME##POST_URL#";
		}
		else
		{
			$arMessageFields["NOTIFY_EVENT"] = "published_comment";
			$arMessageFields["NOTIFY_TAG"] = "BLOG|COMMENT|".$arParams["POST_ID"]."|".$arParams["COMMENT_ID"];

			$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => Loc::getMessage(
				(!$bTitleEmpty ? "SONET_IM_NEW_COMMENT_PUBLISHED_W_TITLE" : "SONET_IM_NEW_COMMENT_PUBLISHED_WO_TITLE"),
				array(
					"#link_com_start#" => "<a href=\"#COMMENT_URL#\" class=\"bx-notifier-item-action\">",
					"#link_com_end#" => "</a>",
					"#title#" => htmlspecialcharsbx($arParams["TITLE"])
				),
				$languageId
			);

			$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => Loc::getMessage(
					(!$bTitleEmpty ? "SONET_IM_NEW_COMMENT_PUBLISHED_W_TITLE" : "SONET_IM_NEW_COMMENT_PUBLISHED_WO_TITLE"),
					array(
						"#link_com_start#" => "",
						"#link_com_end#" => "",
						"#title#" => htmlspecialcharsbx($arParams["TITLE_OUT"])
					),
					$languageId
				)." #SERVER_NAME##COMMENT_URL#";
		}

		$userPostUrl = (isset($arParams['POST_URL']) ? $arParams['POST_URL'] : '');
		$userCommentUrl = (isset($arParams['COMMENT_URL']) ? $arParams['COMMENT_URL'] : '');

		if (IsModuleInstalled("extranet"))
		{
			$arTmp = CSocNetLogTools::ProcessPath(
				array(
					"POST_URL" => $userPostUrl,
					"COMMENT_URL" => $userCommentUrl
				),
				$arParams["TO_USER_ID"],
				SITE_ID
			);

			$userPostUrl = $arTmp["URLS"]["POST_URL"];
			$userCommentUrl = $arTmp["URLS"]["COMMENT_URL"];

			$serverName = (
			mb_strpos($userPostUrl, "http://") === 0
				|| mb_strpos($userPostUrl, "https://") === 0
					? ""
					: $arTmp["SERVER_NAME"]
			);
		}

		$notifyMessage = clone $arMessageFields["NOTIFY_MESSAGE"];
		$notifyMessageOut = clone $arMessageFields["NOTIFY_MESSAGE_OUT"];

		$arMessageFields["NOTIFY_MESSAGE"] = fn (?string $languageId = null) => str_replace(
			array('#POST_URL#', '#COMMENT_URL#'),
			array($userPostUrl, $userCommentUrl),
			$notifyMessage($languageId)
		);
		$arMessageFields["NOTIFY_MESSAGE_OUT"] = fn (?string $languageId = null) => str_replace(
			array('#POST_URL#', '#SERVER_NAME#', '#COMMENT_URL#'),
			array($userPostUrl, $serverName, $userCommentUrl),
			$notifyMessageOut($languageId)
		);

		CIMNotify::Add($arMessageFields);

		return true;
	}


	private static function processNotifyTitle($title)
	{
		$title = htmlspecialcharsback(str_replace(array("\r\n", "\n"), " ", $title));

		return array(
			'TITLE' => htmlspecialcharsEx(truncateText($title, 100)),
			'TITLE_OUT' => htmlspecialcharsEx(truncateText($title, 255)),
			'IS_TITLE_EMPTY' => (trim($title, " \t\n\r\0\x0B\xA0" ) == '')
		);
	}

	public static function NotifyMail($arFields)
	{
		if (!CModule::IncludeModule('mail'))
		{
			return false;
		}

		if (
			!isset($arFields["postId"])
			|| intval($arFields["postId"]) <= 0
			|| !isset($arFields["userId"])
			|| !isset($arFields["postUrl"])
			|| $arFields["postUrl"] == ''
		)
		{
			return false;
		}

		if (!is_array($arFields["userId"]))
		{
			$arFields["userId"] = array($arFields["userId"]);
		}

		if (!isset($arFields["siteId"]))
		{
			$arFields["siteId"] = SITE_ID;
		}

		$nameTemplate = CSite::GetNameFormat("", $arFields["siteId"]);
		$authorName = "";

		if (!empty($arFields["authorId"]))
		{
			$rsAuthor = CUser::GetById($arFields["authorId"]);
			$arAuthor = $rsAuthor->Fetch();
			$authorName = CUser::FormatName(
				$nameTemplate,
				$arAuthor,
				true,
				false
			);

			if (check_email($authorName))
			{
				$authorName = '"'.$authorName.'"';
			}

			foreach($arFields["userId"] as $key => $val)
			{
				if (intval($val) == intval($arFields["authorId"]))
				{
					unset($arFields["userId"][$key]);
				}
			}
		}

		if (empty($arFields["userId"]))
		{
			return false;
		}

		if (
			!isset($arFields["type"])
			|| !in_array(mb_strtoupper($arFields["type"]), array("POST", "POST_SHARE", "COMMENT"))
		)
		{
			$arFields["type"] = "COMMENT";
		}

		$arEmail = \Bitrix\Mail\User::getUserData($arFields["userId"], $nameTemplate);
		if (empty($arEmail))
		{
			return false;
		}

		$arBlogPost = CBlogPost::GetByID(intval($arFields["postId"]));
		if (!$arBlogPost)
		{
			return false;
		}

		$arTitle = self::processNotifyTitle($arBlogPost["TITLE"]);
		$postTitle = $arTitle['TITLE'];

		switch(mb_strtoupper($arFields["type"]))
		{
			case "COMMENT":
				$mailMessageId = "<BLOG_COMMENT_".$arFields["commentId"]."@".$GLOBALS["SERVER_NAME"].">";
				$mailTemplateType = "BLOG_SONET_NEW_COMMENT";
				break;
			case "POST_SHARE":
				$mailMessageId = "<BLOG_POST_".$arFields["postId"]."@".$GLOBALS["SERVER_NAME"].">";
				$mailTemplateType = "BLOG_SONET_POST_SHARE";
				break;
			default:
				$mailMessageId = "<BLOG_POST_".$arFields["postId"]."@".$GLOBALS["SERVER_NAME"].">";
				$mailTemplateType = "BLOG_SONET_NEW_POST";
		}

		$mailMessageInReplyTo = "<BLOG_POST_".$arFields["postId"]."@".$GLOBALS["SERVER_NAME"].">";
		$defaultEmailFrom = \Bitrix\Mail\User::getDefaultEmailFrom();

		foreach ($arEmail as $userId => $arUser)
		{
			$email = $arUser["EMAIL"];
			$nameFormatted = str_replace(array('<', '>', '"'), '', $arUser["NAME_FORMATTED"]);

			if (
				intval($userId) <= 0
				&& $email == ''
			)
			{
				continue;
			}

			$res = \Bitrix\Mail\User::getReplyTo(
				$arFields["siteId"],
				$userId,
				'BLOG_POST',
				$arFields["postId"],
				$arFields["postUrl"]
			);
			if (is_array($res))
			{
				list($replyTo, $backUrl) = $res;

				if (
					$replyTo
					&& $backUrl
				)
				{
					$authorName = str_replace(array('<', '>', '"'), '', $authorName);
					CEvent::Send(
						$mailTemplateType,
						$arFields["siteId"],
						array(
							"=Reply-To" => $authorName.' <'.$replyTo.'>',
							"=Message-Id" => $mailMessageId,
							"=In-Reply-To" => $mailMessageInReplyTo == $mailMessageId ? '' : $mailMessageInReplyTo,
							"EMAIL_FROM" => $authorName.' <'.$defaultEmailFrom.'>',
							"EMAIL_TO" => (!empty($nameFormatted) ? ''.$nameFormatted.' <'.$email.'>' : $email),
							"RECIPIENT_ID" => $userId,
							"COMMENT_ID" => (isset($arFields["commentId"]) ? intval($arFields["commentId"]) : false),
							"POST_ID" => intval($arFields["postId"]),
							"POST_TITLE" => $postTitle,
							"URL" => $arFields["postUrl"]
						)
					);
				}
			}
		}

		if (
			mb_strtoupper($arFields["type"]) == 'COMMENT'
			&& Loader::includeModule('crm')
		)
		{
			CCrmLiveFeedComponent::processCrmBlogComment(array(
				"AUTHOR" => isset($arAuthor) ? $arAuthor : false,
				"POST_ID" => intval($arFields["postId"]),
				"COMMENT_ID" => intval($arFields["commentId"]),
				"USER_ID" => array_keys($arEmail)
			));
		}

		return true;
	}

	public static function DeleteSocNetPostPerms($postId)
	{
		global $DB;
		$postId = intval($postId);
		if($postId <= 0)
			return;

		$DB->Query("DELETE FROM b_blog_socnet_rights WHERE POST_ID = ".$postId);
	}

	public static function GetMentionedUserID($arFields)
	{
		global $USER_FIELD_MANAGER;
		$arMentionedUserID = array();

		if (isset($arFields["DETAIL_TEXT"]))
		{
			preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/isu", $arFields["DETAIL_TEXT"], $arMention);
			if (!empty($arMention))
			{
				$arMentionedUserID = array_merge($arMentionedUserID, $arMention[1]);
			}
		}

		$arPostUF = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST", $arFields["ID"], LANGUAGE_ID);

		if (
			is_array($arPostUF)
			&& isset($arPostUF["UF_GRATITUDE"])
			&& is_array($arPostUF["UF_GRATITUDE"])
			&& isset($arPostUF["UF_GRATITUDE"]["VALUE"])
			&& intval($arPostUF["UF_GRATITUDE"]["VALUE"]) > 0
			&& CModule::IncludeModule("iblock")
		)
		{
			if (
				!is_array($GLOBALS["CACHE_HONOUR"])
				|| !array_key_exists("honour_iblock_id", $GLOBALS["CACHE_HONOUR"])
				|| intval($GLOBALS["CACHE_HONOUR"]["honour_iblock_id"]) <= 0
			)
			{
				$rsIBlock = CIBlock::GetList(array(), array("=CODE" => "honour", "=TYPE" => "structure"));
				if ($arIBlock = $rsIBlock->Fetch())
				{
					$GLOBALS["CACHE_HONOUR"]["honour_iblock_id"] = $arIBlock["ID"];
				}
			}

			if (intval($GLOBALS["CACHE_HONOUR"]["honour_iblock_id"]) > 0)
			{
				$rsElementProperty = CIBlockElement::GetProperty(
					$GLOBALS["CACHE_HONOUR"]["honour_iblock_id"],
					$arPostUF["UF_GRATITUDE"]["VALUE"]
				);
				while ($arElementProperty = $rsElementProperty->GetNext())
				{
					if (
						$arElementProperty["CODE"] == "USERS"
						&& intval($arElementProperty["VALUE"]) > 0
					)
					{
						$arMentionedUserID[] = $arElementProperty["VALUE"];
					}
				}
			}
		}

		return $arMentionedUserID;
	}

}
