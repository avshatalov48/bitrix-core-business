<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

IncludeModuleLangFile(__FILE__);
$GLOBALS["BLOG_USER"] = Array();

use Bitrix\Blog\BlogUser;

class CAllBlogUser
{
	public static function IsLocked($userID)
	{
		$userID = intval($userID);
		if ($userID > 0)
		{
			$arUser = CBlogUser::GetByID($userID, BLOG_BY_USER_ID);
			if ($arUser)
			{
				if ($arUser["ALLOW_POST"] != "Y")
					return True;
			}
		}
		return False;
	}

	public static function CanUserUpdateUser($ID, $userID, $selectType = BLOG_BY_BLOG_USER_ID)
	{
		$ID = intval($ID);
		$userID = intval($userID);
		$selectType = (($selectType == BLOG_BY_USER_ID) ? BLOG_BY_USER_ID : BLOG_BY_BLOG_USER_ID);

		$blogModulePermissions = $GLOBALS["APPLICATION"]->GetGroupRight("blog");
		if ($blogModulePermissions >= "W")
			return True;

		$arUser = CBlogUser::GetByID($ID, $selectType);
		if ($arUser && intval($arUser["USER_ID"]) == $userID)
			return True;

		return False;
	}

	/*************** ADD, UPDATE, DELETE *****************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB;

		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_EMPTY_USER_ID"), "EMPTY_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "ALIAS") && $arFields["ALIAS"] <> '')
		{
			$dbResult = CBlogUser::GetList(array(), array("ALIAS" => $arFields["ALIAS"], "!ID" => intval($ID)), false, false, array("ID"));
			if ($dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_DUPL_ALIAS"), "ERROR_DUPL_ALIAS");
				return false;
			}
		}

		if (is_set($arFields, "LAST_VISIT") && (!$DB->IsDate($arFields["LAST_VISIT"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_LAST_VISIT"), "ERROR_LAST_VISIT");
			return false;
		}

		if (is_set($arFields, "DATE_REG") && (!$DB->IsDate($arFields["DATE_REG"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_DATE_REG"), "ERROR_DATE_REG");
			return false;
		}

		if ((is_set($arFields, "ALLOW_POST") || $ACTION=="ADD") && $arFields["ALLOW_POST"] != "Y" && $arFields["ALLOW_POST"] != "N")
			$arFields["ALLOW_POST"] = "Y";

		if (is_set($arFields, "AVATAR") && $arFields["AVATAR"]["name"] == '' && $arFields["AVATAR"]["del"] == '')
			unset($arFields["AVATAR"]);

		if (is_set($arFields, "AVATAR"))
		{
			$max_size = Option::get('blog', 'avatar_max_size', 30000);
			$res = CFile::CheckImageFile($arFields["AVATAR"], $max_size, 0, 0);
			if ($res <> '')
			{
				$GLOBALS["APPLICATION"]->ThrowException($res, "ERROR_AVATAR");
				return false;
			}
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		$bSuccess = True;

		$arUser = CBlogUser::GetByID($ID, BLOG_BY_USER_ID);
		if ($arUser)
		{

			$dbResult = CBlog::GetList(array(), array("OWNER_ID" => $arUser["USER_ID"]), false, false, array("ID"));
			if ($dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_OWNER"), "ERROR_OWNER");
				$bSuccess = False;
			}

			if ($bSuccess)
			{
				$dbResult = CBlogPost::GetList(array(), array("AUTHOR_ID" => $arUser["USER_ID"]), false, false, array("ID"));
				if ($arResult = $dbResult->Fetch())
				{
					if(!CBlogPost::Delete($arResult["ID"]))
					{
						$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_AUTHOR"), "ERROR_AUTHOR");
						$bSuccess = False;
					}
				}
			}

			if ($bSuccess)
			{
				$dbGloUser = CUser::GetByID($arUser["USER_ID"]);
				$arGloUser = $dbGloUser->Fetch();

				$DB->Query(
					"UPDATE b_blog_comment SET ".
					"	AUTHOR_NAME = '".$DB->ForSql(CBlogUser::GetUserName($arUser["ALIAS"], $arGloUser["NAME"], $arGloUser["LAST_NAME"], $arGloUser["LOGIN"], $arGloUser["SECOND_NAME"]))."', ".
					"	AUTHOR_ID = null ".
					"WHERE AUTHOR_ID = ".$arUser["USER_ID"]."",
					true
				);

				$DB->Query("DELETE FROM b_blog_user2user_group WHERE USER_ID = ".$arUser["USER_ID"]."", true);
			}

			if ($bSuccess)
			{
				$strSql =
					"SELECT F.ID ".
					"FROM b_blog_user FU, b_file F ".
					"WHERE FU.ID = ".$arUser["ID"]." ".
					"	AND FU.AVATAR = F.ID ";
				$z = $DB->Query($strSql);
				while ($zr = $z->Fetch())
					CFile::Delete($zr["ID"]);


				if (CModule::IncludeModule("search"))
				{
					CSearch::Index("blog", "U".$arUser["ID"],
						array(
							"TITLE" => "",
							"BODY" => ""
						)
					);
				}


				unset($GLOBALS["BLOG_USER"]["BLOG_USER_CACHE_".$arUser["ID"]]);
				unset($GLOBALS["BLOG_USER"]["BLOG_USER1_CACHE_".$arUser["USER_ID"]]);
				unset($GLOBALS["BLOG_USER"]["BLOG_USER2GROUP_CACHE_".$arUser["ID"]]);
				unset($GLOBALS["BLOG_USER"]["BLOG_USER2GROUP1_CACHE_".$arUser["USER_ID"]]);

				return $DB->Query("DELETE FROM b_blog_user WHERE ID = ".$arUser["ID"]."", true);
			}
			if(!$bSuccess)
				return false;
		}

		return True;
	}

	public static function DeleteFromUserGroup($ID, $blogID, $selectType = BLOG_BY_BLOG_USER_ID)
	{
		global $DB;

		$ID = intval($ID);
		$blogID = intval($blogID);
		$selectType = (($selectType == BLOG_BY_USER_ID) ? BLOG_BY_USER_ID : BLOG_BY_BLOG_USER_ID);

		$bSuccess = True;

		$arResult = CBlog::GetByID($blogID);
		if (!$arResult)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $blogID, GetMessage("BLG_GU_ERROR_NO_BLOG")), "ERROR_NO_BLOG");
			$bSuccess = False;
		}

		if ($bSuccess)
		{
			$arUser = CBlogUser::GetByID($ID, $selectType);

			$dbResult = CUser::GetByID($arUser["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				$bSuccess = False;
			}
		}

		if ($bSuccess)
		{
			$DB->Query(
				"DELETE FROM b_blog_user2user_group ".
				"WHERE USER_ID = ".intval($arUser["USER_ID"])." ".
				"	AND BLOG_ID = ".$blogID." "
			);
		}

		return $bSuccess;
	}

	public static function AddToUserGroup($ID, $blogID, $arGroups = array(), $joinStatus = "Y", $selectType = BLOG_BY_BLOG_USER_ID, $action = BLOG_CHANGE)
	{
		global $DB;

		$ID = intval($ID);
		$blogID = intval($blogID);
		if (!is_array($arGroups))
			$arGroups = array($arGroups);
		$joinStatus = (($joinStatus == "Y") ? "Y" : "N");
		$selectType = (($selectType == BLOG_BY_USER_ID) ? BLOG_BY_USER_ID : BLOG_BY_BLOG_USER_ID);
		$action = (($action == BLOG_ADD) ? BLOG_ADD : BLOG_CHANGE);

		$bSuccess = True;

		$arResult = CBlog::GetByID($blogID);
		if (!$arResult)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $blogID, GetMessage("BLG_GU_ERROR_NO_BLOG")), "ERROR_NO_BLOG");
			$bSuccess = False;
		}

		if ($bSuccess)
		{
			$arUser = CBlogUser::GetByID($ID, $selectType);

			$dbResult = CUser::GetByID($arUser["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GU_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				$bSuccess = False;
			}
		}

		if ($bSuccess)
		{
			if ($action == BLOG_CHANGE)
				$DB->Query(
					"DELETE FROM b_blog_user2user_group ".
					"WHERE USER_ID = ".intval($arUser["USER_ID"])." ".
					"	AND BLOG_ID = ".$blogID." "
				);

			if (count($arGroups) > 0)
			{
				array_walk(
					$arGroups,
					function (&$item) {
						$item = (int)$item;
					}
				);

				$dbUserGroups = CBlogUserGroup::GetList(
					array(),
					array("ID" => $arGroups, "BLOG_ID" => $blogID),
					false,
					false,
					array("ID")
				);
				$arGroups = array();
				while ($arUserGroup = $dbUserGroups->Fetch())
					$arGroups[] = intval($arUserGroup["ID"]);

				if ($action == BLOG_ADD)
					$arCurrentGroups = CBlogUser::GetUserGroups($ID, $blogID, "", $selectType);

				foreach($arGroups as $val)
				{
					if ($val != 1 && $val != 2)
					{
						if ($action == BLOG_CHANGE
							|| $action == BLOG_ADD && !in_array($val, $arCurrentGroups))
						{
							$DB->Query(
								"INSERT INTO b_blog_user2user_group (USER_ID, BLOG_ID, USER_GROUP_ID) ".
								"VALUES (".intval($arUser["USER_ID"]).", ".$blogID.", ".intval($val).")"
							);
						}
					}
				}
			}

			unset($GLOBALS["BLOG_USER"]["BLOG_USER2GROUP_CACHE_".$arUser["ID"]]);
			unset($GLOBALS["BLOG_USER"]["BLOG_USER2GROUP1_CACHE_".$arUser["USER_ID"]]);
		}

		return $bSuccess;
	}

	public static function SetLastVisit()
	{
		if (isset($GLOBALS["BLOG_USER"]["BLOG_LAST_VISIT_SET"]) && $GLOBALS["BLOG_USER"]["BLOG_LAST_VISIT_SET"] == "Y")
			return True;

		if (!$GLOBALS["USER"]->IsAuthorized())
			return False;

		$userID = intval($GLOBALS["USER"]->GetID());
		if ($userID <= 0)
			return False;

		$arBlogUser = CBlogUser::GetByID($userID, BLOG_BY_USER_ID);
		if ($arBlogUser)
			CBlogUser::Update(
				$arBlogUser["ID"],
				array("=LAST_VISIT" => $GLOBALS["DB"]->GetNowFunction())
			);
		else
			CBlogUser::Add(
				array(
					"USER_ID" => $userID,
					"=LAST_VISIT" => $GLOBALS["DB"]->GetNowFunction(),
					"=DATE_REG" => $GLOBALS["DB"]->GetNowFunction(),
					"ALLOW_POST" => "Y"
				)
			);

		$GLOBALS["BLOG_USER"]["BLOG_LAST_VISIT_SET"] = "Y";

		return True;
	}

	//*************** SELECT *********************/
	public static function GetByID($ID, $selectType = BLOG_BY_BLOG_USER_ID)
	{
		global $DB;

		$ID = intval($ID);
		$selectType = (($selectType == BLOG_BY_USER_ID) ? BLOG_BY_USER_ID : BLOG_BY_BLOG_USER_ID);

		$varName = (($selectType == BLOG_BY_USER_ID) ? "BLOG_USER1_CACHE_" : "BLOG_USER_CACHE_");
		if (isset($GLOBALS["BLOG_USER"][$varName.$ID]) && is_array($GLOBALS["BLOG_USER"][$varName.$ID]) && is_set($GLOBALS["BLOG_USER"][$varName.$ID], "ID"))
		{
			return $GLOBALS["BLOG_USER"][$varName.$ID];
		}
		else
		{
			$strSql =
				"SELECT B.ID, B.USER_ID, B.ALIAS, B.DESCRIPTION, B.AVATAR, B.INTERESTS, ".
				"	B.ALLOW_POST, ".
				"	".$DB->DateToCharFunction("B.LAST_VISIT", "FULL")." as LAST_VISIT, ".
				"	".$DB->DateToCharFunction("B.DATE_REG", "FULL")." as DATE_REG ".
				"FROM b_blog_user B ".
				"WHERE B.".(($selectType == BLOG_BY_USER_ID) ? "USER_ID" : "ID")." = ".$ID."";
			$dbResult = $DB->Query($strSql);
			if ($arResult = $dbResult->Fetch())
			{
				$GLOBALS["BLOG_USER"]["BLOG_USER_CACHE_".$arResult["ID"]] = $arResult;
				$GLOBALS["BLOG_USER"]["BLOG_USER1_CACHE_".$arResult["USER_ID"]] = $arResult;
				return $arResult;
			}
		}

		return False;
	}

	public static function GetUserFriends($ID, $bFlag = True)
	{
		global $DB;

		$ID = intval($ID);

		if ($bFlag)
		{
			$strSql =
				"SELECT B.ID, B.NAME, B.ACTIVE, B.URL, B.OWNER_ID ".
				"FROM b_blog_user2user_group U2UG ".
				"	INNER JOIN b_blog_user_group_perms UGP ".
				"		ON (U2UG.BLOG_ID = UGP.BLOG_ID AND U2UG.USER_GROUP_ID = UGP.USER_GROUP_ID) ".
				"	INNER JOIN b_blog B ".
				"		ON (U2UG.BLOG_ID = B.ID) ".
				"WHERE U2UG.USER_ID = ".$ID." ".
				//"	AND UGP.PERMS >= '".$DB->ForSql(BLOG_PERMS_WRITE)."' ".
				//"	AND UGP.PERMS_TYPE = '".$DB->ForSql(BLOG_PERMS_POST)."' ".
				"	AND UGP.POST_ID IS NULL ".
				"	AND B.ACTIVE = 'Y' ".
				"GROUP BY B.ID, B.NAME, B.ACTIVE, B.URL, B.OWNER_ID ".
				"ORDER BY B.NAME ASC";
		}
		else
		{
			$strSql =
				"SELECT B.ID, B.NAME, B.ACTIVE, B.URL ".
				"FROM b_blog B1 ".
				"	INNER JOIN b_blog_user_group_perms UGP ".
				"		ON (B1.ID = UGP.BLOG_ID) ".
				"	INNER JOIN b_blog_user2user_group U2UG ".
				"		ON (UGP.BLOG_ID = U2UG.BLOG_ID AND UGP.USER_GROUP_ID = U2UG.USER_GROUP_ID) ".
				"	INNER JOIN b_blog B ".
				"		ON (U2UG.USER_ID = B.OWNER_ID) ".
				"WHERE B1.OWNER_ID = ".$ID." ".
				//"	AND UGP.PERMS >= '".$DB->ForSql(BLOG_PERMS_WRITE)."' ".
				//"	AND UGP.PERMS_TYPE = '".$DB->ForSql(BLOG_PERMS_POST)."' ".
				"	AND UGP.POST_ID IS NULL ".
				"	AND B.ACTIVE = 'Y' ".
				"	AND B1.ACTIVE = 'Y' ".
				"GROUP BY B.ID, B.NAME, B.ACTIVE, B.URL ".
				"ORDER BY B.NAME ASC";
		}

		$dbResult = $DB->Query($strSql);

		return $dbResult;
	}

	public static function GetUserGroups($ID, $blogID, $joinStatus = "", $selectType = BLOG_BY_BLOG_USER_ID, $bUrl = false)
	{
		global $DB;

		$ID = intval($ID);
		$joinStatus = (($joinStatus == "Y" || $joinStatus == "N") ? $joinStatus : "");
		$selectType = (($selectType == BLOG_BY_USER_ID) ? BLOG_BY_USER_ID : BLOG_BY_BLOG_USER_ID);
		if($bUrl)
			$bUrl = true;
		else
			$bUrl = false;
		
		if(!$bUrl)
			$blogID = intval($blogID);
		else
			$blogID = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($blogID));

		$varName = (($selectType == BLOG_BY_USER_ID) ? "BLOG_USER2GROUP1_CACHE_".$blogID."_".$joinStatus."_".$ID."_".$bUrl : "BLOG_USER2GROUP_CACHE_".$blogID."_".$joinStatus."_".$ID."_".$bUrl);

		if (isset($GLOBALS["BLOG_USER"][$varName]) && is_array($GLOBALS["BLOG_USER"][$varName]))
		{
			return $GLOBALS["BLOG_USER"][$varName];
		}
		else
		{
			$arGroups = array(1);
			if (isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAuthorized())
				$arGroups[] = 2;

			if ($ID > 0 && $blogID <> '')
			{
				if($selectType == BLOG_BY_BLOG_USER_ID)
				{
					$arBlogUser = CBlogUser::GetByID($ID, $selectType);
					$userID = $arBlogUser["USER_ID"];
				}
				else
					$userID = $ID;
				
				$strSql =
					"SELECT UG.ID, UG.USER_ID, UG.BLOG_ID, UG.USER_GROUP_ID ".
					"FROM b_blog_user2user_group UG ";
				if($bUrl)
					$strSql .= " INNER JOIN b_blog B ON (UG.BLOG_ID = B.ID AND B.URL='".$DB->ForSql($blogID)."') ";
				
				$strSql .= " WHERE UG.USER_ID = ".$userID." ";
				
				if(!$bUrl)
					$strSql .= "	AND UG.BLOG_ID = ".$blogID." ";

				$dbResult = $DB->Query($strSql);

				while ($arResult = $dbResult->Fetch())
					$arGroups[] = intval($arResult["USER_GROUP_ID"]);
			}

			if($selectType == BLOG_BY_BLOG_USER_ID && !empty($arBlogUser))
				$GLOBALS["BLOG_USER"]["BLOG_USER2GROUP_CACHE_".$blogID."_".$joinStatus."_".intval($arBlogUser["ID"])."_".$bUrl] = $arGroups;
			$GLOBALS["BLOG_USER"]["BLOG_USER2GROUP1_CACHE_".$blogID."_".$joinStatus."_".intval($userID)."_".$bUrl] = $arGroups;
			return $arGroups;
		}

		return False;
	}

	public static function GetUserPerms($arGroups, $blogID, $postID = 0, $permsType = BLOG_PERMS_POST, $selectType = BLOG_BY_BLOG_USER_ID)
	{
		global $DB;

		$blogID = intval($blogID);
		$postID = intval($postID);
		$permsType = (($permsType == BLOG_PERMS_COMMENT) ? BLOG_PERMS_COMMENT : BLOG_PERMS_POST);
		$selectType = (($selectType == BLOG_BY_USER_ID) ? BLOG_BY_USER_ID : BLOG_BY_BLOG_USER_ID);

		if (!is_array($arGroups))
		{
			$ID = intval($arGroups);
			$arGroups = CBlogUser::GetUserGroups($ID, $blogID, "Y", $selectType);
		}

		$strGroups = "";
		foreach($arGroups as $val)
		{
			if ($strGroups <> '')
				$strGroups .= ",";

			$strGroups .= intval($val);
		}

		$varName = "BLOG_USER_PERMS_CACHE_".$blogID."_".$postID."_".$permsType;

		if (isset($GLOBALS["BLOG_USER"][$varName]) && is_array($GLOBALS["BLOG_USER"][$varName])
			&& isset($GLOBALS["BLOG_USER"][$varName][$strGroups]) && is_array($GLOBALS["BLOG_USER"][$varName][$strGroups]))
		{
			return $GLOBALS["BLOG_USER"][$varName][$strGroups];
		}
		else
		{
			if ($postID > 0)
			{
				$strSql =
					"SELECT MAX(P.PERMS) as PERMS ".
					"FROM b_blog_user_group_perms P ".
					"WHERE P.BLOG_ID = ".$blogID." ".
					"	AND P.USER_GROUP_ID IN (".$strGroups.") ".
					"	AND P.PERMS_TYPE = '".$DB->ForSql($permsType)."' ".
					"	AND P.POST_ID = ".$postID." ";
				$dbResult = $DB->Query($strSql);
				if (($arResult = $dbResult->Fetch()) && ($arResult["PERMS"] <> ''))
				{
					$GLOBALS["BLOG_USER"][$varName][$strGroups] = $arResult["PERMS"];
					return $arResult["PERMS"];
				}
			}

			$strSql =
				"SELECT MAX(P.PERMS) as PERMS ".
				"FROM b_blog_user_group_perms P ".
				"WHERE P.BLOG_ID = ".$blogID." ".
				"	AND P.USER_GROUP_ID IN (".$strGroups.") ".
				"	AND P.PERMS_TYPE = '".$DB->ForSql($permsType)."' ".
				"	AND P.POST_ID IS NULL ";
			$dbResult = $DB->Query($strSql);
			if (($arResult = $dbResult->Fetch()) && ($arResult["PERMS"] <> ''))
			{
				$GLOBALS[$varName][$strGroups] = $arResult["PERMS"];
				return $arResult["PERMS"];
			}

			return False;
		}
	}

	public static function GetUserName($alias, $name, $lastName, $login, $secondName = "")
	{
		return BlogUser::GetUserName($alias, $name, $lastName, $login, $secondName);
	}
	
	public static function GetUserNameEx($arUser, $arBlogUser, $arParams)
	{
		return BlogUser::GetUserNameEx($arUser, $arBlogUser, $arParams);
	}	

	public static function PreparePath($userID = 0, $siteID = False, $is404 = True)
	{
		$userID = intval($userID);
		if (!$siteID)
			$siteID = SITE_ID;

		$dbPath = CBlogSitePath::GetList(array(), array("SITE_ID"=>$siteID));
		while($arPath = $dbPath->Fetch())
		{
			if($arPath["TYPE"] <> '')
				$arPaths[$arPath["TYPE"]] = $arPath["PATH"];
			else
				$arPaths["OLD"] = $arPath["PATH"];
		}

		if($arPaths["U"] <> '')
		{
			$result = str_replace("#user_id#", $userID, $arPaths["U"]);
		}
		else
		{
			if($is404)
				$result = htmlspecialcharsbx($arPaths["OLD"])."/users/".$userID.".php";
			else
				$result = htmlspecialcharsbx($arPaths["OLD"])."/users.php?&user_id=".$userID;
		}

		return $result;
	}

	public static function PreparePath2User($arParams = array())
	{
		return CBlogUser::PreparePath(
			isset($arParams["USER_ID"]) ? $arParams["USER_ID"] : 0,
			False
		);
	}

	public static function GetUserIP()
	{
		if ($_SERVER["HTTP_X_FORWARDED_FOR"] ?? null)
		{
			$clientIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else
		{
			$clientIP = $_SERVER["HTTP_CLIENT_IP"] ?? null;
		}

		$clientProxy = $_SERVER["REMOTE_ADDR"];
		if (!$clientIP)
		{
			$clientIP = $clientProxy;
			$clientProxy = "";
		}

		return array($clientIP, $clientProxy);
	}

	public static function GetUserInfo($id, $path, $arParams = array())
	{
		if (!empty(CBlogPost::$arBlogUCache[$id]))
		{
			$arResult["arUser"] = CBlogPost::$arBlogUCache[$id];
		}
		else
		{
			if (intval($arParams["AVATAR_SIZE"] ?? null) <= 0)
				$arParams["AVATAR_SIZE"] = 100;

			if (intval($arParams["AVATAR_SIZE_COMMENT"] ?? null) <= 0)
				$arParams["AVATAR_SIZE_COMMENT"] = 100;

			$bResizeImmediate = (isset($arParams["RESIZE_IMMEDIATE"]) && $arParams["RESIZE_IMMEDIATE"] == "Y");

			$arSelect = Array(
				"FIELDS" => Array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "PERSONAL_GENDER", "EXTERNAL_AUTH_ID")
			);

			if (ModuleManager::isModuleInstalled('extranet'))
			{
				$arSelect["SELECT"] = array('UF_DEPARTMENT');
			}

			$dbUser = CUser::GetList(
				Array('ID' => 'desc'),
				'',
				Array("ID" => $id),
				$arSelect
			);
			if($arResult["arUser"] = $dbUser->GetNext())
			{
				if (
					intval($arResult["arUser"]["PERSONAL_PHOTO"]) <= 0
					&& ModuleManager::isModuleInstalled('socialnetwork')
				)
				{
					switch ($arResult["arUser"]["PERSONAL_GENDER"])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
							break;
						default:
							$suffix = "unknown";
					}
					$arResult["arUser"]["PERSONAL_PHOTO"] = Option::get('socialnetwork', 'default_user_picture_'.$suffix, false, SITE_ID);
				}

				if(intval($arResult["arUser"]["PERSONAL_PHOTO"]) > 0)
				{
					$arResult["arUser"]["PERSONAL_PHOTO_file"] = CFile::GetFileArray($arResult["arUser"]["PERSONAL_PHOTO"]);
					$arResult["arUser"]["PERSONAL_PHOTO_resized"] = CFile::ResizeImageGet(
						$arResult["arUser"]["PERSONAL_PHOTO_file"],
						array("width" => $arParams["AVATAR_SIZE"], "height" => $arParams["AVATAR_SIZE"]),
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						$bResizeImmediate
					);
					if ($arResult["arUser"]["PERSONAL_PHOTO_resized"] !== false)
						$arResult["arUser"]["PERSONAL_PHOTO_img"] = CFile::ShowImage($arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"], $arParams["AVATAR_SIZE"], $arParams["AVATAR_SIZE"], "border=0 align='right'");
					$arResult["arUser"]["PERSONAL_PHOTO_resized_30"] = CFile::ResizeImageGet(
						$arResult["arUser"]["PERSONAL_PHOTO_file"],
						array("width" => $arParams["AVATAR_SIZE_COMMENT"], "height" => $arParams["AVATAR_SIZE_COMMENT"]),
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						$bResizeImmediate
					);
					if ($arResult["arUser"]["PERSONAL_PHOTO_resized_30"] !== false)
						$arResult["arUser"]["PERSONAL_PHOTO_img_30"] = CFile::ShowImage($arResult["arUser"]["PERSONAL_PHOTO_resized_30"]["src"], $arParams["AVATAR_SIZE_COMMENT"], $arParams["AVATAR_SIZE_COMMENT"], "border=0 align='right'");
				}
				$arResult["arUser"]["url"] = CComponentEngine::MakePathFromTemplate($path, array("user_id" => $id));
			}
			CBlogPost::$arBlogUCache[$id] = $arResult["arUser"];
		}

		return $arResult["arUser"];
	}

	public static function GetUserInfoArray($arId, $path, $arParams = array())
	{
		if (
			!is_array($arId)
			&& intval($arId) > 0
		)
		{
			$arId = array(
				intval($arId)
			);
		}

		$arId = array_unique($arId);

		$arIdToGet = array();
		$arResult["arUser"] = array();

		foreach ($arId as $userId)
		{
			if (!empty(CBlogPost::$arBlogUCache[$userId]))
			{
				$arResult["arUser"][$userId] = CBlogPost::$arBlogUCache[$userId];
			}
			else
			{
				$arIdToGet[] = $userId;
			}
		}

		if (!empty($arIdToGet))
		{
			if (intval($arParams["AVATAR_SIZE"]) <= 0)
			{
				$arParams["AVATAR_SIZE"] = 100;
			}

			if (intval($arParams["AVATAR_SIZE_COMMENT"] ?? null) <= 0)
			{
				$arParams["AVATAR_SIZE_COMMENT"] = 100;
			}

			$arSelectParams = Array(
				"FIELDS" => Array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "PERSONAL_GENDER", "EXTERNAL_AUTH_ID")
			);

			if (
				ModuleManager::isModuleInstalled('intranet')
				|| ModuleManager::isModuleInstalled('crm')
			)
			{
				$arSelectParams["SELECT"] = array();
				if (ModuleManager::isModuleInstalled('intranet'))
				{
					$arSelectParams["SELECT"][] = "UF_DEPARTMENT";
				}
				if (ModuleManager::isModuleInstalled('crm'))
				{
					$arSelectParams["SELECT"][] = "UF_USER_CRM_ENTITY";
				}
			}

			$dbUser = CUser::GetList(
				Array('ID' => 'desc'),
				'',
				Array("ID" => implode(" | ", $arIdToGet)),
				$arSelectParams
			);
			while ($arUser = $dbUser->GetNext())
			{
				if (
					intval($arUser["PERSONAL_PHOTO"]) <= 0
					&& ModuleManager::isModuleInstalled('socialnetwork')
				)
				{
					switch ($arUser['PERSONAL_GENDER'])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
							break;
						default:
							$suffix = "unknown";
					}
					$arUser['PERSONAL_PHOTO'] = Option::get('socialnetwork', 'default_user_picture_'.$suffix, false, SITE_ID);
				}

				if(intval($arUser["PERSONAL_PHOTO"]) > 0)
				{
					$arUser["PERSONAL_PHOTO_file"] = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
					$arUser["PERSONAL_PHOTO_resized"] = CFile::ResizeImageGet(
						$arUser["PERSONAL_PHOTO_file"],
						array("width" => $arParams["AVATAR_SIZE"], "height" => $arParams["AVATAR_SIZE"]),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					if ($arUser["PERSONAL_PHOTO_resized"] !== false)
					{
						$arUser["PERSONAL_PHOTO_img"] = CFile::ShowImage($arUser["PERSONAL_PHOTO_resized"]["src"], $arParams["AVATAR_SIZE"], $arParams["AVATAR_SIZE"], "border=0 align='right'");
					}

					$arUser["PERSONAL_PHOTO_resized_30"] = CFile::ResizeImageGet(
						$arUser["PERSONAL_PHOTO_file"],
						array("width" => $arParams["AVATAR_SIZE_COMMENT"], "height" => $arParams["AVATAR_SIZE_COMMENT"]),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					if ($arUser["PERSONAL_PHOTO_resized_30"] !== false)
					{
						$arUser["PERSONAL_PHOTO_img_30"] = CFile::ShowImage($arUser["PERSONAL_PHOTO_resized_30"]["src"], $arParams["AVATAR_SIZE_COMMENT"], $arParams["AVATAR_SIZE_COMMENT"], "border=0 align='right'");
					}
				}

				$arUser["url"] = CComponentEngine::MakePathFromTemplate($path, array("user_id" => $arUser["ID"]));

				$arResult["arUser"][$arUser["ID"]] = CBlogPost::$arBlogUCache[$arUser["ID"]] = $arUser;
			}
		}
		return $arResult["arUser"];
	}
}
?>