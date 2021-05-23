<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arParams["BLOG_URL"]);
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);
		
if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER_SETTINGS"] = trim($arParams["PATH_TO_USER_SETTINGS"]);
if($arParams["PATH_TO_USER_SETTINGS"] == '')
	$arParams["PATH_TO_USER_SETTINGS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_settings&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["ID"] = intval($arParams["ID"]);

if ($arParams["BLOG_URL"] <> '')
{
	if(intval($arParams["ID"]) > 0)
	{
		if($arParams["SET_TITLE"]=="Y")
			$APPLICATION->SetTitle(GetMessage("B_B_USE_TITLE"));

		$dbUser = CUser::GetByID($arParams["ID"]);
		if ($arUser = $dbUser->GetNext())
		{
			$arResult["User"] = $arUser;
			if ($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]))
			{
				if($arBlog["ACTIVE"] == "Y")
				{
					$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
					if($arGroup["SITE_ID"] == SITE_ID)
					{
						$arResult["Blog"] = $arBlog;

						if (CBlog::CanUserManageBlog($arBlog["ID"], intval($USER->GetID())))
						{
							if($arParams["SET_TITLE"]=="Y")
								$APPLICATION->SetTitle(str_replace("#NAME#", $arBlog["NAME"], GetMessage("B_B_USE_TITLE_BLOG")));
							$errorMessage = "";
							$okMessage = "";
							$arBlogUser = CBlogUser::GetByID($arUser["ID"], BLOG_BY_USER_ID);
							$arBlogUser = CBlogTools::htmlspecialcharsExArray($arBlogUser);
							$arResult["BlogUser"] = $arBlogUser;
							
							if ($GLOBALS["user_action"] == "Y" && check_bitrix_sessid())
							{
								if($GLOBALS["cancel"] <> '')
									LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS"], array("blog" => $arBlog["URL"])));
								if (empty($arBlogUser))
								{
									CBlogUser::Add(
										array(
											"USER_ID" => $arUser["ID"],
											"=LAST_VISIT" => $DB->GetNowFunction(),
											"=DATE_REG" => $DB->GetNowFunction(),
											"ALLOW_POST" => "Y"
										)
									);
								}

								CBlogUser::AddToUserGroup($arUser["ID"], $arBlog["ID"], $GLOBALS["add2groups"], "", BLOG_BY_USER_ID, BLOG_CHANGE);

								$dbCandidate = CBlogCandidate::GetList(
									array(),
									array("BLOG_ID" => $arBlog["ID"], "USER_ID" => $arUser["ID"])
								);
								if ($arCandidate = $dbCandidate->Fetch())
									CBlogCandidate::Delete($arCandidate["ID"]);
								
								LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS"], array("blog" => $arBlog["URL"])));
							}

							$arResult["ERROR_MESSAGE"] = $errorMessage;
							$arResult["OK_MESSAGE"] = $okMessage;
							$arResult["userName"] = CBlogUser::GetUserName($arBlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]);
							$arResult["urlToUser"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));
							$arResult["arUserGroups"] = CBlogUser::GetUserGroups($arUser["ID"], $arBlog["ID"], "Y", BLOG_BY_USER_ID);
							$dbBlogGroups = CBlogUserGroup::GetList(
								array("NAME" => "ASC"),
								array("BLOG_ID" => $arBlog["ID"]),
								false,
								false,
								array("ID", "NAME")
							);
							while ($arBlogGroups = $dbBlogGroups->GetNext())
								$arResult["Groups"][] = $arBlogGroups;
						}
						else
							$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_RIGHT");
					}
					else
						$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
				}
				else
					$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
			}
			else
			{
				$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
			}
		}
		else
		{
			$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_USER");
		}
	}
	else
		$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_USER");
}
else
{
	$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
}
	
$this->IncludeComponentTemplate();
?>