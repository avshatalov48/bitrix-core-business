<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);
if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if($arParams["PATH_TO_BLOG"] == '')
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
$arParams["PATH_TO_BLOG_EDIT"] = trim($arParams["PATH_TO_BLOG_EDIT"]);
if($arParams["PATH_TO_BLOG_EDIT"] == '')
	$arParams["PATH_TO_BLOG_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog_edit&".$arParams["BLOG_VAR"]."=#blog#");
$blogModulePermissions = $APPLICATION->GetGroupRight("blog");

if (!$USER->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{

	if(CBlog::CanUserCreateBlog($USER->GetID()))
	{
		$USER_ID = intval($USER->GetID());
		if($arParams["BLOG_URL"] <> '')
		{
			$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if(
					intval($arBlog["SOCNET_GROUP_ID"]) <= 0
					&& $arGroup["SITE_ID"] != SITE_ID
				)
					unset($arBlog);
			}
			else
				unset($arBlog);
		}
		else
		{
			$arBlog = CBlog::GetByOwnerID($USER_ID, $arParams["GROUP_ID"]);
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if(
					intval($arBlog["SOCNET_GROUP_ID"]) <= 0
					&& $arGroup["SITE_ID"] != SITE_ID
				)
					unset($arBlog);
			}
			else
				unset($arBlog);
		}
		if(!empty($arBlog))
		{
			$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
			$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
			$arResult["BLOG"] = $arBlog;
		}

		if (CBlog::CanUserManageBlog($arBlog["ID"], intval($USER->GetID())) || (CBlog::CanUserCreateBlog($USER->GetID()) && intval($arBlog["ID"])<=0))
		{
			$bBlockURL = COption::GetOptionString("blog", "block_url_change", "N") == 'Y' ? true : false;
			if($bBlockURL && !($USER->IsAdmin()) && !empty($arBlog))
				$arResult["BlockURL"] = "Y";

			if ($_POST['reset'])
			{
				LocalRedirect($arResult["urlToBlog"]);
			}
			elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['do_blog'] == "Y" && check_bitrix_sessid())
			{
				if ($_POST['perms_p'][1] > BLOG_PERMS_READ)
					$_POST['perms_p'][1] = BLOG_PERMS_READ;
				if ($_POST['perms_c'][1] > BLOG_PERMS_WRITE)
					$_POST['perms_c'][1] = BLOG_PERMS_WRITE;

				$arFields = array(
					"NAME" => $_POST['NAME'],
					"DESCRIPTION" => $_POST['DESCRIPTION'],
					"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
					"ENABLE_IMG_VERIF" => (($_POST['ENABLE_IMG_VERIF'] == "Y") ? "Y" : "N"),
					"EMAIL_NOTIFY" => (($_POST['EMAIL_NOTIFY'] == "Y") ? "Y" : "N"),
					"ENABLE_RSS" => "Y",
//					"PERMS_POST" => $_POST['perms_p'],
//					"PERMS_COMMENT" => $_POST['perms_c'],
				);
				if(intval($_POST['GROUP_ID'])>0)
					$arFields["GROUP_ID"] = intval($_POST['GROUP_ID']);

				if ((!$bBlockURL || $USER->IsAdmin() || empty($arBlog)) && $_POST["URL"] <> '')
					$arFields["URL"] = $_POST['URL'];

				if (count($arParams["BLOG_PROPERTY"]) > 0)
				{
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_BLOG", $arFields);
				}
				
				$arFields["PATH"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => "#blog_url#"));
				
				if (!empty($arBlog))
				{
					
					if (is_array($_POST['group']))
						$arFields["AUTO_GROUPS"] = serialize(array_keys($_POST['group']));
					else
						$arFields["AUTO_GROUPS"] = "";
					
					$newID = CBlog::Update($arBlog["ID"], $arFields);
				}
				else
				{
					$arFields["=DATE_CREATE"] = $DB->CurrentTimeFunction();
					$arFields["ACTIVE"] = "Y";
					$arFields["OWNER_ID"] = $USER->GetID();

					$newID = CBlog::Add($arFields);
				}
				
					
				if(intval($newID) > 0)
				{
					$autoGroup = Array();
					if(!empty($_POST["grp_name"]))
					{
						foreach($_POST["grp_name"] as $k => $v)
						{
							if(intval($k) > 0)
							{
								if($_POST["grp_delete"][$k] != "Y")
								{
									$res = CBlogUserGroup::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$newID, "NAME" => $v, "!ID" => $k));
									if (!$res->Fetch())
									{
										CBlogUserGroup::Update($k, Array("NAME" => $v));
										if($_POST["group"][$k] == "Y")
											$autoGroup[] = $k;
									}
									else
									{
										$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_GROUP_EXIST", Array("#GROUP_NAME#" => htmlspecialcharsbx($v)));
									}
								}
								else
									CBlogUserGroup::Delete($k);
							}
							else
							{
								$res = CBlogUserGroup::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$newID, "NAME" => $v));
								if (!$res->Fetch())
								{
									$uGrID = CBlogUserGroup::Add(Array("NAME" => $v, "BLOG_ID" => $newID));
									
									if(intval($uGrID) > 0 && $_POST["group"][$k] == "Y")
										$autoGroup[] = $uGrID;							
								}
								else
								{
									$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_GROUP_EXIST", Array("#GROUP_NAME#" => htmlspecialcharsbx($v)));
								}
							}
						}
					}
					
					if (!empty($autoGroup))
						$arFields = Array("AUTO_GROUPS" => serialize($autoGroup));
					else
						$arFields = Array("AUTO_GROUPS" => "");
					$arFields["PERMS_POST"] = $_POST['perms_p'];
					$arFields["PERMS_COMMENT"] = $_POST['perms_c'];

					$newID = CBlog::Update($newID, $arFields);
				}

				if (intval($newID)>0 && empty($arResult["ERROR_MESSAGE"]))
				{
					$arBlog = CBlog::GetByID($newID);
					$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
					$arResult["BLOG"] = $arBlog;
					$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
					$arResult["urlToBlogEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_EDIT"], array("blog" => $arBlog["URL"]));

					if (intval($arBlog["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule("socialnetwork") && method_exists("CSocNetGroup", "GetSite"))
					{
						$arSites = array();
						$rsGroupSite = CSocNetGroup::GetSite($arBlog["SOCNET_GROUP_ID"]);
						while($arGroupSite = $rsGroupSite->Fetch())
							$arSites[] = $arGroupSite["LID"];
					}
					else
						$arSites = array(SITE_ID);

					foreach ($arSites as $site_id_tmp)
					{
						BXClearCache(True, "/".$site_id_tmp."/blog/new_blogs/");
						BXClearCache(True, "/".$site_id_tmp."/blog/groups/".$arBlog['GROUP_ID']."/");
						BXClearCache(True, "/".$site_id_tmp."/blog/".$arBlog['URL']);
						BXClearCache(True, "/".$site_id_tmp."/blog/last_messages/");
						BXClearCache(True, "/".$site_id_tmp."/blog/commented_posts/");
						BXClearCache(True, "/".$site_id_tmp."/blog/popular_posts/");
						BXClearCache(True, "/".$site_id_tmp."/blog/last_comments/");
						BXClearCache(True, "/".$site_id_tmp."/blog/popular_blogs/");
						BXClearCache(True, "/".$site_id_tmp."/blog/last_messages_list_extranet/");
						BXClearCache(True, "/".$site_id_tmp."/blog/blog_groups/");
					}

					if ($_POST['apply'] <> '')
						LocalRedirect($arResult["urlToBlogEdit"]);
					else
						LocalRedirect($arResult["urlToBlog"]);
				}
				else
				{
					if ($ex = $APPLICATION->GetException())
						$arResult["ERROR_MESSAGE"][] = $ex->GetString();
					elseif(empty($arResult["ERROR_MESSAGE"]))
						$arResult["ERROR_MESSAGE"][] = GetMessage('BLOG_ERR_SAVE');

					foreach($_POST as $k => $v)
					{
						if(is_array($v))
						{
							foreach($v as $k1 => $v1)
							{
								$arResult["BLOG"][$k1] = htmlspecialcharsbx($v1);
								$arResult["BLOG"]['~'.$k1] = $v1;
							}
						}
						else
						{
							$arResult["BLOG"][$k] = htmlspecialcharsbx($v);
							$arResult["BLOG"]['~'.$k] = $v;						
						}
					}
				}
			}
	
	
			if($arParams["SET_TITLE"]=="Y")
			{
				if (!empty($arBlog))
					$APPLICATION->SetTitle(str_replace("#BLOG#", $arBlog["NAME"], GetMessage('BLOG_TOP_TITLE')));
				else
					$APPLICATION->SetTitle(GetMessage('BLOG_NEW_BLOG'));
			}

			$arFilterGroup = array("SITE_ID" => SITE_ID);
			if(!empty($arParams["GROUP_ID"]))
				$arFilterGroup["ID"] = $arParams["GROUP_ID"];
			$dbBlogGroup = CBlogGroup::GetList(
				array("NAME" => "ASC"),
				$arFilterGroup
			);
			$arBlogGroupTmp = Array();
			while ($arBlogGroup = $dbBlogGroup->GetNext())
			{
				if($arBlogGroup["ID"] == $arResult["BLOG"]["GROUP_ID"])
					$arBlogGroup["SELECTED"] = "Y";
				$arBlogGroupTmp[] = $arBlogGroup;
			}
			$arResult["GROUP"] = $arBlogGroupTmp;

			$arResult["AUTO_GROUPS"] = Array();
			if(!empty($arBlog))
				$arResult["AUTO_GROUPS"] = unserialize($arBlog["AUTO_GROUPS"]);
				
			if(!empty($arBlog))
			{
				$res=CBlogUserGroup::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]), array("ID", "NAME", "BLOG_ID", "COUNT" => "USER2GROUP_ID"));
				while ($arGroup=$res->Fetch())
				{
					$arSumGroup[$arGroup["ID"]] = $arGroup["CNT"];
				}

				$res=CBlogUserGroup::GetList(Array("ID" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]));
				$arUGroupTmp = Array();
				while($arUGroup=$res->GetNext())
				{
					if(is_array($arResult["AUTO_GROUPS"]) && in_array($arUGroup["ID"], $arResult["AUTO_GROUPS"]))
						$arUGroup["CHECKED"] = "Y";
					$arUGroup["CNT"] = intval($arSumGroup[$arUGroup["ID"]]);
					$arUGroupTmp[] = $arUGroup;
				}
				$arResult["USER_GROUP"] = $arUGroupTmp;
			}
			else
				$arResult["USER_GROUP"][] = Array("ID" => 0, "NAME" => GetMessage('BLOG_FRIENDS'), "CNT" => 0);

			$arResult["BLOG_POST_PERMS"] = $GLOBALS["AR_BLOG_POST_PERMS"];
			$arResult["BLOG_COMMENT_PERMS"] = $GLOBALS["AR_BLOG_COMMENT_PERMS"];
			
			if(!$USER->IsAdmin() && $blogModulePermissions < "W")
			{
				$arResult["post_everyone_max_rights"] = COption::GetOptionString("blog", "post_everyone_max_rights", "");
				$arResult["comment_everyone_max_rights"] = COption::GetOptionString("blog", "comment_everyone_max_rights", "");
				$arResult["post_auth_user_max_rights"] = COption::GetOptionString("blog", "post_auth_user_max_rights", "");
				$arResult["comment_auth_user_max_rights"] = COption::GetOptionString("blog", "comment_auth_user_max_rights", "");
				$arResult["post_group_user_max_rights"] = COption::GetOptionString("blog", "post_group_user_max_rights", "");
				$arResult["comment_group_user_max_rights"] = COption::GetOptionString("blog", "comment_group_user_max_rights", "");
				
				foreach($arResult["BLOG_POST_PERMS"] as  $v)
				{
					if($arResult["post_everyone_max_rights"] <> '' && $v <= $arResult["post_everyone_max_rights"])
						$arResult["ar_post_everyone_rights"][] = $v;
					if($arResult["post_auth_user_max_rights"] <> '' && $v <= $arResult["post_auth_user_max_rights"])
						$arResult["ar_post_auth_user_rights"][] = $v;
					if($arResult["post_group_user_max_rights"] <> '' && $v <= $arResult["post_group_user_max_rights"])
						$arResult["ar_post_group_user_rights"][] = $v;

				}

				foreach($arResult["BLOG_COMMENT_PERMS"] as  $v)
				{
					if($arResult["comment_everyone_max_rights"] <> '' && $v <= $arResult["comment_everyone_max_rights"])
						$arResult["ar_comment_everyone_rights"][] = $v;
					if($arResult["comment_auth_user_max_rights"] <> '' && $v <= $arResult["comment_auth_user_max_rights"])
						$arResult["ar_comment_auth_user_rights"][] = $v;
					if($arResult["comment_group_user_max_rights"] <> '' && $v <= $arResult["comment_group_user_max_rights"])
						$arResult["ar_comment_group_user_rights"][] = $v;
				}
			}
			$arResult["BLOG_PROPERTIES"] = array("SHOW" => "N");
			$arResult["useCaptcha"] = COption::GetOptionString("blog", "captcha_choice", "U");

			if (!empty($arParams["BLOG_PROPERTY"]))
			{
				$arBlogFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_BLOG", $arBlog["ID"], LANGUAGE_ID);

				if (count($arParams["BLOG_PROPERTY"]) > 0)
				{
					foreach ($arBlogFields as $FIELD_NAME => $arBlogField)
					{
						if (!in_array($FIELD_NAME, $arParams["BLOG_PROPERTY"]))
							continue;
						$arBlogField["EDIT_FORM_LABEL"] = $arBlogField["EDIT_FORM_LABEL"] <> '' ? $arBlogField["EDIT_FORM_LABEL"] : $arBlogField["FIELD_NAME"];
						$arBlogField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arBlogField["EDIT_FORM_LABEL"]);
						$arBlogField["~EDIT_FORM_LABEL"] = $arBlogField["EDIT_FORM_LABEL"];
						$arResult["BLOG_PROPERTIES"]["DATA"][$FIELD_NAME] = $arBlogField;
					}
				}
				if (!empty($arResult["BLOG_PROPERTIES"]["DATA"]))
					$arResult["BLOG_PROPERTIES"]["SHOW"] = "Y";
			}

			
			if (!empty($arBlog))
			{
				$res=CBlogUserGroupPerms::GetList(array("ID" => "DESC"),array("BLOG_ID" => $arBlog['ID'], "POST_ID" => 0));
				while($arPerms = $res->Fetch())
				{
					if ($arPerms['PERMS_TYPE']=='P')
						$arResult["BLOG"]["perms_p"][$arPerms['USER_GROUP_ID']] = $arPerms['PERMS'];
					elseif ($arPerms['PERMS_TYPE']=='C')
						$arResult["BLOG"]["perms_c"][$arPerms['USER_GROUP_ID']] = $arPerms['PERMS'];
				}
			}
			else
			{
				$arResult["BLOG"]["perms_p"][1] = BLOG_PERMS_READ;
				$arResult["BLOG"]["perms_p"][2] = BLOG_PERMS_READ;
				$arResult["BLOG"]["perms_c"][1] = BLOG_PERMS_WRITE;
				$arResult["BLOG"]["perms_c"][2] = BLOG_PERMS_WRITE;
			}
	
			if (!empty($arBlog))
				$arResult["CAN_UPDATE"] = "Y";
		}
		else
			$arResult["FATAL_ERROR"][] = GetMessage('BLOG_ERR_NO_RIGHTS');
	}
	else
		$arResult["FATAL_ERROR"][] = GetMessage("BLOG_NOT_RIGHTS_TO_CREATE");
}

$this->IncludeComponentTemplate();
?>